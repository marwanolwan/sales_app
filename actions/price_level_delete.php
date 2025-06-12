<?php
require_once '../core/db.php';
require_once '../core/functions.php';
// require_permission('manage_pricing');
verify_csrf_token();
$product_id = (int)($_POST['product_id'] ?? 0);
if ($product_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM price_offers WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $_SESSION['success_message'] = "تم حذف تسعيرات الصنف نهائياً.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "فشل الحذف النهائي.";
    }
}
header("Location: ../index.php?page=pricing");
exit();