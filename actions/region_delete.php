<?php
// actions/region_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=regions");
    exit();
}

require_permission('manage_regions');
verify_csrf_token();

$region_id = isset($_POST['region_id']) ? (int)$_POST['region_id'] : null;

if (!$region_id) {
    $_SESSION['error_message'] = "معرف منطقة غير صالح.";
    header("Location: ../index.php?page=regions");
    exit();
}

try {
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE region_id = ?");
    $stmt_check->execute([$region_id]);
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error_message'] = "لا يمكن حذف المنطقة لأنها مرتبطة بمستخدمين.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM regions WHERE region_id = ?");
        $stmt->execute([$region_id]);
        $_SESSION['success_message'] = "تم حذف المنطقة بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Region delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف المنطقة.";
}

header("Location: ../index.php?page=regions");
exit();