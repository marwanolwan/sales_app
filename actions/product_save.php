<?php
// actions/product_save.php

// 1. تضمين الملفات الأساسية
require_once '../core/db.php';
require_once '../core/functions.php';

// 2. التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=products");
    exit();
}

// 3. التحقق من الصلاحيات والـ CSRF
require_permission('manage_products');
verify_csrf_token();

// 4. استلام البيانات من النموذج
$action = $_POST['action'] ?? 'add';
$product_id = ($action == 'edit') ? (int)($_POST['product_id'] ?? null) : null;
$product_code = trim($_POST['product_code'] ?? '');
$name = trim($_POST['name'] ?? '');
$family_id = !empty($_POST['family_id']) ? (int)$_POST['family_id'] : null;
$unit = trim($_POST['unit'] ?? '');
$packaging_details = trim($_POST['packaging_details'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;
$current_image_path = $_POST['current_image_path'] ?? null;
$remove_image = isset($_POST['remove_image']) ? 1 : 0;

$image_dir = '../uploads/products_images/';
if (!is_dir($image_dir)) {
    mkdir($image_dir, 0775, true);
}
$is_new_product = isset($_POST['is_new_product']) ? 1 : 0;
$new_product_end_date = !empty($_POST['new_product_end_date']) ? $_POST['new_product_end_date'] :   null;
if ($is_new_product == 0) {
    $new_product_end_date = null;
}
// 5. التحقق من صحة المدخلات (Server-side validation)
$errors = [];
if (empty($product_code)) $errors[] = "رمز المنتج مطلوب.";
if (empty($name)) $errors[] = "اسم المنتج مطلوب.";
if (empty($unit)) $errors[] = "وحدة البيع مطلوبة.";

// إذا كانت هناك أخطاء، قم بإعادة التوجيه مع رسالة
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: ../index.php?page=products&action={$action}" . ($product_id ? "&id={$product_id}" : ''));
    exit();
}

$image_filename_to_save = $current_image_path;
$new_image_uploaded_path = null;

// 6. معالجة رفع الصورة الجديدة
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
    $file_info = $_FILES['product_image'];
    $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error_message'] = "امتداد ملف الصورة غير مسموح به.";
    } elseif ($file_info['size'] > $max_size) {
        $_SESSION['error_message'] = "حجم ملف الصورة يتجاوز 2MB.";
    } else {
        $new_image_filename = 'prod_' . time() . '_' . uniqid() . '.' . $file_extension;
        if (move_uploaded_file($file_info['tmp_name'], $image_dir . $new_image_filename)) {
            $image_filename_to_save = $new_image_filename;
            $new_image_uploaded_path = $image_dir . $new_image_filename; // لتسهيل الحذف عند الخطأ
        } else {
            $_SESSION['error_message'] = "فشل في رفع ملف الصورة. تحقق من صلاحيات المجلد.";
        }
    }

    if (isset($_SESSION['error_message'])) {
        header("Location: ../index.php?page=products&action={$action}" . ($product_id ? "&id={$product_id}" : ''));
        exit();
    }
}

// 7. تنفيذ الاستعلام في قاعدة البيانات
try {
    // التحقق من تكرار رمز المنتج
    $sql_check = "SELECT product_id FROM products WHERE product_code = ?";
    $params_check = [$product_code];
    if ($action == 'edit' && $product_id) {
        $sql_check .= " AND product_id != ?";
        $params_check[] = $product_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        if ($new_image_uploaded_path) unlink($new_image_uploaded_path); // حذف الصورة الجديدة إذا كان الرمز مكررًا
        $_SESSION['error_message'] = "رمز المنتج '{$product_code}' موجود بالفعل.";
        header("Location: ../index.php?page=products&action={$action}" . ($product_id ? "&id={$product_id}" : ''));
        exit();
    }

    // حذف الصورة القديمة إذا تم رفع صورة جديدة أو تم تحديد خيار الإزالة
    if (($new_image_uploaded_path || $remove_image) && $current_image_path && file_exists($image_dir . $current_image_path)) {
        unlink($image_dir . $current_image_path);
    }
    if ($remove_image) {
        $image_filename_to_save = null;
    }

 if ($action == 'add') {
        $sql = "INSERT INTO products (product_code, name, family_id, unit, packaging_details, product_image_path, is_active, is_new_product, new_product_end_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $product_code, $name, $family_id, $unit, $packaging_details, 
            $image_filename_to_save, $is_active, $is_new_product, $new_product_end_date
        ];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } elseif ($action == 'edit' && $product_id) {
        $sql = "UPDATE products SET product_code = ?, name = ?, family_id = ?, unit = ?, packaging_details = ?, product_image_path = ?, is_active = ?, is_new_product = ?, new_product_end_date = ?
                WHERE product_id = ?";
        $params = [
            $product_code, $name, $family_id, $unit, $packaging_details, 
            $image_filename_to_save, $is_active, $is_new_product, $new_product_end_date,
            $product_id
        ];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
} catch (PDOException $e) {
    if ($new_image_uploaded_path && file_exists($new_image_uploaded_path)) {
        unlink($new_image_uploaded_path); // حذف الصورة الجديدة عند حدوث خطأ في قاعدة البيانات
    }
    error_log("Product save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ المنتج.";
}

// 8. إعادة التوجيه إلى صفحة القائمة
header("Location: ../index.php?page=products");
exit();