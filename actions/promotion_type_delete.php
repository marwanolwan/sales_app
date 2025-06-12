<?php
// actions/promotion_type_delete.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=promotion_types");
    exit();
}

require_permission('manage_promotions');
verify_csrf_token();

$promo_type_id = isset($_POST['promo_type_id']) ? (int)$_POST['promo_type_id'] : null;

if (!$promo_type_id) {
    $_SESSION['error_message'] = "معرف نوع الدعاية غير صالح.";
    header("Location: ../index.php?page=promotion_types");
    exit();
}

try {
    // التحقق من جهة الخادم لمنع حذف الأنواع المستخدمة
    $stmt_check1 = $pdo->prepare("SELECT COUNT(*) FROM annual_campaigns WHERE promo_type_id = ?");
    $stmt_check1->execute([$promo_type_id]);
    $count1 = $stmt_check1->fetchColumn();

    $stmt_check2 = $pdo->prepare("SELECT COUNT(*) FROM temp_campaigns WHERE promo_type_id = ?");
    $stmt_check2->execute([$promo_type_id]);
    $count2 = $stmt_check2->fetchColumn();

    if (($count1 + $count2) > 0) {
        $_SESSION['error_message'] = "لا يمكن حذف هذا النوع لأنه مستخدم في حملات حالية.";
    } else {
        $stmt_delete = $pdo->prepare("DELETE FROM promotion_types WHERE promo_type_id = ?");
        $stmt_delete->execute([$promo_type_id]);
        $_SESSION['success_message'] = "تم حذف نوع الدعاية بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Promotion type delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف نوع الدعاية.";
}

header("Location: ../index.php?page=promotion_types");
exit();