<?php
// controllers/temp_campaigns.php

require_permission('manage_promotions');

$action = $_GET['action'] ?? 'list';
$customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : null;
$campaign_id = isset($_REQUEST['campaign_id']) ? (int)$_REQUEST['campaign_id'] : null;

if (!$customer_id) {
    $_SESSION['error_message'] = "يجب تحديد عميل.";
    header("Location: index.php?page=promotions");
    exit();
}

$stmt_cust = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
$stmt_cust->execute([$customer_id]);
$customer = $stmt_cust->fetch();
if (!$customer) {
    $_SESSION['error_message'] = "العميل غير موجود.";
    header("Location: index.php?page=promotions");
    exit();
}

$page_title = "الدعاية المؤقتة للزبون: " . htmlspecialchars($customer['name']);
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title .= ($action == 'add') ? ' - إضافة حملة جديدة' : ' - تعديل حملة';
        $campaign_data = null;
        if ($action == 'edit' && $campaign_id) {
            $stmt = $pdo->prepare("SELECT * FROM temp_campaigns WHERE temp_campaign_id = ? AND customer_id = ?");
            $stmt->execute([$campaign_id, $customer_id]);
            $campaign_data = $stmt->fetch();
            if (!$campaign_data) {
                $_SESSION['error_message'] = "الحملة غير موجودة.";
                header("Location: index.php?page=temp_campaigns&customer_id={$customer_id}");
                exit();
            }
        }
        $temp_promo_types = $pdo->query("SELECT promo_type_id, name FROM promotion_types WHERE is_annual = 0 ORDER BY name ASC")->fetchAll();
        $view_file = 'views/promotions/temp_campaign_form.php';
        break;
        
    case 'photos':
        if (!$campaign_id) { header("Location: index.php?page=temp_campaigns&customer_id={$customer_id}"); exit(); }
        
        $stmt_campaign = $pdo->prepare("SELECT description FROM temp_campaigns WHERE temp_campaign_id = ?");
        $stmt_campaign->execute([$campaign_id]);
        $campaign = $stmt_campaign->fetch();
        if(!$campaign) { /* Handle error */ }

        $page_title = 'إدارة صور الحملة: "' . htmlspecialchars($campaign['description']) . '"';
        
        $stmt_photos = $pdo->prepare("SELECT * FROM temp_campaign_photos WHERE temp_campaign_id = ? ORDER BY uploaded_at DESC");
        $stmt_photos->execute([$campaign_id]);
        $photos = $stmt_photos->fetchAll();
        
        $view_file = 'views/promotions/temp_photos_manage.php';
        break;

    case 'list':
    default:
        $stmt = $pdo->prepare("SELECT tc.*, pt.name as promo_type_name, (SELECT COUNT(*) FROM temp_campaign_photos WHERE temp_campaign_id = tc.temp_campaign_id) as photo_count FROM temp_campaigns tc JOIN promotion_types pt ON tc.promo_type_id = pt.promo_type_id WHERE tc.customer_id = ? ORDER BY tc.start_date DESC");
        $stmt->execute([$customer_id]);
        $campaigns = $stmt->fetchAll();
        $view_file = 'views/promotions/temp_campaign_list.php';
        break;
}

include 'views/layout.php';