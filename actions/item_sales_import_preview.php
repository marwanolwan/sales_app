<?php
// actions/item_sales_import_preview.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من وجود مكتبة PhpSpreadsheet
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    $_SESSION['error_message'] = "مكتبة PhpSpreadsheet غير موجودة. يرجى تثبيتها.";
    header("Location: ../index.php?page=item_sales"); 
    exit();
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=item_sales"); 
    exit();
}

// التحقق من الصلاحيات وتوكن CSRF
require_permission('manage_item_sales');
verify_csrf_token();

if (isset($_FILES['item_sales_excel_file']) && $_FILES['item_sales_excel_file']['error'] == UPLOAD_ERR_OK) {
    
    $temp_dir = '../temp_uploads/';
    if (!is_dir($temp_dir) && !mkdir($temp_dir, 0775, true)) {
        $_SESSION['error_message'] = "فشل في إنشاء المجلد المؤقت.";
        header("Location: ../index.php?page=item_sales"); 
        exit();
    }

    $file_path = $temp_dir . uniqid('item_sales_') . '.' . pathinfo($_FILES['item_sales_excel_file']['name'], PATHINFO_EXTENSION);
    if (!move_uploaded_file($_FILES['item_sales_excel_file']['tmp_name'], $file_path)) {
        $_SESSION['error_message'] = "فشل في نقل الملف المؤقت.";
        header("Location: ../index.php?page=item_sales"); 
        exit();
    }
    
    $_SESSION['import_file_path'] = $file_path;
    $preview_data = [];
    $import_errors = [];
    
    try {
        $spreadsheet = IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        // جلب البيانات اللازمة للتحقق دفعة واحدة
        $reps_map = $pdo->query("SELECT username, user_id FROM users WHERE role = 'representative' AND is_active = TRUE")->fetchAll(PDO::FETCH_KEY_PAIR);
        $customers_map = $pdo->query("SELECT customer_code, customer_id FROM customers WHERE status = 'active'")->fetchAll(PDO::FETCH_KEY_PAIR);
        $products_map = $pdo->query("SELECT product_code, product_id FROM products WHERE is_active = TRUE")->fetchAll(PDO::FETCH_KEY_PAIR);

        array_shift($rows); // إزالة صف العناوين

        foreach ($rows as $row_num => $row) {
            $year = trim($row['A'] ?? '');
            $month = trim($row['B'] ?? '');
            $customer_code = trim($row['C'] ?? '');
            $product_code = trim($row['D'] ?? '');
            $rep_username = trim($row['E'] ?? '');
            $quantity_sold = trim($row['F'] ?? '');
            $unit_price = trim($row['G'] ?? '');
            $total_value = trim($row['H'] ?? '');

            if (empty(implode('', [$year, $month, $customer_code, $product_code, $rep_username, $quantity_sold]))) continue;

            $row_errors = [];
            if (!filter_var($year, FILTER_VALIDATE_INT)) $row_errors[] = "سنة غير صالحة.";
            if (!filter_var($month, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]])) $row_errors[] = "شهر غير صالح.";
            if (!isset($customers_map[$customer_code])) $row_errors[] = "رمز العميل '{$customer_code}' غير موجود أو غير نشط.";
            if (!isset($products_map[$product_code])) $row_errors[] = "رمز الصنف '{$product_code}' غير موجود أو غير نشط.";
            if (!isset($reps_map[$rep_username])) $row_errors[] = "اسم مستخدم المندوب '{$rep_username}' غير موجود أو غير نشط.";
            if (!is_numeric($quantity_sold)) $row_errors[] = "الكمية المباعة يجب أن تكون رقمًا.";

            if (!empty($row_errors)) $import_errors[$row_num] = $row_errors;

            $preview_data[$row_num] = ['data' => compact('year', 'month', 'customer_code', 'product_code', 'rep_username', 'quantity_sold', 'unit_price', 'total_value')];
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = "خطأ في قراءة ملف Excel: " . $e->getMessage();
        header("Location: ../index.php?page=item_sales"); 
        exit();
    }
    
    $_SESSION['import_preview_data'] = ['data' => $preview_data, 'errors' => $import_errors];
    
    // **التصحيح الرئيسي هنا: تم تغيير 'item_sales_import' إلى 'item_sales'**
    header("Location: ../index.php?page=item_sales&action=import_preview");
    exit();

} else {
    $_SESSION['error_message'] = "الرجاء اختيار ملف Excel صالح.";
    header("Location: ../index.php?page=item_sales");
    exit();
}