<?php
// actions/task_comment_add.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=tasks"); 
    exit();
}

require_login(); // يجب أن يكون المستخدم مسجلاً دخوله ليضيف تعليقًا
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$comment_text = trim($_POST['comment_text'] ?? '');
$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];
$attachment = $_FILES['comment_attachment'] ?? null;

// --- 2. التحقق من صحة البيانات والصلاحيات ---
if ($task_id <= 0) {
    $_SESSION['error_message'] = "معرف المهمة غير صالح.";
    header("Location: ../index.php?page=tasks");
    exit();
}

// يجب أن يكون هناك نص أو مرفق على الأقل
if (empty($comment_text) && (empty($attachment) || $attachment['error'] !== UPLOAD_ERR_OK)) {
    $_SESSION['error_message'] = "يجب كتابة تعليق أو إرفاق ملف.";
    header("Location: ../index.php?page=tasks&action=view&id={$task_id}");
    exit();
}

// التحقق من أن المستخدم لديه صلاحية للتعليق على هذه المهمة
try {
    $stmt_check = $pdo->prepare("SELECT created_by_user_id, assigned_to_user_id FROM tasks WHERE task_id = ?");
    $stmt_check->execute([$task_id]);
    $task_users = $stmt_check->fetch();

    if (!$task_users) {
        $_SESSION['error_message'] = "المهمة التي تحاول التعليق عليها غير موجودة.";
        header("Location: ../index.php?page=tasks");
        exit();
    }
    
    // السماح بالتعليق إذا كان المستخدم هو المنشئ، أو المكلف، أو مدير
    if ($task_users['created_by_user_id'] != $current_user_id && $task_users['assigned_to_user_id'] != $current_user_id && $current_user_role != 'admin') {
        $_SESSION['error_message'] = "ليس لديك صلاحية لإضافة تعليق على هذه المهمة.";
        header("Location: ../index.php?page=tasks&action=view&id={$task_id}");
        exit();
    }

} catch (PDOException $e) {
    error_log("Comment permission check failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ أثناء التحقق من صلاحيات التعليق.";
    header("Location: ../index.php?page=tasks&action=view&id={$task_id}");
    exit();
}

// --- 3. معالجة رفع المرفق (إن وجد) ---
$attachment_path = null;
if (!empty($attachment) && $attachment['error'] === UPLOAD_ERR_OK) {
    define('COMMENT_ATTACHMENT_DIR', '../uploads/task_comments/');
    if (!is_dir(COMMENT_ATTACHMENT_DIR)) {
        mkdir(COMMENT_ATTACHMENT_DIR, 0775, true);
    }
    
    $file_name = basename($attachment['name']);
    $file_tmp = $attachment['tmp_name'];
    $file_size = $attachment['size'];
    
    // يمكنك إضافة تحقق من امتداد وحجم الملف هنا
    // if ($file_size > 5000000) { /* error: file too large */ }
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_path = "comment_{$task_id}_" . time() . '_' . uniqid() . '.' . $ext;
    
    if (move_uploaded_file($file_tmp, COMMENT_ATTACHMENT_DIR . $new_file_path)) {
        $attachment_path = $new_file_path;
    } else {
        $_SESSION['error_message'] = "فشل رفع الملف المرفق.";
        header("Location: ../index.php?page=tasks&action=view&id={$task_id}");
        exit();
    }
}

// --- 4. حفظ التعليق في قاعدة البيانات ---
try {
    $sql = "INSERT INTO task_comments (task_id, user_id, comment_text, attachment_path) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $task_id,
        $current_user_id,
        !empty($comment_text) ? $comment_text : null, // أدخل NULL إذا كان التعليق فارغًا
        $attachment_path
    ]);

    // (اختياري) يمكنك هنا إنشاء إشعار للمستخدم الآخر في المهمة
    // $other_user_id = ($task_users['created_by_user_id'] == $current_user_id) ? $task_users['assigned_to_user_id'] : $task_users['created_by_user_id'];
    // if ($other_user_id != $current_user_id) {
    //     // كود إنشاء إشعار في جدول `notifications`
    // }

    $_SESSION['success_message'] = "تمت إضافة تعليقك بنجاح.";

} catch (PDOException $e) {
    error_log("Add comment failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء إضافة التعليق.";
}

// --- 5. إعادة التوجيه إلى صفحة المهمة ---
// إضافة مرساة (anchor) للرابط للانتقال مباشرة إلى قسم التعليقات
header("Location: ../index.php?page=tasks&action=view&id={$task_id}#comments-list");
exit();