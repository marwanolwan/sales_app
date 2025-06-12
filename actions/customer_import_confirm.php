<?php
// actions/customer_import_confirm.php
require_once '../vendor/autoload.php';
require_once '../core/db.php';
require_once '../core/functions.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=customers&action=import"); exit();
}
require_permission('manage_customers');
verify_csrf_token();

define('IMPORT_TEMP_DIR', '../temp_uploads/');
$file_name = $_POST['confirmed_file_name'] ?? '';
$file_path = IMPORT_TEMP_DIR . $file_name;

if (empty($file_name) || !file_exists($file_path)) {
    $_SESSION['error_message'] = "لم يتم العثور على ملف للاستيراد.";
    header("Location: ../index.php?page=customers&action=import"); exit();
}

try {
    $spreadsheet = IOFactory::load($file_path);
    $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    if (isset($data[1]) && (strtolower(trim($data[1]['A'])) == 'رمز العميل')) unset($data[1]);

    // Pre-fetch necessary data maps to avoid queries in loop
    $categories_map = $pdo->query("SELECT name, category_id FROM customer_categories")->fetchAll(PDO::FETCH_KEY_PAIR);
    $reps_map = $pdo->query("SELECT full_name, user_id FROM users WHERE role='representative'")->fetchAll(PDO::FETCH_KEY_PAIR);
    $promoters_map = $pdo->query("SELECT full_name, user_id FROM users WHERE role='promoter'")->fetchAll(PDO::FETCH_KEY_PAIR);
    $existing_codes_in_db = $pdo->query("SELECT customer_code FROM customers")->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();
    $sql = "INSERT INTO customers (customer_code, name, category_id, address, representative_id, promoter_id, latitude, longitude, status, opening_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    $imported_count = 0;
    $failed_rows = [];
    $codes_in_file = [];

    foreach ($data as $row_key => $row) {
        if (empty(array_filter($row))) continue;
        
        $customer_code = trim($row['A'] ?? '');
        $name = trim($row['B'] ?? '');
        $category_name = trim($row['C'] ?? '');
        $address = trim($row['D'] ?? '');
        $rep_name = trim($row['E'] ?? '');
        $promoter_name = trim($row['F'] ?? '');
        $latitude = filter_var($row['G'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        $longitude = filter_var($row['H'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        $status = (strtolower(trim($row['I'] ?? '')) === 'inactive') ? 'inactive' : 'active';
        $opening_date_raw = trim($row['J'] ?? '');

        // Server-side re-validation
        if (empty($customer_code) || empty($name) || in_array($customer_code, $existing_codes_in_db) || isset($codes_in_file[$customer_code])) {
            $failed_rows[] = "الصف {$row_key} (الرمز: {$customer_code}): خطأ في الرمز أو الاسم أو تكرار.";
            continue;
        }

        $category_id = isset($categories_map[$category_name]) ? $categories_map[$category_name] : null;
        $rep_id = isset($reps_map[$rep_name]) ? $reps_map[$rep_name] : null;
        $promoter_id = isset($promoters_map[$promoter_name]) ? $promoters_map[$promoter_name] : null;

        $opening_date = null;
        if (!empty($opening_date_raw)) {
            if (is_numeric($opening_date_raw)) {
                $opening_date = Date::excelToDateTimeObject($opening_date_raw)->format('Y-m-d');
            } else {
                $opening_date = date('Y-m-d', strtotime($opening_date_raw));
            }
        }
        
        $stmt->execute([
            $customer_code, $name, $category_id, $address, $rep_id, $promoter_id, 
            $latitude, $longitude, $status, $opening_date
        ]);
        
        $codes_in_file[$customer_code] = true;
        $imported_count++;
    }

    $pdo->commit();
    $_SESSION['success_message'] = "تم استيراد {$imported_count} عميل بنجاح.";
    if (!empty($failed_rows)) {
        $_SESSION['warning_message'] = "الصفوف التالية لم تستورد لوجود أخطاء: <br>" . implode('<br>', $failed_rows);
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['error_message'] = "حدث خطأ فادح أثناء الاستيراد: " . $e->getMessage();
} finally {
    @unlink($file_path);
}

header("Location: ../index.php?page=customers");
exit();