<?php
// actions/customer_delete.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=customers"); exit();
}

require_permission('manage_customers');
verify_csrf_token();

$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;

if (!$customer_id) {
    $_SESSION['error_message'] = "معرف العميل غير صالح.";
    header("Location: ../index.php?page=customers");
    exit();
}

try {
    // التحقق من وجود فروع مرتبطة
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE main_account_id = ?");
    $stmt_check->execute([$customer_id]);
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error_message'] = "لا يمكن حذف هذا الحساب الرئيسي لأنه مرتبط بفروع.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
        $stmt->execute([$customer_id]);
        $_SESSION['success_message'] = "تم حذف العميل بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Customer delete failed: " . $e->getMessage());
    if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
         $_SESSION['error_message'] = "لا يمكن حذف العميل لارتباطه بمعاملات أخرى.";
    } else {
        $_SESSION['error_message'] = "خطأ في حذف العميل.";
    }
}

header("Location: ../index.php?page=customers");
exit();