<?php
// actions/market_survey_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=market_surveys");
    exit();
}

require_permission('manage_market_surveys'); // تأكد من الصلاحية
verify_csrf_token();

$survey_id = isset($_POST['survey_id']) ? (int)$_POST['survey_id'] : null;

if (!$survey_id) {
    $_SESSION['error_message'] = "معرف دراسة غير صالح.";
    header("Location: ../index.php?page=market_surveys");
    exit();
}

define('SURVEY_IMAGE_DIR', '../uploads/market_surveys/');

try {
    $pdo->beginTransaction();

    // 1. جلب جميع الصور المرتبطة بهذه الدراسة لحذفها من السيرفر
    $sql_get_images = "SELECT i.image_path FROM market_survey_images i
                       JOIN market_survey_competitors c ON i.competitor_entry_id = c.competitor_entry_id
                       WHERE c.survey_id = ?";
    $stmt_get_images = $pdo->prepare($sql_get_images);
    $stmt_get_images->execute([$survey_id]);
    $images_to_delete = $stmt_get_images->fetchAll(PDO::FETCH_COLUMN);

    // 2. حذف الدراسة من الجدول الرئيسي (سيؤدي هذا إلى حذف المنافسين والصور تلقائياً بسبب ON DELETE CASCADE)
    $stmt_delete_survey = $pdo->prepare("DELETE FROM market_surveys WHERE survey_id = ?");
    $stmt_delete_survey->execute([$survey_id]);
    
    // 3. حذف ملفات الصور من السيرفر
    foreach ($images_to_delete as $image_path) {
        if (!empty($image_path) && file_exists(SURVEY_IMAGE_DIR . $image_path)) {
            unlink(SURVEY_IMAGE_DIR . $image_path);
        }
    }

    $pdo->commit();
    $_SESSION['success_message'] = "تم حذف الدراسة وكل ما يتعلق بها بنجاح.";

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Survey delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف الدراسة.";
}

header("Location: ../index.php?page=market_surveys");
exit();