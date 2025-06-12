<?php
// actions/ticket_update_details.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=tickets"); 
    exit();
}

require_login();
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$new_status = $_POST['status'] ?? '';
$department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
$user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$sql = "UPDATE tickets SET status = :status, ...";

$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// --- 2. التحقق من صحة البيانات والصلاحيات ---
$allowed_statuses = ['New','Open','In Progress','Resolved','Closed'];
if ($ticket_id <= 0 || !in_array($new_status, $allowed_statuses)) {
    $_SESSION['error_message'] = "بيانات تحديث التذكرة غير صالحة.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php?page=tickets'));
    exit();
}

try {
    // جلب بيانات التذكرة الحالية للتحقق والمقارنة
    $stmt_check = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
    $stmt_check->execute([$ticket_id]);
    $current_ticket = $stmt_check->fetch();

    if (!$current_ticket) {
        $_SESSION['error_message'] = "التذكرة المطلوبة غير موجودة.";
        header("Location: ../index.php?page=tickets");
        exit();
    }
    
    // أي مستخدم مرتبط بالتذكرة يمكنه تغيير الحالة، ولكن فقط المدير/المشرف يمكنه تغيير الإسناد
    $can_change_status = ($current_ticket['created_by_user_id'] == $current_user_id || 
                          $current_ticket['assigned_to_user_id'] == $current_user_id || 
                          in_array($current_user_role, ['admin', 'supervisor']));

    $can_change_assignment = in_array($current_user_role, ['admin', 'supervisor']);

    if (!$can_change_status) {
        $_SESSION['error_message'] = "ليس لديك صلاحية لتغيير تفاصيل هذه التذكرة.";
        header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}");
        exit();
    }
    
    // إذا حاول مستخدم غير مصرح له تغيير الإسناد، تجاهل التغيير
    if (!$can_change_assignment) {
        $department_id = $current_ticket['assigned_to_department_id'];
        $user_id = $current_ticket['assigned_to_user_id'];
    }
    
    // --- 3. بناء استعلام التحديث وتتبع التغييرات للتعليقات ---
    $pdo->beginTransaction();
    $update_log = [];

    // تتبع تغيير الحالة
    if ($current_ticket['status'] !== $new_status) {
        $update_log[] = "تغيير الحالة من '{$current_ticket['status']}' إلى '{$new_status}'";
    }

    // تتبع تغيير إسناد القسم
    if ($current_ticket['assigned_to_department_id'] != $department_id) {
        $old_dept_name = $current_ticket['department_name'] ?? 'لا شيء'; // department_name from the controller's JOIN
        $new_dept_name_stmt = $pdo->prepare("SELECT name FROM departments WHERE department_id = ?");
        $new_dept_name_stmt->execute([$department_id]);
        $new_dept_name = $new_dept_name_stmt->fetchColumn() ?? 'لا شيء';
        $update_log[] = "تغيير إسناد القسم من '{$old_dept_name}' إلى '{$new_dept_name}'";
    }
    
    // تتبع تغيير إسناد الموظف
    if ($current_ticket['assigned_to_user_id'] != $user_id) {
         $old_user_name = $current_ticket['assignee_name'] ?? 'لا أحد'; // assignee_name from the controller's JOIN
         $new_user_name_stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
         $new_user_name_stmt->execute([$user_id]);
         $new_user_name = $new_user_name_stmt->fetchColumn() ?? 'لا أحد';
         $update_log[] = "تغيير إسناد الموظف من '{$old_user_name}' إلى '{$new_user_name}'";
    }

    $sql = "UPDATE tickets SET 
                status = :status,
                assigned_to_department_id = :department_id,
                assigned_to_user_id = :user_id,
                updated_at = NOW()";
    
    // تحديث أوقات البدء والانتهاء
    if ($current_ticket['status'] !== 'In Progress' && $new_status === 'In Progress' && $current_ticket['started_at'] === null) {
        $sql .= ", started_at = NOW()";
    }
    if ($current_ticket['status'] !== 'Completed' && $new_status === 'Completed') {
        $sql .= ", completed_at = NOW()";
    } elseif ($current_ticket['status'] === 'Completed' && $new_status !== 'Completed') {
        $sql .= ", completed_at = NULL";
    }

    $sql .= " WHERE ticket_id = :ticket_id";

    $params = [
        ':status' => $new_status,
        ':department_id' => $department_id,
        ':user_id' => $user_id,
        ':ticket_id' => $ticket_id
    ];

    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute($params);

    // --- 4. إضافة تعليق تلقائي بالتغييرات التي تمت ---
    if (!empty($update_log)) {
        $comment_text = "قام " . $_SESSION['full_name'] . " بتحديث التذكرة:\n- " . implode("\n- ", $update_log);
        $stmt_comment = $pdo->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt_comment->execute([$ticket_id, $current_user_id, $comment_text]);
    }
    
    $pdo->commit();
    $_SESSION['success_message'] = "تم تحديث التذكرة بنجاح.";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Ticket details update failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء تحديث التذكرة.";
} catch (Exception $e) {
     if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error_message'] = $e->getMessage();
}

// --- 5. إعادة التوجيه إلى صفحة التذكرة ---
header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}");
exit();