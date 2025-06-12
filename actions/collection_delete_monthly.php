<?php
// actions/collection_delete_monthly.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=collections");
    exit();
}

require_permission('manage_collections');
verify_csrf_token();

// استلام البيانات
$year = (int)($_POST['year'] ?? 0);
$month = (int)($_POST['month'] ?? 0);
$representative_id = $_POST['representative_id'] ?? 'all';

$redirect_url = "../index.php?page=collections&year={$year}&month={$month}";

if (empty($year) || empty($month)) {
    $_SESSION['error_message'] = "الرجاء تحديد سنة وشهر صالحين للحذف.";
    header("Location: {$redirect_url}");
    exit();
}

try {
    $sql = "DELETE mc FROM monthly_collections mc
            JOIN users u ON mc.representative_id = u.user_id
            WHERE mc.year = ? AND mc.month = ?";
    
    $params = [$year, $month];
    
    // تطبيق فلتر المندوب إذا تم اختياره
    if ($representative_id !== 'all' && is_numeric($representative_id)) {
        $sql .= " AND mc.representative_id = ?";
        $params[] = $representative_id;
    }
    
    // تطبيق فلتر الصلاحيات (مهم جدًا)
    // المشرف يمكنه فقط حذف تحصيلات فريقه
    if ($_SESSION['user_role'] === 'supervisor') {
        $sql .= " AND u.supervisor_id = ?";
        $params[] = $_SESSION['user_id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $deleted_rows = $stmt->rowCount();

    $_SESSION['success_message'] = "تم حذف {$deleted_rows} سجل تحصيل بنجاح للفترة المحددة.";

} catch (PDOException $e) {
    error_log("Monthly collection delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء عملية الحذف.";
}

header("Location: {$redirect_url}");
exit();