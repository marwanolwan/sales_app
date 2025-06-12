<?php
// actions/market_survey_image_delete.php
header('Content-Type: application/json');
require_once '../core/db.php';
require_once '../core/functions.php';

$response = ['success' => false, 'message' => 'طلب غير صالح.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit();
}

try {
    require_permission('manage_market_surveys'); // تأكد من صلاحية المستخدم
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;

    if ($image_id > 0) {
        define('SURVEY_IMAGE_DIR', '../uploads/market_surveys/');
        
        // جلب مسار الصورة لحذفها من السيرفر
        $stmt_get = $pdo->prepare("SELECT image_path FROM market_survey_images WHERE image_id = ?");
        $stmt_get->execute([$image_id]);
        $image_path = $stmt_get->fetchColumn();

        if ($image_path) {
            // حذف السجل من قاعدة البيانات
            $stmt_delete = $pdo->prepare("DELETE FROM market_survey_images WHERE image_id = ?");
            $stmt_delete->execute([$image_id]);

            // حذف الملف من السيرفر
            if (file_exists(SURVEY_IMAGE_DIR . $image_path)) {
                unlink(SURVEY_IMAGE_DIR . $image_path);
            }
            
            $response = ['success' => true, 'message' => 'تم حذف الصورة بنجاح.'];
        } else {
            $response['message'] = 'لم يتم العثور على الصورة.';
        }
    } else {
        $response['message'] = 'معرف صورة غير صالح.';
    }

} catch (Exception $e) {
    error_log("Image delete failed: " . $e->getMessage());
    $response['message'] = 'حدث خطأ في الخادم.';
}

echo json_encode($response);