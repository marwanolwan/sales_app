<?php
// actions/monthly_sales_import_preview.php

require_once '../core/db.php';
require_once '../core/functions.php';

// تأكد من وجود مكتبة PhpSpreadsheet
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    $_SESSION['error_message'] = "مكتبة PhpSpreadsheet غير موجودة. يرجى تثبيتها.";
    header("Location: ../index.php?page=monthly_sales&action=import");
    exit();
}

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=monthly_sales&action=import");
    exit();
}

require_permission('manage_monthly_sales');
verify_csrf_token();

if (isset($_FILES['sales_excel_file']) && $_FILES['sales_excel_file']['error'] == UPLOAD_ERR_OK) {
    
    $temp_dir = '../temp_uploads/';
    if (!is_dir($temp_dir) && !mkdir($temp_dir, 0775, true) && !is_dir($temp_dir)) {
        $_SESSION['error_message'] = "فشل في إنشاء المجلد المؤقت.";
        header("Location: ../index.php?page=monthly_sales&action=import");
        exit();
    }

    $file_path = $temp_dir . uniqid('sales_import_') . '.' . pathinfo($_FILES['sales_excel_file']['name'], PATHINFO_EXTENSION);
    if (!move_uploaded_file($_FILES['sales_excel_file']['tmp_name'], $file_path)) {
        $_SESSION['error_message'] = "فشل في نقل الملف المؤقت.";
        header("Location: ../index.php?page=monthly_sales&action=import");
        exit();
    }
    
    $_SESSION['import_file_path'] = $file_path;
    $preview_data = [];
    $import_errors = [];
    
    try {
        $spreadsheet = IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        // جلب المندوبين المتاحين للمستخدم الحالي للتحقق
        $sql_reps = "SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE";
        $params_reps = [];
        if ($_SESSION['user_role'] == 'supervisor') {
            $sql_reps .= " AND supervisor_id = ?";
            $params_reps[] = $_SESSION['user_id'];
        }
        $stmt_reps = $pdo->prepare($sql_reps);
        $stmt_reps->execute($params_reps);
        $reps_map = $stmt_reps->fetchAll(PDO::FETCH_KEY_PAIR);

        $records_in_file = []; // لتتبع التكرار داخل الملف

        array_shift($rows); // إزالة صف العناوين دائماً للتبسيط

        foreach ($rows as $row_num => $row) {
            $rep_name = trim($row['A'] ?? '');
            $year = trim($row['B'] ?? '');
            $month = trim($row['C'] ?? '');
            $sales_amount = trim($row['D'] ?? '');
            $notes = trim($row['E'] ?? '');

            if (empty($rep_name) && empty($year) && empty($month) && empty($sales_amount)) continue;

            $row_errors = [];
            $rep_id = array_search($rep_name, $reps_map);

            if (empty($rep_name)) $row_errors[] = "اسم المندوب فارغ.";
            elseif ($rep_id === false) $row_errors[] = "المندوب '{$rep_name}' غير موجود أو لا تملك صلاحية عليه.";
            
            if (!filter_var($year, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => date('Y') + 5]])) {
                $row_errors[] = "سنة غير صالحة.";
            }
            if (!filter_var($month, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]])) {
                $row_errors[] = "شهر غير صالح.";
            }
            if (!is_numeric($sales_amount) || (float)$sales_amount < 0) {
                 $row_errors[] = "مبلغ المبيعات غير صالح.";
            }

            if ($rep_id && $year && $month) {
                $unique_key = "{$rep_id}-{$year}-{$month}";
                if (isset($records_in_file[$unique_key])) {
                    $row_errors[] = "سجل مكرر في الملف (الصف {$records_in_file[$unique_key]}).";
                } else {
                    $records_in_file[$unique_key] = $row_num;
                    $stmt = $pdo->prepare("SELECT sale_id FROM monthly_sales WHERE representative_id = ? AND year = ? AND month = ?");
                    $stmt->execute([$rep_id, $year, $month]);
                    if ($stmt->fetch()) $row_errors[] = "سجل موجود بالفعل في قاعدة البيانات.";
                }
            }

            if (!empty($row_errors)) $import_errors[$row_num] = $row_errors;

            $preview_data[$row_num] = [
                'data' => [
                    'rep_name' => $rep_name, 'year' => $year, 'month' => $month, 
                    'sales_amount' => $sales_amount, 'notes' => $notes
                ]
            ];
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = "خطأ في قراءة ملف Excel: " . $e->getMessage();
        header("Location: ../index.php?page=monthly_sales&action=import");
        exit();
    }
    
    $_SESSION['import_preview_data'] = ['data' => $preview_data, 'errors' => $import_errors];
    header("Location: ../index.php?page=monthly_sales&action=import_preview");
    exit();

} else {
    $_SESSION['error_message'] = "الرجاء اختيار ملف Excel صالح.";
    header("Location: ../index.php?page=monthly_sales&action=import");
    exit();
}