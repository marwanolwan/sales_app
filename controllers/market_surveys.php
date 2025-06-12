<?php
// controllers/market_surveys.php

// استخدام صلاحية موجودة مؤقتاً، يمكنك إنشاء صلاحية مخصصة لاحقاً
$action = $_GET['action'] ?? 'list';
if (in_array($action, ['add', 'edit'])) {
    require_permission('manage_market_surveys');
} else {
    require_permission('view_market_surveys');
}


$survey_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = "دراسات أسعار السوق";
$view_file = '';
define('SURVEY_IMAGE_DIR', 'uploads/market_surveys/');

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إنشاء دراسة سوق جديدة' : 'تعديل دراسة السوق';
        
        $our_products = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = TRUE ORDER BY name ASC")->fetchAll();
        $customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        
        $survey_data = null;
        $competitors_data = [];
        if ($action == 'edit' && $survey_id) {
            $stmt = $pdo->prepare("SELECT * FROM market_surveys WHERE survey_id = ?");
            $stmt->execute([$survey_id]);
            $survey_data = $stmt->fetch();
            if (!$survey_data) {
                $_SESSION['error_message'] = "الدراسة غير موجودة.";
                header("Location: index.php?page=market_surveys");
                exit();
            }
            
            // جلب المنافسين
            $stmt_comp = $pdo->prepare("SELECT * FROM market_survey_competitors WHERE survey_id = ? ORDER BY competitor_entry_id ASC");
            $stmt_comp->execute([$survey_id]);
            $competitors_data = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);

            // جلب الصور وربطها بكل منافس
            $competitor_ids = array_column($competitors_data, 'competitor_entry_id');
            if (!empty($competitor_ids)) {
                $placeholders = implode(',', array_fill(0, count($competitor_ids), '?'));
                $stmt_images = $pdo->prepare("SELECT * FROM market_survey_images WHERE competitor_entry_id IN ($placeholders)");
                $stmt_images->execute($competitor_ids);
                $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
                
                // تحويل مصفوفة الصور إلى مصفوفة مرتبطة بـ ID المنافس
                $images_by_competitor = [];
                foreach ($images as $image) {
                    $images_by_competitor[$image['competitor_entry_id']][] = $image;
                }
                
                // إضافة الصور إلى بيانات كل منافس
                foreach ($competitors_data as $key => $competitor) {
                    $competitors_data[$key]['images'] = $images_by_competitor[$competitor['competitor_entry_id']] ?? [];
                }
            }
        }
        $view_file = 'views/market_surveys/form.php';
        break;

    case 'view':
        if (!$survey_id) {
            header("Location: index.php?page=market_surveys"); exit();
        }
        
        $page_title = "تفاصيل دراسة السوق";
        
        $sql = "SELECT ms.*, p.name as product_name, p.product_code, u.full_name as user_name, c.name as customer_name
                FROM market_surveys ms
                JOIN products p ON ms.product_id = p.product_id
                JOIN users u ON ms.user_id = u.user_id
                LEFT JOIN customers c ON ms.customer_id = c.customer_id
                WHERE ms.survey_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$survey_id]);
        $survey_data = $stmt->fetch();

        if (!$survey_data) {
            $_SESSION['error_message'] = "الدراسة غير موجودة.";
            header("Location: index.php?page=market_surveys"); exit();
        }
        
        // نفس منطق جلب المنافسين والصور المتعددة
        $stmt_comp = $pdo->prepare("SELECT * FROM market_survey_competitors WHERE survey_id = ? ORDER BY competitor_entry_id ASC");
        $stmt_comp->execute([$survey_id]);
        $competitors_data = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);
        
        $competitor_ids = array_column($competitors_data, 'competitor_entry_id');
        if (!empty($competitor_ids)) {
            $placeholders = implode(',', array_fill(0, count($competitor_ids), '?'));
            $stmt_images = $pdo->prepare("SELECT * FROM market_survey_images WHERE competitor_entry_id IN ($placeholders)");
            $stmt_images->execute($competitor_ids);
            $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

            $images_by_competitor = [];
            foreach ($images as $image) {
                $images_by_competitor[$image['competitor_entry_id']][] = $image;
            }
            
            foreach ($competitors_data as $key => $competitor) {
                $competitors_data[$key]['images'] = $images_by_competitor[$competitor['competitor_entry_id']] ?? [];
            }
        }
        
        $view_file = 'views/market_surveys/view.php';
        break;
        
    case 'list':
    default:
        $sql = "SELECT ms.survey_id, ms.survey_date, p.name as product_name, u.full_name as user_name, c.name as customer_name,
                (SELECT COUNT(*) FROM market_survey_competitors WHERE survey_id = ms.survey_id) as competitor_count
                FROM market_surveys ms
                JOIN products p ON ms.product_id = p.product_id
                JOIN users u ON ms.user_id = u.user_id
                LEFT JOIN customers c ON ms.customer_id = c.customer_id
                ORDER BY ms.survey_date DESC, ms.survey_id DESC"; // ترتيب إضافي بالـ ID
        $surveys = $pdo->query($sql)->fetchAll();
        $view_file = 'views/market_surveys/list.php';
        break;
}

include 'views/layout.php';