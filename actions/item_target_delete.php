<?php
// actions/item_target_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=item_targets");
    exit();
}

require_permission('manage_item_targets');
verify_csrf_token();

$item_target_id = isset($_POST['item_target_id']) ? (int)$_POST['item_target_id'] : null;

if (!$item_target_id) {
    $_SESSION['error_message'] = "معرف الهدف غير صالح.";
    header("Location: ../index.php?page=item_targets");
    exit();
}

try {
    $can_delete = false;
    if ($_SESSION['user_role'] == 'admin') {
        $can_delete = true;
    } elseif ($_SESSION['user_role'] == 'supervisor') {
       $stmt_check = $pdo->prepare("SELECT it.item_target_id FROM item_sales_targets it JOIN users u ON it.representative_id = u.user_id WHERE it.item_target_id = ? AND u.supervisor_id = ?");
       $stmt_check->execute([$item_target_id, $_SESSION['user_id']]);
       if ($stmt_check->fetch()) {
           $can_delete = true;
       }
    }
    
    if ($can_delete) {
        $stmt = $pdo->prepare("DELETE FROM item_sales_targets WHERE item_target_id = ?");
        $stmt->execute([$item_target_id]);
        $_SESSION['success_message'] = "تم حذف الهدف بنجاح.";
    } else {
        $_SESSION['error_message'] = "ليس لديك صلاحية لحذف هذا الهدف.";
    }
} catch (PDOException $e) {
    error_log("Item target delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف الهدف.";
}

header("Location: ../index.php?page=item_targets");
exit();