<?php
// actions/asset_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=assets"); 
    exit();
}

// التحقق من الصلاحيات وتوكن الحماية
require_permission('manage_assets');
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$action = $_POST['action'] ?? 'add';
$asset_id = ($action == 'edit') ? (int)($_POST['asset_id'] ?? null) : null;

$serial_number = trim($_POST['serial_number'] ?? '');
$type_id = (int)($_POST['type_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? '';

// --- 2. التحقق من صحة البيانات ---
$errors = [];
if (empty($serial_number)) {
    $errors[] = "الرقم التسلسلي مطلوب.";
}
if (empty($type_id)) {
    $errors[] = "يجب اختيار نوع الأصل.";
}
$allowed_statuses = ['In Warehouse','With Customer','Under Maintenance','Retired'];
if (!in_array($status, $allowed_statuses)) {
    $errors[] = "حالة الأصل المحددة غير صالحة.";
}

// معالجة الحقول التي تعتمد على الحالة
$customer_id = null;
$deployed_date = null;

if ($status === 'With Customer') {
    $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
    $deployed_date = !empty($_POST['deployed_date']) ? $_POST['deployed_date'] : null;
    if (empty($customer_id)) {
        $errors[] = "يجب تحديد العميل عندما تكون حالة الأصل 'لدى العميل'.";
    }
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: ../index.php?page=assets&action={$action}" . ($asset_id ? "&id={$asset_id}" : ''));
    exit();
}

// --- 3. تنفيذ عملية الحفظ في قاعدة البيانات ---
try {
    // التحقق من عدم تكرار الرقم التسلسلي
    $sql_check = "SELECT asset_id FROM assets WHERE serial_number = ?";
    $params_check = [$serial_number];
    if ($action == 'edit' && $asset_id) {
        $sql_check .= " AND asset_id != ?";
        $params_check[] = $asset_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "الرقم التسلسلي '{$serial_number}' موجود بالفعل لأصل آخر.";
        header("Location: ../index.php?page=assets&action={$action}" . ($asset_id ? "&id={$asset_id}" : ''));
        exit();
    }

    // تنفيذ الإضافة أو التحديث
    if ($action == 'add') {
        $sql = "INSERT INTO assets (serial_number, type_id, description, status, customer_id, deployed_date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$serial_number, $type_id, $description, $status, $customer_id, $deployed_date];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم إضافة الأصل بنجاح.";

    } elseif ($action == 'edit' && $asset_id) {
        $sql = "UPDATE assets SET 
                    serial_number = ?, 
                    type_id = ?, 
                    description = ?, 
                    status = ?, 
                    customer_id = ?, 
                    deployed_date = ? 
                WHERE asset_id = ?";
        $params = [$serial_number, $type_id, $description, $status, $customer_id, $deployed_date, $asset_id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم تحديث بيانات الأصل بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Asset save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ الأصل.";
    // إعادة التوجيه إلى نفس الصفحة في حالة حدوث خطأ للحفاظ على البيانات المدخلة
    header("Location: ../index.php?page=assets&action={$action}" . ($asset_id ? "&id={$asset_id}" : ''));
    exit();
}

// --- 4. إعادة التوجيه إلى قائمة الأصول الرئيسية ---
header("Location: ../index.php?page=assets");
exit();