<?php
// actions/asset_type_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=assets&action=types"); 
    exit();
}

// التحقق من الصلاحيات وتوكن الحماية
require_permission('manage_assets'); // نفترض أن نفس الصلاحية تدير الأنواع
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$action = $_POST['action'] ?? 'add_type';
$type_id = ($action == 'edit_type') ? (int)($_POST['type_id'] ?? null) : null;
$type_name = trim($_POST['type_name'] ?? '');
$description = trim($_POST['description'] ?? '');

// --- 2. التحقق من صحة البيانات ---
if (empty($type_name)) {
    $_SESSION['error_message'] = "اسم نوع الأصل مطلوب.";
    // إعادة التوجيه إلى نفس الصفحة مع الحفاظ على السياق
    header("Location: ../index.php?page=assets&action={$action}" . ($type_id ? "&type_id={$type_id}" : ''));
    exit();
}

// --- 3. تنفيذ عملية الحفظ في قاعدة البيانات ---
try {
    // التحقق من عدم تكرار الاسم
    $sql_check = "SELECT type_id FROM asset_types WHERE type_name = ?";
    $params_check = [$type_name];
    if ($action == 'edit_type' && $type_id) {
        $sql_check .= " AND type_id != ?";
        $params_check[] = $type_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "اسم نوع الأصل موجود بالفعل.";
        header("Location: ../index.php?page=assets&action={$action}" . ($type_id ? "&type_id={$type_id}" : ''));
        exit();
    }

    // تنفيذ الإضافة أو التحديث
    if ($action == 'add_type') {
        $sql = "INSERT INTO asset_types (type_name, description) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$type_name, $description]);
        $_SESSION['success_message'] = "تم إضافة نوع الأصل بنجاح.";
    } elseif ($action == 'edit_type' && $type_id) {
        $sql = "UPDATE asset_types SET type_name = ?, description = ? WHERE type_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$type_name, $description, $type_id]);
        $_SESSION['success_message'] = "تم تحديث نوع الأصل بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Asset type save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ نوع الأصل.";
}

// --- 4. إعادة التوجيه إلى قائمة أنواع الأصول ---
header("Location: ../index.php?page=assets&action=types");
exit();