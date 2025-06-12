<?php
// actions/product_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=products");
    exit();
}

require_permission('manage_products');
verify_csrf_token();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

if (!$product_id) {
    $_SESSION['error_message'] = "معرف منتج غير صالح.";
    header("Location: ../index.php?page=products");
    exit();
}

try {
    // جلب مسار الصورة لحذفها
    $stmt_get_img = $pdo->prepare("SELECT product_image_path FROM products WHERE product_id = ?");
    $stmt_get_img->execute([$product_id]);
    $img_to_delete = $stmt_get_img->fetchColumn();

    // حذف من قاعدة البيانات
    $stmt_delete = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt_delete->execute([$product_id]);

    // حذف ملف الصورة من الخادم
    if ($img_to_delete && file_exists('../uploads/products_images/' . $img_to_delete)) {
        unlink('../uploads/products_images/' . $img_to_delete);
    }
    
    $_SESSION['success_message'] = "تم حذف المنتج بنجاح.";

} catch (PDOException $e) {
    error_log("Product delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف المنتج. قد يكون مرتبطًا بسجلات مبيعات.";
}

header("Location: ../index.php?page=products");
exit();