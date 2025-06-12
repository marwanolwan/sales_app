<?php
// actions/annual_photo_delete.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

require_permission('manage_promotions');
verify_csrf_token();

$customer_id = (int)($_POST['customer_id'] ?? 0);
$campaign_id = (int)($_POST['campaign_id'] ?? 0);
$photo_id = (int)($_POST['photo_id'] ?? 0);
$year = (int)($_POST['year'] ?? 0);
$month = (int)($_POST['month'] ?? 0);

$redirect_url = "../index.php?page=annual_campaigns&action=photos&customer_id={$customer_id}&campaign_id={$campaign_id}&year={$year}&month={$month}";

if (!$photo_id) {
    $_SESSION['error_message'] = "معرف الصورة غير صالح.";
    header("Location: {$redirect_url}");
    exit();
}

try {
    // جلب مسار الصورة قبل الحذف من قاعدة البيانات
    $stmt_path = $pdo->prepare("SELECT image_path FROM annual_campaign_photos WHERE photo_id = ?");
    $stmt_path->execute([$photo_id]);
    $image_path = $stmt_path->fetchColumn();

    if ($image_path) {
        // حذف السجل من قاعدة البيانات
        $stmt_delete = $pdo->prepare("DELETE FROM annual_campaign_photos WHERE photo_id = ?");
        $stmt_delete->execute([$photo_id]);

        // حذف الملف الفعلي من الخادم
        $full_path = '../uploads/annual_campaigns_photos/' . $image_path;
        if (file_exists($full_path) && is_file($full_path)) {
            unlink($full_path);
        }
        
        $_SESSION['success_message'] = "تم حذف الصورة بنجاح.";
    } else {
        $_SESSION['error_message'] = "الصورة المطلوبة للحذف غير موجودة.";
    }

} catch (PDOException $e) {
    error_log("Annual photo delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حذف الصورة.";
}

header("Location: {$redirect_url}");
exit();