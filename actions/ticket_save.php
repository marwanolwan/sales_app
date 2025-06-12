<?php
// actions/ticket_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=tickets"); 
    exit();
}

require_login(); // يجب أن يكون المستخدم مسجلاً دخوله
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$action = $_POST['action'] ?? 'add';
$ticket_id = ($action == 'edit') ? (int)($_POST['ticket_id'] ?? null) : null;

$subject = trim($_POST['subject'] ?? '');
$description = trim($_POST['description'] ?? '');
$customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
$ticket_type = $_POST['ticket_type'] ?? '';
$priority = $_POST['priority'] ?? 'Medium';
$status = $_POST['status'] ?? 'New';
$assigned_to_dept_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
$assigned_to_user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// --- 2. التحقق من صحة البيانات ---
$errors = [];
if (empty($subject)) {
    $errors[] = "موضوع التذكرة مطلوب.";
}
if (empty($description)) {
    $errors[] = "وصف التذكرة مطلوب.";
}
if (empty($ticket_type)) {
    $errors[] = "يجب تحديد نوع التذكرة.";
}

// قائمة الحالات والأولويات المسموح بها للحماية
$allowed_statuses = ['New','Open','In Progress','Resolved','Closed'];
$allowed_priorities = ['Low','Medium','High','Critical'];
if (!in_array($status, $allowed_statuses)) {
    $status = 'New'; // قيمة افتراضية آمنة
}
if (!in_array($priority, $allowed_priorities)) {
    $priority = 'Medium'; // قيمة افتراضية آمنة
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: ../index.php?page=tickets&action={$action}" . ($ticket_id ? "&id={$ticket_id}" : ''));
    exit();
}

// --- 3. بدء عملية قاعدة البيانات ---
define('TICKET_ATTACHMENT_DIR', '../uploads/ticket_attachments/');
if (!is_dir(TICKET_ATTACHMENT_DIR)) {
    mkdir(TICKET_ATTACHMENT_DIR, 0775, true);
}

$pdo->beginTransaction();

try {
    // --- 3.1. حفظ أو تحديث التذكرة الأساسية ---
    if ($action == 'edit' && $ticket_id) { 
        // تحديث تذكرة حالية
        // تحقق من صلاحية التعديل مرة أخرى من جانب الخادم
        $stmt_check = $pdo->prepare("SELECT created_by_user_id FROM tickets WHERE ticket_id = ?");
        $stmt_check->execute([$ticket_id]);
        $owner_id = $stmt_check->fetchColumn();

        if ($owner_id != $current_user_id && $current_user_role != 'admin') {
            throw new Exception("ليس لديك صلاحية لتعديل هذه التذكرة.");
        }

          $sql = "UPDATE tickets SET 
                    subject = :subject,
                    description = :description,
                    customer_id = :customer_id,
                    ticket_type = :ticket_type,
                    priority = :priority,
                    status = :status,
                    assigned_to_department_id = :assigned_to_department_id,
                    assigned_to_user_id = :assigned_to_user_id
                WHERE ticket_id = :ticket_id";
        
        // إذا قام المستخدم بإغلاق التذكرة، يتم أرشفتها تلقائيًا
        $current_status = $_POST['status'];
        if ($current_status === 'Closed') {
            // ملاحظة: الأرشفة أفضل من الحذف، لكن سنعتبر closed الآن هو الأرشفة
            // يمكنك تغيير هذا إلى 'Archived' إذا أضفت حالة جديدة
        }
        
        $params = [
            ':subject' => $subject,
            ':description' => $description,
            ':customer_id' => $customer_id,
            ':ticket_type' => $ticket_type,
            ':priority' => $priority,
            ':status' => $current_status,
            ':assigned_to_department_id' => $assigned_to_dept_id,
            ':assigned_to_user_id' => $assigned_to_user_id,
            ':ticket_id' => $ticket_id
        ];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    } else { // إنشاء تذكرة جديدة
        $sql = "INSERT INTO tickets 
                    (subject, description, customer_id, ticket_type, priority, status, created_by_user_id, assigned_to_department_id, assigned_to_user_id) 
                VALUES 
                    (:subject, :description, :customer_id, :ticket_type, :priority, :status, :created_by_user_id, :assigned_to_department_id, :assigned_to_user_id)";
        
        $params = [
            ':subject' => $subject,
            ':description' => $description,
            ':customer_id' => $customer_id,
            ':ticket_type' => $ticket_type,
            ':priority' => $priority,
            ':status' => 'New', // التذاكر الجديدة تبدأ دائمًا بحالة "جديدة"
            ':created_by_user_id' => $current_user_id,
            ':assigned_to_department_id' => $assigned_to_dept_id,
            ':assigned_to_user_id' => $assigned_to_user_id
        ];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ticket_id = $pdo->lastInsertId(); // الحصول على ID التذكرة الجديدة
    }

    // --- 3.2. معالجة رفع المرفقات ---
    if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        $stmt_attachment = $pdo->prepare("INSERT INTO ticket_attachments (ticket_id, file_name, file_path, uploaded_by_user_id) VALUES (?, ?, ?, ?)");
        
        foreach ($_FILES['attachments']['name'] as $key => $name) {
            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = basename($name);
                $file_tmp = $_FILES['attachments']['tmp_name'][$key];
                
                // إنشاء اسم فريد للملف
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_path = "ticket_{$ticket_id}_" . time() . '_' . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($file_tmp, TICKET_ATTACHMENT_DIR . $new_file_path)) {
                    $stmt_attachment->execute([$ticket_id, $file_name, $new_file_path, $current_user_id]);
                } else {
                    // إذا فشل رفع ملف واحد، تراجع عن كل شيء
                    throw new Exception("فشل رفع الملف: " . htmlspecialchars($file_name));
                }
            }
        }
    }

    // --- 4. إتمام العملية ---
    $pdo->commit();
    $_SESSION['success_message'] = "تم حفظ التذكرة بنجاح.";
    header("Location: ../index.php?page=tickets&action=view&id=" . $ticket_id);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Ticket save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ فني أثناء حفظ التذكرة: " . $e->getMessage();
    header("Location: ../index.php?page=tickets&action=" . ($ticket_id ? "edit&id={$ticket_id}" : "add"));
    exit();
}