<?php
// actions/monthly_sales_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=monthly_sales");
    exit();
}

require_permission('manage_monthly_sales');
verify_csrf_token();

$sale_id = isset($_POST['sale_id']) ? (int)$_POST['sale_id'] : null;

if (!$sale_id) {
    $_SESSION['error_message'] = "معرف سجل غير صالح.";
    header("Location: ../index.php?page=monthly_sales");
    exit();
}

try {
    $can_delete = false;
    if ($_SESSION['user_role'] == 'admin') {
        $can_delete = true;
    } elseif ($_SESSION['user_role'] == 'supervisor') {
       $stmt_check = $pdo->prepare("SELECT ms.sale_id FROM monthly_sales ms JOIN users u ON ms.representative_id = u.user_id WHERE ms.sale_id = ? AND u.supervisor_id = ?");
       $stmt_check->execute([$sale_id, $_SESSION['user_id']]);
       if ($stmt_check->fetch()) {
           $can_delete = true;
       }
    }
    
    if ($can_delete) {
        $stmt = $pdo->prepare("DELETE FROM monthly_sales WHERE sale_id = ?");
        $stmt->execute([$sale_id]);
        $_SESSION['success_message'] = "تم حذف سجل المبيعات بنجاح.";
    } else {
        $_SESSION['error_message'] = "ليس لديك صلاحية لحذف هذا السجل.";
    }
} catch (PDOException $e) {
    error_log("Monthly sale delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف سجل المبيعات.";
}

header("Location: ../index.php?page=monthly_sales");
exit();