<?php
// controllers/annual_campaigns.php

require_permission('manage_promotions');

$action = $_GET['action'] ?? 'list';
$customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : null;
$campaign_id = isset($_REQUEST['campaign_id']) ? (int)$_REQUEST['campaign_id'] : null;

// التحقق من وجود customer_id هو أمر أساسي لكل الإجراءات
if (!$customer_id) {
    $_SESSION['error_message'] = "يجب تحديد عميل.";
    header("Location: index.php?page=promotions");
    exit();
}

// جلب بيانات العميل مرة واحدة في الأعلى
$stmt_cust = $pdo->prepare("SELECT name, customer_code FROM customers WHERE customer_id = ?");
$stmt_cust->execute([$customer_id]);
$customer = $stmt_cust->fetch();
if (!$customer) {
     $_SESSION['error_message'] = "العميل غير موجود.";
     header("Location: index.php?page=promotions");
     exit();
}

$page_title = "الدعاية السنوية للزبون: " . htmlspecialchars($customer['name']);
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title .= ($action == 'add') ? ' - إضافة حملة جديدة' : ' - تعديل حملة';
        $campaign_data = null;
        if ($action == 'edit' && $campaign_id) {
            $stmt = $pdo->prepare("SELECT * FROM annual_campaigns WHERE annual_campaign_id = ? AND customer_id = ?");
            $stmt->execute([$campaign_id, $customer_id]);
            $campaign_data = $stmt->fetch();
            if (!$campaign_data) {
                 $_SESSION['error_message'] = "الحملة غير موجودة.";
                 header("Location: index.php?page=annual_campaigns&customer_id={$customer_id}");
                 exit();
            }
        }
        $annual_promo_types = $pdo->query("SELECT promo_type_id, name FROM promotion_types WHERE is_annual = 1 ORDER BY name ASC")->fetchAll();
        $view_file = 'views/promotions/annual_campaign_form.php';
        break;
        
    case 'photos':
        // =====| بداية التعديل |=====
        
        if (!$campaign_id) { 
            header("Location: index.php?page=annual_campaigns&customer_id={$customer_id}"); 
            exit(); 
        }

        // جلب بيانات الحملة الأساسية (هذا هو السطر الذي كان ناقصًا)
        $stmt_campaign = $pdo->prepare("SELECT ac.*, pt.name as promo_name FROM annual_campaigns ac JOIN promotion_types pt ON ac.promo_type_id = pt.promo_type_id WHERE ac.annual_campaign_id = ? AND ac.customer_id = ?");
        $stmt_campaign->execute([$campaign_id, $customer_id]);
        $campaign_data = $stmt_campaign->fetch();
        
        if (!$campaign_data) { 
            $_SESSION['error_message'] = "الحملة المطلوبة غير موجودة لهذا العميل.";
            header("Location: index.php?page=annual_campaigns&customer_id={$customer_id}"); 
            exit(); 
        }

        $page_title = 'إدارة صور الحملة: ' . htmlspecialchars($campaign_data['promo_name']);
        
        // جلب الصور للشهر والسنة المحددين
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

        $stmt_photos = $pdo->prepare("SELECT * FROM annual_campaign_photos WHERE annual_campaign_id = ? AND year = ? AND month = ? ORDER BY uploaded_at DESC");
        $stmt_photos->execute([$campaign_id, $year, $month]);
        $photos = $stmt_photos->fetchAll();
        
        // جلب قائمة الشهور التي بها صور
        $stmt_months_with_photos = $pdo->prepare("SELECT DISTINCT year, month, COUNT(photo_id) as photo_count FROM annual_campaign_photos WHERE annual_campaign_id = ? GROUP BY year, month ORDER BY year, month");
        $stmt_months_with_photos->execute([$campaign_id]);
        $months_with_photos = $stmt_months_with_photos->fetchAll();

        $view_file = 'views/promotions/annual_photos_manage.php';
        
        // =====| نهاية التعديل |=====
        break;

    case 'list':
    default:
        $stmt = $pdo->prepare("SELECT ac.*, pt.name as promo_type_name FROM annual_campaigns ac JOIN promotion_types pt ON ac.promo_type_id = pt.promo_type_id WHERE ac.customer_id = ? ORDER BY ac.start_date DESC");
        $stmt->execute([$customer_id]);
        $campaigns = $stmt->fetchAll();
        $view_file = 'views/promotions/annual_campaign_list.php';
        break;
}

include 'views/layout.php';