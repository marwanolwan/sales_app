<?php
// actions/customer_import_preview.php
require_once '../vendor/autoload.php';
require_once '../core/db.php';
require_once '../core/functions.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=customers&action=import"); exit();
}
require_permission('manage_customers');
verify_csrf_token();

define('IMPORT_TEMP_DIR', '../temp_uploads/');
if (!is_dir(IMPORT_TEMP_DIR) && !mkdir(IMPORT_TEMP_DIR, 0775, true)) {
    $_SESSION['error_message'] = "فشل في إنشاء المجلد المؤقت للاستيراد.";
    header("Location: ../index.php?page=customers&action=import"); exit();
}

if (isset($_FILES['customer_excel_file']) && $_FILES['customer_excel_file']['error'] == UPLOAD_ERR_OK) {
    $file_tmp_path = $_FILES['customer_excel_file']['tmp_name'];
    $file_name = 'cust_import_' . uniqid() . '.' . pathinfo($_FILES['customer_excel_file']['name'], PATHINFO_EXTENSION);
    $destination_path = IMPORT_TEMP_DIR . $file_name;

    if (move_uploaded_file($file_tmp_path, $destination_path)) {
        try {
            $spreadsheet = IOFactory::load($destination_path);
            $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            // Remove header row if it exists
            if (isset($data[1]) && (strtolower(trim($data[1]['A'])) == 'رمز العميل' || strtolower(trim($data[1]['A'])) == 'customer_code')) {
                unset($data[1]);
            }
            
            $preview_data = $data;
            $import_errors = [];
            
            $existing_codes_in_db = $pdo->query("SELECT customer_code FROM customers")->fetchAll(PDO::FETCH_COLUMN);
            $codes_in_file = [];

            foreach ($data as $row_key => $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows

                $row_errors = [];
                $customer_code = trim($row['A'] ?? '');
                
                if (empty($customer_code)) $row_errors[] = "رمز العميل فارغ";
                if (empty(trim($row['B'] ?? ''))) $row_errors[] = "اسم العميل فارغ";

                if (!empty($customer_code)) {
                    if (in_array($customer_code, $existing_codes_in_db)) $row_errors[] = "الرمز موجود بالفعل في النظام";
                    if (isset($codes_in_file[$customer_code])) $row_errors[] = "الرمز مكرر في الملف (صف {$codes_in_file[$customer_code]})";
                    $codes_in_file[$customer_code] = $row_key;
                }

                if (!empty($row_errors)) {
                    $import_errors[$row_key] = [
                        'summary' => "الصف {$row_key} (الرمز: {$customer_code})",
                        'details' => $row_errors
                    ];
                }
            }

            $_SESSION['import_preview_data'] = $preview_data;
            $_SESSION['import_errors'] = $import_errors;
            $_SESSION['import_file_name'] = $file_name;

            header("Location: ../index.php?page=customers&action=import_preview");
            exit();

        } catch (Exception $e) {
            $_SESSION['error_message'] = "خطأ في قراءة ملف Excel: " . $e->getMessage();
            @unlink($destination_path);
        }
    } else { $_SESSION['error_message'] = "فشل في نقل الملف."; }
} else { $_SESSION['error_message'] = "خطأ في تحميل الملف."; }

header("Location: ../index.php?page=customers&action=import");
exit();