<?php
// actions/item_sales_import_confirm.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=item_sales"); 
    exit();
}

require_permission('manage_item_sales');
verify_csrf_token();

$file_path = $_SESSION['import_file_path'] ?? null;
$preview_data = $_SESSION['import_preview_data']['data'] ?? [];
$import_errors = $_SESSION['import_preview_data']['errors'] ?? [];

// **التصحيح هنا: تم تغيير 'item_sales_import' إلى 'item_sales'**
$redirect_page = '../index.php?page=item_sales';

// تنظيف الجلسة والملف المؤقت دائمًا
if ($file_path && file_exists($file_path)) {
    unlink($file_path);
}
unset($_SESSION['import_file_path'], $_SESSION['import_preview_data']);

if (empty($preview_data)) {
    $_SESSION['error_message'] = "انتهت صلاحية جلسة الاستيراد.";
    header("Location: {$redirect_page}"); 
    exit();
}

// إعادة جلب الخرائط للتأكيد النهائي
$reps_map = $pdo->query("SELECT username, user_id FROM users WHERE role = 'representative' AND is_active = TRUE")->fetchAll(PDO::FETCH_KEY_PAIR);
$customers_map = $pdo->query("SELECT customer_code, customer_id FROM customers WHERE status = 'active'")->fetchAll(PDO::FETCH_KEY_PAIR);
$products_map = $pdo->query("SELECT product_code, product_id FROM products WHERE is_active = TRUE")->fetchAll(PDO::FETCH_KEY_PAIR);

$imported_count = 0;
$failed_count = 0;
$import_batch_id = 'BATCH-' . strtoupper(uniqid());

try {
    $pdo->beginTransaction();
    $sql = "INSERT INTO monthly_item_sales (year, month, customer_id, product_id, representative_id, quantity_sold, unit_price, total_value, import_batch_id, recorded_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    foreach ($preview_data as $row_num => $row) {
        if (isset($import_errors[$row_num])) {
            $failed_count++;
            continue;
        }
        
        $data = $row['data'];
        $customer_id = $customers_map[$data['customer_code']] ?? false;
        $product_id = $products_map[$data['product_code']] ?? false;
        $rep_id = $reps_map[$data['rep_username']] ?? false;

        if ($customer_id === false || $product_id === false || $rep_id === false) {
            $failed_count++;
            continue;
        }
        
        $unit_price_val = is_numeric($data['unit_price']) ? (float)$data['unit_price'] : null;
        $total_value_val = is_numeric($data['total_value']) ? (float)$data['total_value'] : null;

        $stmt->execute([
            (int)$data['year'], (int)$data['month'], $customer_id, $product_id, $rep_id,
            (float)$data['quantity_sold'], $unit_price_val, $total_value_val,
            $import_batch_id, $_SESSION['user_id']
        ]);
        $imported_count++;
    }

    $pdo->commit();
    
    if ($imported_count > 0) $_SESSION['success_message'] = "تم استيراد {$imported_count} سجل بنجاح. معرف الدفعة: " . $import_batch_id;
    if ($failed_count > 0) $_SESSION['warning_message'] = "تم تجاهل {$failed_count} سجل بسبب وجود أخطاء.";
    if ($imported_count == 0 && $failed_count > 0) $_SESSION['error_message'] = "لم يتم استيراد أي سجلات.";

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Item sales import confirm failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء الاستيراد.";
}

header("Location: {$redirect_page}");
exit();