<?php
// actions/collection_import_confirm.php
require_once '../core/db.php';
require_once '../core/functions.php';

// تأكد من وجود هذا المسار أو قم بتثبيت المكتبة عبر Composer
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    die("مكتبة PhpSpreadsheet غير موجودة. يرجى تثبيتها.");
}

use PhpOffice\PhpSpreadsheet\IOFactory;

require_permission('manage_collections');
verify_csrf_token();

$redirect_url = "../index.php?page=collections";
$import_file = $_SESSION['import_file_path'] ?? null;

if (!$import_file || !file_exists($import_file)) {
    $_SESSION['error_message'] = "لم يتم العثور على ملف للاستيراد أو انتهت صلاحية الجلسة.";
    header("Location: {$redirect_url}");
    exit();
}

try {
    // جلب خريطة المندوبين مرة أخرى لضمان صحة البيانات
    $reps_map = $pdo->query("SELECT full_name, user_id FROM users WHERE role = 'representative'")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $spreadsheet = IOFactory::load($import_file);
    $sheet = $spreadsheet->getActiveSheet();
    $raw_data = $sheet->toArray(null, true, true, true);
    array_shift($raw_data); // تجاهل الصف الأول (العناوين)

    $pdo->beginTransaction();
    $sql = "INSERT INTO monthly_collections (year, month, representative_id, collection_amount, notes, recorded_by_user_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    $imported_count = 0;
    $skipped_rows_details = [];

    foreach ($raw_data as $key => $row) {
        $row_num_for_user = $key; // key يبدأ من 2 عادةً بعد إزالة العناوين
        if (empty(array_filter($row))) continue;

        $year = filter_var(trim($row['A'] ?? ''), FILTER_VALIDATE_INT);
        $month = filter_var(trim($row['B'] ?? ''), FILTER_VALIDATE_INT);
        $rep_name = trim($row['C'] ?? '');
        $amount = filter_var(trim($row['D'] ?? ''), FILTER_VALIDATE_FLOAT);
        $notes = trim($row['E'] ?? '');
        $rep_id = $reps_map[$rep_name] ?? null;

        // إعادة التحقق من كل الشروط قبل الإدخال النهائي
        if ($year && $month && $rep_id && $amount !== false) {
             $stmt_check = $pdo->prepare("SELECT collection_id FROM monthly_collections WHERE representative_id = ? AND year = ? AND month = ?");
             $stmt_check->execute([$rep_id, $year, $month]);
             if ($stmt_check->fetch()) {
                 $skipped_rows_details[] = "الصف {$row_num_for_user}: سجل مكرر للمندوب '{$rep_name}'.";
                 continue; // تخطي السجلات المكررة
             }

            $stmt->execute([$year, $month, $rep_id, $amount, $notes, $_SESSION['user_id']]);
            $imported_count++;
        } else {
            $skipped_rows_details[] = "الصف {$row_num_for_user}: بيانات غير صالحة أو مندوب غير موجود.";
        }
    }
    
    $pdo->commit();

    $_SESSION['success_message'] = "تم استيراد {$imported_count} سجل بنجاح.";
    if (!empty($skipped_rows_details)) {
        $_SESSION['warning_message'] = "تم تجاهل بعض السجلات بسبب أخطاء:<br>- " . implode("<br>- ", $skipped_rows_details);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['error_message'] = "فشل الاستيراد بسبب خطأ: " . $e->getMessage();
} finally {
    // حذف الملف المؤقت بعد الانتهاء
    if (file_exists($import_file)) {
        unlink($import_file);
    }
    unset($_SESSION['import_file_path']);
}

header("Location: {$redirect_url}");
exit();