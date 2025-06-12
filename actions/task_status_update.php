<?php
// actions/task_status_update.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=tasks"); 
    exit();
}

require_login();
verify_csrf_token();

// --- 1. استلام البيانات ---
$task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$new_status = $_POST['status'] ?? '';
$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// قائمة الحالات المسموح بها للحماية
$allowed_statuses = ['Not Started', 'In Progress', 'Completed', 'Archived'];

// --- 2. التحقق من صحة البيانات والصلاحيات ---
if ($task_id <= 0 || !in_array($new_status, $allowed_statuses)) {
    $_SESSION['error_message'] = "بيانات تحديث الحالة غير صالحة.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php?page=tasks'));
    exit();
}

try {
    // جلب بيانات المهمة للتحقق من الصلاحية والحالة الحالية
    $stmt_check = $pdo->prepare("SELECT created_by_user_id, assigned_to_user_id, status, started_at FROM tasks WHERE task_id = ?");
    $stmt_check->execute([$task_id]);
    $task = $stmt_check->fetch();

    if (!$task) {
        $_SESSION['error_message'] = "المهمة غير موجودة.";
        header("Location: ../index.php?page=tasks");
        exit();
    }
    
    // السماح بالتحديث إذا كان المستخدم هو المنشئ، أو المكلف، أو مدير
    if ($task['created_by_user_id'] != $current_user_id && $task['assigned_to_user_id'] != $current_user_id && $current_user_role != 'admin') {
        $_SESSION['error_message'] = "ليس لديك صلاحية لتغيير حالة هذه المهمة.";
        header("Location: ../index.php?page=tasks&action=view&id={$task_id}");
        exit();
    }

    // --- 3. بناء استعلام التحديث ---
    $sql = "UPDATE tasks SET status = :new_status";
    $params = [':new_status' => $new_status, ':task_id' => $task_id];
    
    // تحديث وقت بدء التنفيذ تلقائيًا
    // إذا كانت المهمة لم تبدأ بعد وتم تغيير حالتها إلى "قيد التنفيذ"
    if ($task['status'] === 'Not Started' && $new_status === 'In Progress' && $task['started_at'] === null) {
        $sql .= ", started_at = NOW()";
    }

    // تحديث وقت الانتهاء تلقائيًا
    // إذا تم تغيير حالة المهمة إلى "منتهية"
    if ($new_status === 'Completed') {
        $sql .= ", completed_at = NOW()";
    } else {
        // إذا تم إرجاع المهمة من "منتهية" إلى حالة أخرى، قم بإلغاء وقت الانتهاء
        $sql .= ", completed_at = NULL";
    }

    $sql .= " WHERE task_id = :task_id";

    // --- 4. تنفيذ التحديث ---
    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute($params);

    $_SESSION['success_message'] = "تم تحديث حالة المهمة بنجاح.";

} catch (PDOException $e) {
    error_log("Task status update failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء تحديث الحالة.";
}

// --- 5. إعادة التوجيه إلى صفحة المهمة ---
header("Location: ../index.php?page=tasks&action=view&id={$task_id}");
exit();