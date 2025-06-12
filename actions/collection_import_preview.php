<?php
// actions/collection_import_preview.php

// 1. تضمين الملفات الأساسية
require_once '../core/db.php';
require_once '../core/functions.php';

// 2. التحقق من الصلاحيات والـ CSRF
require_permission('manage_collections');
verify_csrf_token();

// 3. تأكد من وجود مكتبة PhpSpreadsheet
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    $_SESSION['error_message'] = "خطأ حرج: مكتبة PhpSpreadsheet غير موجودة. يرجى تثبيتها عبر Composer.";
    header("Location: ../index.php?page=collections&action=import");
    exit();
}
use PhpOffice\PhpSpreadsheet\IOFactory;

// 4. تحديد روابط إعادة التوجيه
$redirect_url_on_error = "../index.php?page=collections&action=import";
$redirect_url_on_success = "../index.php?page=collections&action=import_preview";

// 5. التحقق من رفع الملف
if (!isset($_FILES['collection_excel_file']) || $_FILES['collection_excel_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_message'] = "خطأ في رفع الملف أو لم يتم اختيار ملف. رمز الخطأ: " . ($_FILES['collection_excel_file']['error'] ?? 'N/A');
    header("Location: {$redirect_url_on_error}");
    exit();
}

/**
 * دالة لمعالجة وتوحيد الأسماء العربية.
 * @param string $name الاسم المراد معالجته.
 * @return string الاسم بعد المعالجة.
 */
function normalize_arabic_name($name) {
    $name = trim($name);
    $name = str_replace(['ى', 'ة', 'أ', 'إ', 'آ'], ['ي', 'ه', 'ا', 'ا', 'ا'], $name);
    $name = preg_replace('/[~ًٌٍَُِّْ]/u', '', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    return $name;
}


$file_tmp_path = $_FILES['collection_excel_file']['tmp_name'];

try {
    // 6. جلب المندوبين المتاحين للمستخدم الحالي للتحقق منهم
    $reps_sql = "SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE";
    $reps_params = [];
    if ($_SESSION['user_role'] === 'supervisor') {
        $reps_sql .= " AND supervisor_id = ?";
        $reps_params[] = $_SESSION['user_id'];
    }
    $stmt_reps = $pdo->prepare($reps_sql);
    $stmt_reps->execute($reps_params);
    $reps_from_db = $stmt_reps->fetchAll();

    // إنشاء خريطة بالأسماء الموحدة
    $representatives_map_by_name = [];
    foreach ($reps_from_db as $rep) {
        $normalized_name = normalize_arabic_name($rep['full_name']);
        $representatives_map_by_name[$normalized_name] = $rep['user_id'];
    }

    // 7. قراءة ملف Excel
    $spreadsheet = IOFactory::load($file_tmp_path);
    $sheet = $spreadsheet->getActiveSheet();
    $raw_data = $sheet->toArray(null, true, true, true);
    
    array_shift($raw_data); // تجاهل الصف الأول (العناوين)

    if (empty($raw_data)) {
        $_SESSION['error_message'] = "الملف فارغ أو لا يحتوي على بيانات بعد إزالة العناوين.";
        header("Location: {$redirect_url_on_error}"); exit();
    }

    $import_errors = [];
    $valid_data_to_import = [];
    $display_row_num = 1;

    // 8. التحقق من صحة كل صف في الملف
    foreach ($raw_data as $key => $row) {
        $display_row_num++;
        if (empty(array_filter($row))) continue; // تخطي الصفوف الفارغة تمامًا
        
        $current_row_errors = [];
        $year = filter_var(trim($row['A'] ?? ''), FILTER_VALIDATE_INT);
        $month = filter_var(trim($row['B'] ?? ''), FILTER_VALIDATE_INT);
        $rep_name_from_excel = trim($row['C'] ?? '');
        $normalized_rep_name = normalize_arabic_name($rep_name_from_excel);
        $amount = filter_var(trim($row['D'] ?? ''), FILTER_VALIDATE_FLOAT);

        if (!$year || $year < 2020) $current_row_errors[] = "سنة غير صالحة.";
        if (!$month || $month < 1 || $month > 12) $current_row_errors[] = "شهر غير صالح.";
        if ($amount === false || $amount < 0) $current_row_errors[] = "مبلغ غير صالح.";
        
        $rep_id = $representatives_map_by_name[$normalized_rep_name] ?? null;
        if (empty($rep_name_from_excel)) $current_row_errors[] = "اسم المندوب فارغ.";
        elseif (!$rep_id) $current_row_errors[] = "المندوب '{$rep_name_from_excel}' غير موجود أو لا تملك صلاحية عليه.";

        if ($rep_id && $year && $month) {
            $stmt_check = $pdo->prepare("SELECT collection_id FROM monthly_collections WHERE representative_id = ? AND year = ? AND month = ?");
            $stmt_check->execute([$rep_id, $year, $month]);
            if ($stmt_check->fetch()) {
                $current_row_errors[] = "سجل موجود بالفعل لهذا المندوب في نفس الشهر.";
            }
        }

        if (!empty($current_row_errors)) {
            $import_errors[$key] = $current_row_errors;
        } else {
            // إضافة البيانات الصالحة إلى مصفوفة منفصلة
            // مع إضافة rep_id لتسهيل عملية الحفظ لاحقًا
            $row['rep_id'] = $rep_id;
            $valid_data_to_import[$key] = $row;
        }
    }

    // 9. حفظ البيانات في الجلسة
    $_SESSION['import_preview_data'] = ['data' => $raw_data, 'errors' => $import_errors];
    $_SESSION['valid_import_data'] = $valid_data_to_import; // فقط البيانات الصالحة للحفظ

    // 10. إعادة التوجيه إلى واجهة المعاينة
    header("Location: {$redirect_url_on_success}");
    exit();

} catch (Exception $e) {
    $_SESSION['error_message'] = "خطأ في قراءة ملف Excel: " . $e->getMessage();
    header("Location: {$redirect_url_on_error}");
    exit();
}