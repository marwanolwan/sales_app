<?php
// controllers/promotion_reports.php

require_permission('manage_promotions'); // أو صلاحية خاصة بالتقارير

$action = $_GET['action'] ?? 'dashboard';
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

$page_title = "تقارير الدعاية والعقود";
$view_file = '';

switch ($action) {
    case 'customer_report':
        if (!$customer_id) {
            $_SESSION['error_message'] = "يجب تحديد عميل لعرض التقرير.";
            header("Location: index.php?page=promotion_reports");
            exit();
        }

        // جلب بيانات العميل
        $stmt_cust = $pdo->prepare("SELECT name, customer_code FROM customers WHERE customer_id = ?");
        $stmt_cust->execute([$customer_id]);
        $customer = $stmt_cust->fetch();
        if (!$customer) { /* Handle error */ }

        $page_title = "تقرير الدعاية للزبون: " . htmlspecialchars($customer['name']);

        // --- جلب جميع البيانات اللازمة للتقرير المفصل ---

        // 1. الحملات السنوية
        $stmt_annual = $pdo->prepare("SELECT ac.*, pt.name as promo_type_name FROM annual_campaigns ac JOIN promotion_types pt ON ac.promo_type_id = pt.promo_type_id WHERE ac.customer_id = ? ORDER BY ac.start_date DESC");
        $stmt_annual->execute([$customer_id]);
        $annual_campaigns = $stmt_annual->fetchAll();

        // 2. جلب شهور الصور للحملات السنوية
        $annual_photos_info = [];
        foreach ($annual_campaigns as $campaign) {
            $stmt_photos = $pdo->prepare("SELECT DISTINCT year, month FROM annual_campaign_photos WHERE annual_campaign_id = ?");
            $stmt_photos->execute([$campaign['annual_campaign_id']]);
            $months_with_photos = $stmt_photos->fetchAll(); // نستخدم الوضع الافتراضي وهو FETCH_ASSOC
            $annual_photos_info[$campaign['annual_campaign_id']] = $months_with_photos;
        }

        // 3. الحملات المؤقتة
        $stmt_temp = $pdo->prepare("SELECT tc.*, pt.name as promo_type_name FROM temp_campaigns tc JOIN promotion_types pt ON tc.promo_type_id = pt.promo_type_id WHERE tc.customer_id = ? ORDER BY tc.start_date DESC");
        $stmt_temp->execute([$customer_id]);
        $temp_campaigns = $stmt_temp->fetchAll();

        // 4. العقود السنوية
        $stmt_contracts = $pdo->prepare("SELECT * FROM annual_contracts WHERE customer_id = ? ORDER BY year DESC");
        $stmt_contracts->execute([$customer_id]);
        $contracts = $stmt_contracts->fetchAll();
        
        $view_file = 'views/promotions/customer_promo_report_details.php';
        break;

    case 'dashboard':
    default:
        // التقرير العام: جلب العملاء الذين لديهم أي نوع من الاتفاقيات
        $sql = "SELECT DISTINCT c.customer_id, c.name, c.customer_code,
                (SELECT COUNT(*) FROM annual_campaigns WHERE customer_id = c.customer_id) as annual_count,
                (SELECT COUNT(*) FROM temp_campaigns WHERE customer_id = c.customer_id) as temp_count,
                (SELECT COUNT(*) FROM annual_contracts WHERE customer_id = c.customer_id) as contract_count
                FROM customers c
                WHERE 
                    EXISTS (SELECT 1 FROM annual_campaigns WHERE customer_id = c.customer_id)
                    OR EXISTS (SELECT 1 FROM temp_campaigns WHERE customer_id = c.customer_id)
                    OR EXISTS (SELECT 1 FROM annual_contracts WHERE customer_id = c.customer_id)
                ORDER BY c.name ASC";
        
        $customers_with_promos = $pdo->query($sql)->fetchAll();
        
        // جلب قائمة العملاء للاختيار في الفلتر
        $all_customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        $view_file = 'views/promotions/reports_dashboard.php';
        break;
}

include 'views/layout.php';