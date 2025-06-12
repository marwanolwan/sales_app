<?php
// actions/market_share_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=market_share"); 
    exit();
}

require_permission('manage_market_share');
verify_csrf_token();

$report_period = $_POST['report_period'];
$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
$entries = $_POST['entry'] ?? [];
$user_id = $_SESSION['user_id'];

if (empty($report_period) || empty($customer_id) || empty($entries)) {
    $_SESSION['error_message'] = "بيانات غير مكتملة. يجب اختيار عميل وفترة وإدخال منتج واحد على الأقل.";
    header("Location: ../index.php?page=market_share&action=data_entry&customer_id={$customer_id}&report_period={$report_period}");
    exit();
}

try {
    $pdo->beginTransaction();

    // ======================| بداية التصحيح |======================
    // حذف الإدخالات القديمة لهذه الفترة وهذا العميل فقط
    $sql_delete = "DELETE FROM market_share_entries WHERE report_period = ? AND customer_id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$report_period, $customer_id]);
    // ======================| نهاية التصحيح |======================

    // إدخال البيانات الجديدة
    $sql_insert = "INSERT INTO market_share_entries 
                   (report_period, customer_id, product_name, is_our_product, product_id_internal, quantity_sold, user_id)
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);

    foreach ($entries as $entry) {
        // تجاهل الإدخالات التي لا تحتوي على كمية أو اسم منتج
        if (!isset($entry['quantity']) || trim($entry['quantity']) === '' || !isset($entry['product_name']) || trim($entry['product_name']) === '') {
            continue;
        }

        $is_our_product = (int)($entry['is_our_product'] ?? 0);
        
        $stmt_insert->execute([
            $report_period,
            $customer_id,
            $entry['product_name'],
            $is_our_product,
            ($is_our_product == 1 && !empty($entry['product_id_internal'])) ? $entry['product_id_internal'] : null,
            $entry['quantity'],
            $user_id
        ]);
    }

    $pdo->commit();
    $_SESSION['success_message'] = "تم حفظ بيانات الحصة السوقية للعميل في الفترة المحددة بنجاح.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Market share save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ فني أثناء الحفظ: " . $e->getMessage();
    // إعادة التوجيه إلى نفس صفحة الإدخال مع البيانات للحفاظ على السياق
    header("Location: ../index.php?page=market_share&action=data_entry&customer_id={$customer_id}&report_period={$report_period}");
    exit();
}

// إعادة التوجيه إلى التقرير مع فلترة لنفس الفترة والعميل الذي تم حفظه
header("Location: ../index.php?page=market_share&period=" . urlencode($report_period) . "&customer_id=" . $customer_id);
exit();