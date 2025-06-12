<?php
// actions/product_import_preview.php

require_once '../core/db.php';
require_once '../core/functions.php';
require_once '../vendor/autoload.php'; // لمكتبة PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=products&action=import");
    exit();
}

require_permission('manage_products');
verify_csrf_token();

if (isset($_FILES['product_excel_file']) && $_FILES['product_excel_file']['error'] == UPLOAD_ERR_OK) {
    
    $temp_dir = '../temp_uploads/';
    if (!is_dir($temp_dir)) mkdir($temp_dir, 0775, true);

    $file_path = $temp_dir . uniqid('prod_import_') . '.xlsx';
    if (!move_uploaded_file($_FILES['product_excel_file']['tmp_name'], $file_path)) {
        $_SESSION['error_message'] = "فشل في نقل الملف المؤقت.";
        header("Location: ../index.php?page=products&action=import");
        exit();
    }
    
    $_SESSION['import_file_path'] = $file_path;
    $preview_data = [];
    $import_errors = [];
    
    try {
        $spreadsheet = IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // جلب عائلات المنتجات للتحقق
        $families_map = $pdo->query("SELECT name, family_id FROM product_families")->fetchAll(PDO::FETCH_KEY_PAIR);
        $codes_in_file = [];

        $is_header = true;
        foreach ($rows as $row_num => $row) {
            if ($is_header) {
                $is_header = false;
                if (strtolower(trim($row['A'] ?? '')) == 'رمز المنتج' || strtolower(trim($row['A'] ?? '')) == 'product_code') {
                    continue; // تخطي صف العناوين
                }
            }
            
            $product_code = trim($row['A'] ?? '');
            $name = trim($row['B'] ?? '');
            $family_name = trim($row['C'] ?? '');
            $unit = trim($row['D'] ?? '');
            $packaging = trim($row['E'] ?? '');
            $status_text = strtolower(trim($row['F'] ?? 'فعال'));
            
            if (empty($product_code) && empty($name) && empty($unit)) continue; // تخطي الصفوف الفارغة

            $row_errors = [];
            // التحقق من صحة البيانات
            if (empty($product_code)) $row_errors[] = "الصف {$row_num}: رمز المنتج فارغ.";
            if (empty($name)) $row_errors[] = "الصف {$row_num}: اسم المنتج فارغ.";
            if (empty($unit)) $row_errors[] = "الصف {$row_num}: وحدة البيع فارغة.";
            
            if (!empty($family_name) && !isset($families_map[$family_name])) {
                $row_errors[] = "الصف {$row_num}: عائلة المنتج '{$family_name}' غير موجودة.";
            }
            if (isset($codes_in_file[$product_code])) {
                $row_errors[] = "الصف {$row_num}: رمز المنتج '{$product_code}' مكرر في الملف.";
            } else {
                $codes_in_file[$product_code] = true;
                $stmt = $pdo->prepare("SELECT product_id FROM products WHERE product_code = ?");
                $stmt->execute([$product_code]);
                if ($stmt->fetch()) $row_errors[] = "الصف {$row_num}: رمز المنتج '{$product_code}' موجود بالفعل في النظام.";
            }
            
            if (!empty($row_errors)) {
                $import_errors[$row_num] = $row_errors;
            }

            $preview_data[$row_num] = [
                'row_num' => $row_num,
                'data' => [
                    'product_code' => $product_code,
                    'name' => $name,
                    'family_name' => $family_name,
                    'unit' => $unit,
                    'packaging_details' => $packaging,
                    'is_active_text' => $status_text,
                ]
            ];
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = "خطأ في قراءة ملف Excel: " . $e->getMessage();
        header("Location: ../index.php?page=products&action=import");
        exit();
    }
    
    $_SESSION['import_preview_data'] = [
        'data' => $preview_data,
        'errors' => $import_errors,
    ];

    header("Location: ../index.php?page=products&action=import_preview");
    exit();

} else {
    $_SESSION['error_message'] = "الرجاء اختيار ملف Excel صالح.";
    header("Location: ../index.php?page=products&action=import");
    exit();
}