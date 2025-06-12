<?php
require_once '../core/db.php';
require_once '../core/functions.php';
// require_permission('manage_pricing');
verify_csrf_token();
$product_id = (int)($_POST['product_id'] ?? 0);
if ($product_id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE price_offers SET status = 'archived' WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $_SESSION['success_message'] = "تمت أرشفة تسعيرات الصنف بنجاح.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "فشلت الأرشفة.";
    }
}
header("Location: ../index.php?page=pricing");
exit();