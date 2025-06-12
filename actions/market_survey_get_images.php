<?php
// actions/market_survey_get_images.php

header('Content-Type: application/json');
require_once '../core/db.php';
require_once '../core/functions.php';

$response = ['success' => false, 'images' => [], 'message' => 'طلب غير صالح.'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode($response);
    exit();
}

try {
    // يمكن استخدام صلاحية العرض هنا
    require_permission('view_market_surveys'); 
    
    $survey_id = isset($_GET['survey_id']) ? (int)$_GET['survey_id'] : 0;

    if ($survey_id > 0) {
        $sql = "SELECT i.image_path, c.competitor_product_name 
                FROM market_survey_images i
                JOIN market_survey_competitors c ON i.competitor_entry_id = c.competitor_entry_id
                WHERE c.survey_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$survey_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'images' => $images
        ];
    } else {
        $response['message'] = 'معرف دراسة غير صالح.';
    }

} catch (Exception $e) {
    error_log("Get survey images failed: " . $e->getMessage());
    $response['message'] = 'حدث خطأ في الخادم.';
}

echo json_encode($response);