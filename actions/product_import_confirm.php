<?php
// actions/product_import_confirm.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=products&action=import");
    exit();
}

require_permission('manage_products');
verify_csrf_token();

$file_path = $_SESSION['import_file_path'] ?? null;
$preview_data = $_SESSION['import_preview_data']['data'] ?? [];
$import_errors = $_SESSION['import_preview_data']['errors'] ?? [];

if (!$file_path || !file_exists($file_path) || empty($preview_data)) {
    $_SESSION['error_message'] = "انتهت صلاحية جلسة الاستيراد أو لا توجد بيانات صالحة. يرجى المحاولة مرة أخرى.";
    header("Location: ../index.php?page=products&action=import");
    exit();
}

$families_map = $pdo->query("SELECT name, family_id FROM product_families")->fetchAll(PDO::FETCH_KEY_PAIR);
$imported_count = 0;
$failed_count = 0;

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO products (product_code, name, family_id, unit, packaging_details, is_active) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    foreach ($preview_data as $row_num => $row) {
        if (isset($import_errors[$row_num])) {
            $failed_count++;
            continue; // تخطي الصفوف التي بها أخطاء
        }
        
        $data = $row['data'];
        $family_id = isset($families_map[$data['family_name']]) ? $families_map[$data['family_name']] : null;
        $status_text = strtolower($data['is_active_text']);
        $is_active = in_array($status_text, ['فعال', 'active', '1']);

        $stmt->execute([
            $data['product_code'],
            $data['name'],
            $family_id,
            $data['unit'],
            $data['packaging_details'],
            $is_active
        ]);
        $imported_count++;
    }

    $pdo->commit();
    
    $_SESSION['success_message'] = "تم استيراد {$imported_count} منتج بنجاح.";
    if ($failed_count > 0) {
        $_SESSION['warning_message'] = "تم تجاهل {$failed_count} صف بسبب وجود أخطاء.";
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Product import confirm failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء الاستيراد.";
} finally {
    // تنظيف الجلسة والملف المؤقت
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    unset($_SESSION['import_file_path']);
    unset($_SESSION['import_preview_data']);
}

header("Location: ../index.php?page=products");
exit();