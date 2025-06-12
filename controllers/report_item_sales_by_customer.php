<?php
// controllers/report_item_sales_by_customer.php

require_permission('view_sales_analysis');

$page_title = "تقرير شراء العملاء لصنف";

// --- الفلاتر ---
$filter_year = $_GET['year'] ?? date('Y');
$filter_period_type = $_GET['period_type'] ?? 'annually';
$selected_month = $_GET['month'] ?? date('n');
$selected_quarter = $_GET['quarter'] ?? ceil(date('n') / 3);
$filter_product_id = $_GET['product_id'] ?? 'all';
$filter_supervisor_id_report = ($_SESSION['user_role'] === 'supervisor') ? $_SESSION['user_id'] : ($_GET['supervisor_id'] ?? 'all');
$filter_representative_id_report = ($_SESSION['user_role'] === 'representative') ? $_SESSION['user_id'] : ($_GET['representative_id'] ?? 'all');
$filter_region_id_report = $_GET['region_id'] ?? 'all';

// --- جلب خيارات الفلاتر (نفس الكود من الملف الأصلي) ---
// ... كود جلب $products_options, $representatives_options_report, $supervisors_options_report, $regions_options_report ...

// --- منطق الاستعلام ---
$customers_sold_to = [];
$customers_not_sold_to = [];
$report_generated = false;
$selected_item_name = "الكل";

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['apply_filters'])) {
    $report_generated = true;
    
    // ... نفس منطق بناء الاستعلام المعقد من ملفك الأصلي ...
    // ... لجلب $customers_sold_to و $customers_not_sold_to
    // ... تأكد من أن هذا المنطق يعمل بشكل صحيح هنا ويعتمد على الفلاتر أعلاه
}

$view_file = 'views/reports/report_item_sales_by_customer.php';
include 'views/layout.php';