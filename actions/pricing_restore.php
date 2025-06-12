<?php
// actions/pricing_restore.php
require_once '../core/db.php';
require_once '../core/functions.php';

// require_permission('manage_pricing');
verify_csrf_token();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

if ($product_id > 0) {
    try {
        // استعادة جميع العروض المؤرشفة لهذا المنتج إلى الحالة النشطة
        $stmt = $pdo->prepare("UPDATE price_offers SET status = 'active' WHERE product_id = ? AND status = 'archived'");
        $stmt->execute([$product_id]);
        $_SESSION['success_message'] = "تم استعادة تسعيرات الصنف بنجاح.";
    } catch (Exception $e) {
        error_log("Pricing restore failed: " . $e->getMessage());
        $_SESSION['error_message'] = "فشلت عملية الاستعادة.";
    }
} else {
    $_SESSION['error_message'] = "معرف منتج غير صالح.";
}

// العودة إلى صفحة الأرشيف
header("Location: ../index.php?page=pricing&action=archived");
exit();