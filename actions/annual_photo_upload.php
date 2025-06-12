<?php
// actions/annual_photo_upload.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

require_permission('manage_promotions');
verify_csrf_token();

// =====| بداية التعديل: استقبال الشهر والسنة |=====
$customer_id = (int)($_POST['customer_id'] ?? 0);
$campaign_id = (int)($_POST['campaign_id'] ?? 0);
$year = (int)($_POST['year'] ?? 0);
$month = (int)($_POST['month'] ?? 0);
// =====| نهاية التعديل |=====

$redirect_url = "../index.php?page=annual_campaigns&action=photos&customer_id={$customer_id}&campaign_id={$campaign_id}&year={$year}&month={$month}";

if (!$customer_id || !$campaign_id || !$year || !$month) {
    $_SESSION['error_message'] = "بيانات الحملة أو الشهر غير صالحة.";
    header("Location: ../index.php?page=promotions");
    exit();
}

if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $upload_dir = '../uploads/annual_campaigns_photos/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0775, true)) {
            $_SESSION['error_message'] = "فشل في إنشاء مجلد الرفع. تحقق من الصلاحيات.";
            header("Location: {$redirect_url}");
            exit();
        }
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // =====| بداية التعديل: استخدام الشهر والسنة في الاستعلام |=====
    $sql = "INSERT INTO annual_campaign_photos (annual_campaign_id, year, month, image_path, uploaded_by_user_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    // =====| نهاية التعديل |=====
    
    $uploaded_count = 0;
    $errors = [];

    foreach ($_FILES['images']['name'] as $key => $name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['images']['tmp_name'][$key];
            $file_type = mime_content_type($tmp_name);
            $file_size = $_FILES['images']['size'][$key];

            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "ملف '{$name}' له نوع غير مسموح به.";
                continue;
            }
            if ($file_size > $max_size) {
                $errors[] = "ملف '{$name}' يتجاوز الحجم المسموح به (5MB).";
                continue;
            }

            $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new_filename = "annual_{$campaign_id}_{$year}_{$month}_" . uniqid() . '.' . $file_ext;
            
            if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                try {
                    // =====| بداية التعديل: تمرير الشهر والسنة إلى execute |=====
                    $stmt->execute([$campaign_id, $year, $month, $new_filename, $_SESSION['user_id']]);
                    // =====| نهاية التعديل |=====
                    $uploaded_count++;
                } catch (PDOException $e) {
                    unlink($upload_dir . $new_filename);
                    error_log("Photo DB insert failed: " . $e->getMessage());
                    $errors[] = "فشل حفظ صورة '{$name}' في قاعدة البيانات.";
                }
            } else {
                 $errors[] = "فشل رفع ملف '{$name}'.";
            }
        }
    }

    if ($uploaded_count > 0) {
        $_SESSION['success_message'] = "تم رفع {$uploaded_count} صورة بنجاح.";
    }
    if (!empty($errors)) {
        $existing_error = $_SESSION['error_message'] ?? '';
        $_SESSION['error_message'] = ($existing_error ? $existing_error . '<br>' : '') . implode('<br>', $errors);
    }

} else {
    $_SESSION['error_message'] = "لم يتم اختيار أي ملفات للرفع.";
}

header("Location: {$redirect_url}");
exit();