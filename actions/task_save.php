<?php
// actions/task_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=tasks"); 
    exit();
}

require_login(); // كل من ينشئ مهمة يجب أن يكون مسجلاً دخوله
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$task_id = !empty($_POST['task_id']) ? (int)$_POST['task_id'] : null;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$assigned_to_user_id = (int)($_POST['assigned_to_user_id'] ?? 0);
$due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
$status = $_POST['status'] ?? 'Not Started';
$steps = $_POST['steps'] ?? [];

$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// --- 2. التحقق من صحة البيانات والصلاحيات ---
$errors = [];
if (empty($title)) {
    $errors[] = "عنوان المهمة مطلوب.";
}
if (empty($assigned_to_user_id)) {
    $errors[] = "يجب تحديد الموظف المكلف بالمهمة.";
}

// التحقق من صلاحية تكليف المستخدم
if ($assigned_to_user_id !== $current_user_id) { // إذا كانت المهمة ليست شخصية
    if ($current_user_role === 'representative' || $current_user_role === 'promoter') {
        $errors[] = "ليس لديك صلاحية لتكليف مهام لمستخدمين آخرين.";
    } elseif ($current_user_role === 'supervisor') {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND supervisor_id = ?");
        $stmt->execute([$assigned_to_user_id, $current_user_id]);
        if ($stmt->fetch() === false) {
            $errors[] = "لا يمكنك تكليف مهمة لهذا الموظف لأنه لا يتبع لفريقك.";
        }
    }
    // المدير (admin) يمكنه تكليف أي شخص، لذلك لا يوجد تحقق هنا له.
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: ../index.php?page=tasks&action=" . ($task_id ? "edit&id={$task_id}" : "add"));
    exit();
}

// --- 3. بدء عملية قاعدة البيانات ---
define('TASK_ATTACHMENT_DIR', '../uploads/task_attachments/');
if (!is_dir(TASK_ATTACHMENT_DIR)) {
    mkdir(TASK_ATTACHMENT_DIR, 0775, true);
}

$pdo->beginTransaction();

try {
    // --- 3.1. حفظ أو تحديث المهمة الأساسية ---
    if ($task_id) { // تحديث مهمة حالية
        $sql = "UPDATE tasks SET title = ?, description = ?, assigned_to_user_id = ?, due_date = ?, status = ? WHERE task_id = ?";
        $params = [$title, $description, $assigned_to_user_id, $due_date, $status, $task_id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else { // إنشاء مهمة جديدة
        $sql = "INSERT INTO tasks (title, description, created_by_user_id, assigned_to_user_id, due_date, status) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$title, $description, $current_user_id, $assigned_to_user_id, $due_date, $status];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $task_id = $pdo->lastInsertId(); // الحصول على ID المهمة الجديدة
    }

    // --- 3.2. معالجة الخطوات (Checklist) ---
    $existing_step_ids = [];
    if (!empty($steps)) {
        $stmt_update_step = $pdo->prepare("UPDATE task_steps SET step_title = ?, is_completed = ? WHERE step_id = ? AND task_id = ?");
        $stmt_insert_step = $pdo->prepare("INSERT INTO task_steps (task_id, step_title, is_completed) VALUES (?, ?, ?)");

        foreach ($steps as $step_id_key => $step_data) {
            $step_title = trim($step_data['title'] ?? '');
            if (empty($step_title)) continue; // تجاهل الخطوات الفارغة

            $is_completed = (int)($step_data['is_completed'] ?? 0);
            
            if (is_numeric($step_id_key)) { // خطوة حالية (تحديث)
                $step_id = (int)$step_id_key;
                $stmt_update_step->execute([$step_title, $is_completed, $step_id, $task_id]);
                $existing_step_ids[] = $step_id;
            } elseif ($step_data['id'] === 'new') { // خطوة جديدة
                $stmt_insert_step->execute([$task_id, $step_title, $is_completed]);
                $existing_step_ids[] = $pdo->lastInsertId();
            }
        }
    }
    // حذف الخطوات التي أزالها المستخدم من النموذج
    if ($task_id && $action == 'edit') {
        $sql_delete_steps = "DELETE FROM task_steps WHERE task_id = ?";
        if (!empty($existing_step_ids)) {
            $placeholders = implode(',', array_fill(0, count($existing_step_ids), '?'));
            $sql_delete_steps .= " AND step_id NOT IN ($placeholders)";
        }
        $stmt_delete_steps = $pdo->prepare($sql_delete_steps);
        $stmt_delete_steps->execute(array_merge([$task_id], $existing_step_ids));
    }
    

    // --- 3.3. معالجة رفع المرفقات ---
    if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        $stmt_attachment = $pdo->prepare("INSERT INTO task_attachments (task_id, file_name, file_path, uploaded_by_user_id) VALUES (?, ?, ?, ?)");
        
        foreach ($_FILES['attachments']['name'] as $key => $name) {
            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = basename($name);
                $file_tmp = $_FILES['attachments']['tmp_name'][$key];
                
                // إنشاء اسم فريد للملف لتجنب الكتابة فوق الملفات
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_path = "task_{$task_id}_" . time() . '_' . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($file_tmp, TASK_ATTACHMENT_DIR . $new_file_path)) {
                    $stmt_attachment->execute([$task_id, $file_name, $new_file_path, $current_user_id]);
                }
            }
        }
    }

    // --- 4. إتمام العملية ---
    $pdo->commit();
    $_SESSION['success_message'] = "تم حفظ المهمة بنجاح.";
    header("Location: ../index.php?page=tasks&action=view&id=" . $task_id);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Task save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ فني أثناء حفظ المهمة: " . $e->getMessage();
    header("Location: ../index.php?page=tasks&action=" . ($task_id ? "edit&id={$task_id}" : "add"));
    exit();
}