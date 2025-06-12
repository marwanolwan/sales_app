<?php
// actions/product_family_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=product_families");
    exit();
}

require_permission('manage_product_families');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$family_id = ($action == 'edit') ? (int)($_POST['family_id'] ?? null) : null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;
$current_logo_path = $_POST['current_logo_path'] ?? null;
$remove_logo = isset($_POST['remove_logo']) ? 1 : 0;

$logo_dir = '../uploads/product_families_logos/';
if (!is_dir($logo_dir)) {
    mkdir($logo_dir, 0775, true);
}

// التحقق من صحة المدخلات
if (empty($name)) {
    $_SESSION['error_message'] = "اسم عائلة المنتج مطلوب.";
    header("Location: ../index.php?page=product_families&action={$action}" . ($family_id ? "&id={$family_id}" : ''));
    exit();
}

$logo_filename_to_save = $current_logo_path;
$new_logo_uploaded = null;

// معالجة رفع الشعار الجديد
if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == UPLOAD_ERR_OK) {
    $file_info = $_FILES['logo_image'];
    $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error_message'] = "امتداد ملف الشعار غير مسموح به.";
        header("Location: ../index.php?page=product_families&action={$action}" . ($family_id ? "&id={$family_id}" : ''));
        exit();
    }
    
    $new_logo_filename = 'logo_' . time() . '_' . uniqid() . '.' . $file_extension;
    if (move_uploaded_file($file_info['tmp_name'], $logo_dir . $new_logo_filename)) {
        $logo_filename_to_save = $new_logo_filename;
        $new_logo_uploaded = $logo_dir . $new_logo_filename; // لتسهيل الحذف عند الخطأ
    } else {
        $_SESSION['error_message'] = "فشل في رفع ملف الشعار.";
        header("Location: ../index.php?page=product_families&action={$action}" . ($family_id ? "&id={$family_id}" : ''));
        exit();
    }
}

try {
    // التحقق من تكرار الاسم
    $sql_check = "SELECT family_id FROM product_families WHERE name = ?";
    $params_check = [$name];
    if ($action == 'edit' && $family_id) {
        $sql_check .= " AND family_id != ?";
        $params_check[] = $family_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        if ($new_logo_uploaded) unlink($new_logo_uploaded); // حذف الشعار الجديد إذا كان الاسم مكررًا
        $_SESSION['error_message'] = "اسم عائلة المنتج موجود بالفعل.";
        header("Location: ../index.php?page=product_families&action={$action}" . ($family_id ? "&id={$family_id}" : ''));
        exit();
    }

    // حذف الشعار القديم إذا تم رفع شعار جديد أو تم تحديد خيار الإزالة
    if (($new_logo_uploaded || $remove_logo) && $current_logo_path && file_exists($logo_dir . $current_logo_path)) {
        unlink($logo_dir . $current_logo_path);
    }
    if ($remove_logo) {
        $logo_filename_to_save = null;
    }

    if ($action == 'add') {
        $sql = "INSERT INTO product_families (name, description, logo_image_path, is_active) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $logo_filename_to_save, $is_active]);
        $_SESSION['success_message'] = "تم إضافة العائلة بنجاح.";
    } elseif ($action == 'edit' && $family_id) {
        $sql = "UPDATE product_families SET name = ?, description = ?, logo_image_path = ?, is_active = ? WHERE family_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $logo_filename_to_save, $is_active, $family_id]);
        $_SESSION['success_message'] = "تم تحديث العائلة بنجاح.";
    }

} catch (PDOException $e) {
    if ($new_logo_uploaded) unlink($new_logo_uploaded); // حذف الشعار الجديد عند حدوث خطأ في قاعدة البيانات
    error_log("Product family save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=product_families");
exit();