<?php
// actions/product_family_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=product_families");
    exit();
}

require_permission('manage_product_families');
verify_csrf_token();

$family_id = isset($_POST['family_id']) ? (int)$_POST['family_id'] : null;

if (!$family_id) {
    $_SESSION['error_message'] = "معرف عائلة غير صالح.";
    header("Location: ../index.php?page=product_families");
    exit();
}

try {
    // تحقق مما إذا كانت العائلة مرتبطة بمنتجات
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE family_id = ?");
    $stmt_check->execute([$family_id]);
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error_message'] = "لا يمكن حذف العائلة لأنها مرتبطة بمنتجات حالية.";
    } else {
        // جلب مسار الشعار لحذفه
        $stmt_get_logo = $pdo->prepare("SELECT logo_image_path FROM product_families WHERE family_id = ?");
        $stmt_get_logo->execute([$family_id]);
        $logo_to_delete = $stmt_get_logo->fetchColumn();

        // حذف من قاعدة البيانات
        $stmt_delete = $pdo->prepare("DELETE FROM product_families WHERE family_id = ?");
        $stmt_delete->execute([$family_id]);

        // حذف ملف الشعار من الخادم
        if ($logo_to_delete && file_exists('../uploads/product_families_logos/' . $logo_to_delete)) {
            unlink('../uploads/product_families_logos/' . $logo_to_delete);
        }
        
        $_SESSION['success_message'] = "تم حذف عائلة المنتج بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Product family delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف عائلة المنتج.";
}

header("Location: ../index.php?page=product_families");
exit();