<?php
// index.php - The Front Controller

// 1. تضمين الملفات الأساسية
require_once 'core/db.php';
require_once 'core/functions.php';

// 2. تحديد الصفحة المطلوبة
$page = $_GET['page'] ?? 'home';

// 3. قائمة بيضاء بالصفحات المسموح بها (Whitelist) - **هنا تم التعديل**
$allowed_pages = [
    'home', 'login', 'logout', 'dashboard',
    'users', 'regions', 'permissions',
    'customer_categories', 'customers',
    'product_families', 'products',
    'sales_targets', 'monthly_sales',
    'item_targets', 
    'item_sales', // <-- **تمت الإضافة هنا**
    'promotion_types', 'promotions',
    'sales_analysis', 'sales_analysis_by_rep', 
    'promotions', 'annual_campaigns',
    'temp_campaigns', 'annual_contracts', 'promotion_reports',
    'reports',
    // 'item_sales_import', // <-- تم حذف هذه لأنها استبدلت بـ item_sales
    'sales_analysis_current_year', 'sales_comparison_yearly',
    'item_target_analysis', 'report_item_sales_by_customer',
    'report_customer_itemized_sales', 'report_item_sales_comparison',
    'collections', 'collections', 'collection_save', 'collection_delete',
    'reports/sales_performance', 'reports/item_sales_analysis',
    'pricing',
    'market_surveys',          // **جديد: صفحة دراسات السوق**
    'market_share',
    'tickets',
    'tasks',   
    'assets',       // **جديد: صفحة الحصة السوقية**
    'posm'
];

// 4. التوجيه إلى المتحكم المناسب
if (in_array($page, $allowed_pages)) {
    // بناء مسار ملف المتحكم
    $controller_file = 'controllers/' . $page . '.php';

    if (file_exists($controller_file)) {
        include $controller_file;
    } else {
        // في حالة أن الصفحة في القائمة البيضاء ولكن الملف غير موجود
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1><p>The controller file for '{$page}' was not found.</p>";
    }
} else {
    // في حالة أن الصفحة غير موجودة في القائمة البيضاء
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1><p>Invalid page requested.</p>";
}

// 5. التعامل مع الصفحة الرئيسية الافتراضية
if ($page === 'home') {
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php?page=dashboard");
    } else {
        header("Location: index.php?page=login");
    }
    exit();
}