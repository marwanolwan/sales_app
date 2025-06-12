<?php
// actions/collection_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=collections");
    exit();
}

// 1. التحقق من الصلاحيات والـ CSRF
require_permission('manage_collections');
verify_csrf_token();

// 2. استلام البيانات من النموذج
$action = $_POST['action'] ?? 'add';
$collection_id = ($action == 'edit') ? (int)($_POST['collection_id'] ?? null) : null;
$representative_id = (int)($_POST['representative_id'] ?? 0);
$year = (int)($_POST['year'] ?? 0);
$month = (int)($_POST['month'] ?? 0);
$collection_amount = filter_var($_POST['collection_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$notes = trim($_POST['notes'] ?? '');

$redirect_url = "../index.php?page=collections&year={$year}&month={$month}";

// 3. التحقق من صحة البيانات
if (empty($representative_id) || empty($year) || empty($month) || $collection_amount === false) {
    $_SESSION['error_message'] = "الرجاء ملء جميع الحقول الإلزامية بشكل صحيح.";
    header("Location: {$redirect_url}&action={$action}" . ($collection_id ? "&id={$collection_id}" : ''));
    exit();
}

// التحقق من صلاحيات المشرف (أنه يضيف/يعدل لمندوب يتبعه)
if ($_SESSION['user_role'] == 'supervisor') {
    $stmt_check_rep = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND supervisor_id = ?");
    $stmt_check_rep->execute([$representative_id, $_SESSION['user_id']]);
    if ($stmt_check_rep->fetch() === false) {
        $_SESSION['error_message'] = "ليس لديك صلاحية لإدارة تحصيلات هذا المندوب.";
        header("Location: ../index.php?page=collections");
        exit();
    }
}

try {
    // التحقق من عدم وجود سجل لنفس المندوب في نفس الشهر والسنة
    $sql_check = "SELECT collection_id FROM monthly_collections WHERE representative_id = ? AND year = ? AND month = ?";
    $params_check = [$representative_id, $year, $month];
    if ($action == 'edit' && $collection_id) {
        $sql_check .= " AND collection_id != ?";
        $params_check[] = $collection_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "يوجد سجل تحصيل بالفعل لهذا المندوب في هذا الشهر. يمكنك تعديل السجل الحالي.";
        header("Location: {$redirect_url}");
        exit();
    }

    // 4. تنفيذ الاستعلام في قاعدة البيانات
    if ($action == 'add') {
        $sql = "INSERT INTO monthly_collections (representative_id, year, month, collection_amount, notes, recorded_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$representative_id, $year, $month, $collection_amount, $notes, $_SESSION['user_id']];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم إضافة سجل التحصيل بنجاح.";

    } elseif ($action == 'edit' && $collection_id) {
        $sql = "UPDATE monthly_collections SET collection_amount = ?, notes = ?
                WHERE collection_id = ? AND representative_id = ?";
        // في التعديل، عادة ما نعدل فقط المبلغ والملاحظات
        $params = [$collection_amount, $notes, $collection_id, $representative_id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم تحديث سجل التحصيل بنجاح.";
    }

} catch (PDOException $e) {
    // تسجيل الخطأ الفعلي
    error_log("Collection save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ سجل التحصيل.";
    // إعادة التوجيه إلى صفحة النموذج في حالة الخطأ للحفاظ على البيانات المدخلة
    $redirect_url .= "&action={$action}" . ($collection_id ? "&id={$collection_id}" : '');
}

// 5. إعادة التوجيه إلى صفحة القائمة
header("Location: {$redirect_url}");
exit();