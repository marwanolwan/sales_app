<?php
// actions/user_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=users");
    exit();
}

require_permission('manage_users');
verify_csrf_token();

$user_id_to_delete = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;

if (!$user_id_to_delete) {
    $_SESSION['error_message'] = "معرف مستخدم غير صالح.";
    header("Location: ../index.php?page=users");
    exit();
}

if ($user_id_to_delete == 1) {
    $_SESSION['error_message'] = "لا يمكن حذف مدير النظام الرئيسي.";
} elseif ($user_id_to_delete == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "لا يمكنك حذف حسابك الخاص.";
} else {
    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE supervisor_id = ?");
        $stmt_check->execute([$user_id_to_delete]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['error_message'] = "لا يمكن حذف هذا المشرف لأنه مرتبط بمستخدمين آخرين.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id_to_delete]);
            $_SESSION['success_message'] = "تم حذف المستخدم بنجاح.";
        }
    } catch (PDOException $e) {
        error_log("User delete failed: " . $e->getMessage());
        $_SESSION['error_message'] = "خطأ في حذف المستخدم. قد يكون مرتبطًا ببيانات أخرى.";
    }
}

header("Location: ../index.php?page=users");
exit();