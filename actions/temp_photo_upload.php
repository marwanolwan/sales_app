<?php
// actions/temp_photo_upload.php
require_once '../core/db.php';
require_once '../core/functions.php';

require_permission('manage_promotions');
verify_csrf_token();

$customer_id = (int)($_POST['customer_id'] ?? 0);
$campaign_id = (int)($_POST['campaign_id'] ?? 0);
$redirect_url = "../index.php?page=temp_campaigns&action=photos&customer_id={$customer_id}&campaign_id={$campaign_id}";

if (!$customer_id || !$campaign_id) {
    $_SESSION['error_message'] = "بيانات الحملة غير صالحة.";
    header("Location: ../index.php?page=promotions");
    exit();
}

if (isset($_FILES['images'])) {
    $upload_dir = '../uploads/temp_campaigns_photos/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $sql = "INSERT INTO temp_campaign_photos (temp_campaign_id, image_path, uploaded_by_user_id) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $uploaded_count = 0;

    foreach ($_FILES['images']['name'] as $key => $name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['images']['tmp_name'][$key];
            $file_type = $_FILES['images']['type'][$key];
            $file_size = $_FILES['images']['size'][$key];

            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_filename = "temp_{$campaign_id}_" . uniqid() . time() . '.' . $file_ext;
                
                if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                    try {
                        $stmt->execute([$campaign_id, $new_filename, $_SESSION['user_id']]);
                        $uploaded_count++;
                    } catch (PDOException $e) {
                        // حذف الملف إذا فشل الإدخال في قاعدة البيانات
                        unlink($upload_dir . $new_filename);
                        error_log("Photo DB insert failed: " . $e->getMessage());
                    }
                }
            }
        }
    }
    $_SESSION['success_message'] = "تم رفع {$uploaded_count} صورة بنجاح.";
} else {
    $_SESSION['error_message'] = "لم يتم اختيار أي ملفات للرفع.";
}

header("Location: {$redirect_url}");
exit();