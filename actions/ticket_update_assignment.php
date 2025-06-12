<?php
// actions/ticket_update_assignment.php

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
$department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
$user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// --- 2. التحقق من صحة البيانات والصلاحيات ---
if ($ticket_id <= 0) {
    $_SESSION['error_message'] = "معرف التذكرة غير صالح.";
    header("Location: ../index.php?page=tickets");
    exit();
}

// فقط المدير والمشرف يمكنهم تغيير الإسناد
if (!in_array($current_user_role, ['admin', 'supervisor'])) {
    $_SESSION['error_message'] = "ليس لديك صلاحية لتغيير إسناد هذه التذكرة.";
    header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}");
    exit();
}

try {
    // التحقق من وجود التذكرة
    $stmt_check = $pdo->prepare("SELECT ticket_id FROM tickets WHERE ticket_id = ?");
    $stmt_check->execute([$ticket_id]);
    if (!$stmt_check->fetch()) {
        $_SESSION['error_message'] = "التذكرة المطلوبة غير موجودة.";
        header("Location: ../index.php?page=tickets");
        exit();
    }

    // --- 3. بناء وتنفيذ استعلام التحديث ---
    // إذا تم إسناد التذكرة لموظف، يفضل إلغاء إسنادها للقسم ليكون الإسناد واضحًا، والعكس صحيح
    // لكننا سنسمح بكليهما الآن من أجل المرونة
    $sql = "UPDATE tickets SET 
                assigned_to_department_id = :department_id,
                assigned_to_user_id = :user_id,
                updated_at = NOW() -- تحديث وقت آخر تعديل
            WHERE ticket_id = :ticket_id";
    
    $params = [
        ':department_id' => $department_id,
        ':user_id' => $user_id,
        ':ticket_id' => $ticket_id
    ];

    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute($params);

    // --- 4. إضافة تعليق تلقائي يفيد بتغيير الإسناد ---
    $assignee_text = "لا أحد";
    if ($department_id) {
        $stmt_dept = $pdo->prepare("SELECT name FROM departments WHERE department_id = ?");
        $stmt_dept->execute([$department_id]);
        $dept_name = $stmt_dept->fetchColumn();
        $assignee_text = "قسم '{$dept_name}'";
    }
    if ($user_id) {
        $stmt_user = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt_user->execute([$user_id]);
        $user_name = $stmt_user->fetchColumn();
        $assignee_text = "الموظف '{$user_name}'";
    }

    $comment_text = "قام " . $_SESSION['full_name'] . " بإسناد التذكرة إلى: " . $assignee_text . ".";
    
    $stmt_comment = $pdo->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt_comment->execute([$ticket_id, $current_user_id, $comment_text]);
    
    // يمكنك إضافة منطق إرسال إشعار هنا للموظف أو القسم الجديد

    $_SESSION['success_message'] = "تم تحديث إسناد التذكرة بنجاح.";

} catch (PDOException $e) {
    error_log("Ticket assignment update failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء تحديث الإسناد.";
}

// --- 5. إعادة التوجيه إلى صفحة التذكرة ---
header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}");
exit();