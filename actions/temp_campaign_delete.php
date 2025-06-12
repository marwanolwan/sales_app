<?php
// actions/temp_campaign_delete.php
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
$redirect_url = "../index.php?page=temp_campaigns&customer_id={$customer_id}";

if (!$customer_id || !$campaign_id) {
    $_SESSION['error_message'] = "بيانات الحملة غير صالحة للحذف.";
    header("Location: ../index.php?page=promotions");
    exit();
}

try {
    // قبل الحذف، يجب حذف الصور المرتبطة من الخادم
    $stmt_photos = $pdo->prepare("SELECT image_path FROM temp_campaign_photos WHERE temp_campaign_id = ?");
    $stmt_photos->execute([$campaign_id]);
    $photos_to_delete = $stmt_photos->fetchAll(PDO::FETCH_COLUMN);

    foreach ($photos_to_delete as $photo_path) {
        $full_path = '../uploads/temp_campaigns_photos/' . $photo_path;
        if (file_exists($full_path) && is_file($full_path)) {
            unlink($full_path);
        }
    }

    // الآن احذف الحملة من قاعدة البيانات (سيتم حذف سجلات الصور تلقائيًا بسبب ON DELETE CASCADE)
    $stmt_delete = $pdo->prepare("DELETE FROM temp_campaigns WHERE temp_campaign_id = ? AND customer_id = ?");
    $stmt_delete->execute([$campaign_id, $customer_id]);
    
    $_SESSION['success_message'] = "تم حذف الحملة المؤقتة وجميع صورها بنجاح.";

} catch (PDOException $e) {
    error_log("Temp campaign delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حذف الحملة.";
}

header("Location: {$redirect_url}");
exit();