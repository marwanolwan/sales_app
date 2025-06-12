<?php
// actions/market_share_delete_by_customer.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=market_share"); 
    exit();
}

require_permission('manage_market_share'); // يجب أن يمتلك صلاحية الإدارة
verify_csrf_token();

$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
$report_period = $_POST['report_period'] ?? null;

if (empty($customer_id) || empty($report_period)) {
    $_SESSION['error_message'] = "بيانات الحذف غير مكتملة.";
    header("Location: ../index.php?page=market_share");
    exit();
}

try {
    $sql = "DELETE FROM market_share_entries WHERE customer_id = ? AND report_period = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id, $report_period]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "تم حذف بيانات الحصة السوقية لهذا العميل في الفترة المحددة بنجاح.";
    } else {
        $_SESSION['warning_message'] = "لم يتم العثور على بيانات لحذفها.";
    }

} catch (PDOException $e) {
    error_log("Market share delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء عملية الحذف.";
}

// إعادة التوجيه إلى نفس صفحة التقرير مع الاحتفاظ بفترة الفلتر
header("Location: ../index.php?page=market_share&period=" . urlencode($report_period));
exit();