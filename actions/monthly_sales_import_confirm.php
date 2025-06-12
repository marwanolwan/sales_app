<?php
/**
 * هذا الملف يعالج طلب تأكيد استيراد المبيعات الشهرية.
 * يتم تشغيله بعد أن يراجع المستخدم البيانات في صفحة المعاينة ويضغط على زر "تأكيد".
 * يقوم بقراءة البيانات الصالحة من الجلسة وإدخالها في قاعدة البيانات.
 */

// 1. تضمين الملفات الأساسية
// require_once هو الأفضل هنا لضمان عدم استمرار التنفيذ إذا كانت الملفات مفقودة.
require_once '../core/db.php';
require_once '../core/functions.php';

// 2. التحقق من أن الطلب هو POST
// هذا يمنع الوصول المباشر إلى الملف عبر URL.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // إذا لم يكن الطلب POST، أعد التوجيه إلى الصفحة الرئيسية لوحدة المبيعات.
    header("Location: ../index.php?page=monthly_sales&action=import");
    exit();
}

// 3. التحقق من الصلاحيات وتوكن CSRF
// التأكد من أن المستخدم لديه الصلاحية اللازمة وأن الطلب آمن.
require_permission('manage_monthly_sales');
verify_csrf_token();

// 4. استرداد البيانات من الجلسة
// يتم جلب مسار الملف المؤقت والبيانات التي تم تحليلها من الجلسة.
$file_path = $_SESSION['import_file_path'] ?? null;
$preview_data = $_SESSION['import_preview_data']['data'] ?? [];
$import_errors = $_SESSION['import_preview_data']['errors'] ?? [];

// 5. تنظيف الجلسة والملف المؤقت
// هذه خطوة مهمة تتم في البداية لضمان عدم بقاء بيانات حساسة أو ملفات مؤقتة
// حتى لو حدث خطأ أثناء التنفيذ.
$redirect_page = '../index.php?page=monthly_sales';
if ($file_path && file_exists($file_path)) {
    unlink($file_path);
}
unset($_SESSION['import_file_path'], $_SESSION['import_preview_data']);

// 6. التحقق من وجود بيانات صالحة للاستيراد
if (empty($preview_data)) {
    $_SESSION['error_message'] = "انتهت صلاحية جلسة الاستيراد أو لا توجد بيانات. يرجى المحاولة مرة أخرى.";
    header("Location: {$redirect_page}");
    exit();
}

// 7. إعادة جلب خريطة المندوبين
// يتم جلب قائمة المندوبين المتاحين للمستخدم الحالي مرة أخرى.
// هذا إجراء أمني إضافي للتأكد من أن المستخدم لم يغير صلاحياته
// أو أن بيانات المندوبين لم تتغير بين مرحلة المعاينة والتأكيد.
$sql_reps_confirm = "SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE";
$params_reps_confirm = [];
if ($_SESSION['user_role'] == 'supervisor') {
    $sql_reps_confirm .= " AND supervisor_id = ?";
    $params_reps_confirm[] = $_SESSION['user_id'];
}
$stmt_reps_confirm = $pdo->prepare($sql_reps_confirm);
$stmt_reps_confirm->execute($params_reps_confirm);
// fetchAll(PDO::FETCH_KEY_PAIR) ينشئ مصفوفة حيث يكون المفتاح هو العمود الأول (user_id) والقيمة هي العمود الثاني (full_name)
// ولكننا نحتاج العكس، لذا سنستخدم array_flip
$reps_map_by_name = $stmt_reps_confirm->fetchAll(PDO::FETCH_KEY_PAIR);
$reps_map_by_name = array_flip($reps_map_by_name); // الآن المفتاح هو الاسم والقيمة هي ID

$imported_count = 0;
$failed_count = 0;

// 8. بدء عملية الإدخال في قاعدة البيانات
try {
    // استخدام Transaction لضمان إدخال جميع السجلات الصالحة معًا أو عدم إدخال أي شيء في حالة حدوث خطأ.
    $pdo->beginTransaction();

    // تجهيز استعلام الإدخال مرة واحدة خارج الحلقة لتحسين الأداء.
    $sql_insert = "INSERT INTO monthly_sales (representative_id, year, month, net_sales_amount, notes, recorded_by_user_id) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);

    // 9. المرور على البيانات التي تم تحليلها
    foreach ($preview_data as $row_num => $row) {
        // إذا كان الصف يحتوي على خطأ تم اكتشافه في مرحلة المعاينة، يتم تجاهله.
        if (isset($import_errors[$row_num])) {
            $failed_count++;
            continue;
        }
        
        $data = $row['data'];
        $rep_id = $reps_map_by_name[$data['rep_name']] ?? false;
        
        // إعادة التحقق السريع قبل الإدخال كطبقة حماية إضافية.
        if ($rep_id === false || !is_numeric($data['year']) || !is_numeric($data['month']) || !is_numeric($data['sales_amount'])) {
            $failed_count++;
            continue;
        }

        // تنفيذ الاستعلام المُجهز مع البيانات الحالية للصف.
        $stmt_insert->execute([
            $rep_id,
            (int)$data['year'],
            (int)$data['month'],
            (float)$data['sales_amount'],
            $data['notes'],
            $_SESSION['user_id']
        ]);
        $imported_count++;
    }

    // 10. تأكيد العملية (Commit)
    // إذا لم تحدث أي أخطاء، يتم حفظ جميع التغييرات في قاعدة البيانات.
    $pdo->commit();
    
    // 11. إعداد رسائل النجاح والتحذير
    if ($imported_count > 0) {
        $_SESSION['success_message'] = "تم استيراد {$imported_count} سجل مبيعات بنجاح.";
    }
    if ($failed_count > 0) {
        // استخدام warning_message للرسائل التي لا تعتبر خطأً فادحًا.
        $_SESSION['warning_message'] = "تم تجاهل {$failed_count} سجل بسبب وجود أخطاء في البيانات.";
    }
    if ($imported_count == 0 && $failed_count > 0) {
        $_SESSION['error_message'] = "لم يتم استيراد أي سجلات بسبب وجود أخطاء في جميع الصفوف.";
    }

} catch (PDOException $e) {
    // 12. التراجع عن العملية في حالة حدوث خطأ
    // إذا حدث أي خطأ أثناء تنفيذ الاستعلامات، يتم التراجع عن جميع التغييرات.
    $pdo->rollBack();
    
    // تسجيل الخطأ الفعلي في سجلات الخادم (مهم جدًا لتصحيح الأخطاء).
    error_log("Sales import confirmation failed: " . $e->getMessage());
    
    // إعطاء رسالة خطأ عامة للمستخدم.
    $_SESSION['error_message'] = "حدث خطأ فني في قاعدة البيانات أثناء عملية الاستيراد. لم يتم حفظ أي بيانات.";
}

// 13. إعادة التوجيه
// في النهاية، يتم إعادة توجيه المستخدم إلى صفحة المبيعات الرئيسية لعرض النتائج.
header("Location: {$redirect_page}");
exit();