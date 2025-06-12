<?php
// actions/pricing_delete_archived.php
require_once '../core/db.php';
require_once '../core/functions.php';

// require_permission('manage_pricing');
verify_csrf_token();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

if ($product_id > 0) {
    try {
        // حذف جميع العروض المؤرشفة لهذا المنتج نهائيًا
        $stmt = $pdo->prepare("DELETE FROM price_offers WHERE product_id = ? AND status = 'archived'");
        $stmt->execute([$product_id]);
        $_SESSION['success_message'] = "تم حذف التسعيرات المؤرشفة للصنف نهائيًا.";
    } catch (Exception $e) {
        error_log("Archived pricing delete failed: " . $e->getMessage());
        $_SESSION['error_message'] = "فشل الحذف النهائي.";
    }
} else {
    $_SESSION['error_message'] = "معرف منتج غير صالح.";
}

// العودة إلى صفحة الأرشيف
header("Location: ../index.php?page=pricing&action=archived");
exit();