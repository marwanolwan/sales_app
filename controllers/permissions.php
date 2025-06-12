<?php
// controllers/permissions.php

require_permission('manage_permissions');

$page_title = "إدارة الصلاحيات";

$roles = ['admin', 'supervisor', 'representative', 'promoter'];
$roles_translation = [
    'admin' => 'مدير النظام',
    'supervisor' => 'مشرف مبيعات',
    'representative' => 'مندوب مبيعات',
    'promoter' => 'مروج مبيعات'
];

// ** المصدر الوحيد للميزات **
// قائمة شاملة بكل الميزات الممكنة في النظام مع ترجمتها.
// هذا يجعل إضافة ميزة جديدة أمرًا سهلاً: فقط أضفها هنا.
$features_list = [
    'manage_users' => 'إدارة المستخدمين',
    'manage_regions' => 'إدارة المناطق',
    'manage_permissions' => 'إدارة الصلاحيات',
    'manage_customer_categories' => 'إدارة تصنيفات العملاء',
    'manage_customers' => 'إدارة العملاء',
    'manage_product_families' => 'إدارة عائلات المنتجات',
    'manage_products' => 'إدارة المنتجات',
    'manage_sales_targets' => 'إدارة الأهداف النقدية',
    'manage_monthly_sales' => 'إدارة المبيعات النقدية',
    'manage_item_sales' => 'إدارة مبيعات الأصناف (كميات)',
    'manage_item_targets' => 'إدارة أهداف الأصناف (كميات)',
    'view_dashboard_summaries' => 'عرض ملخصات لوحة التحكم',
    'view_sales_analysis' => 'عرض تحليل المبيعات والأداء',
    'view_item_target_analysis' => 'عرض تحليل أهداف الأصناف',
    // يمكنك إضافة أي صلاحيات مستقبلية هنا
    // 'view_advanced_reports' => 'عرض التقارير المتقدمة',
];
// فرز القائمة أبجديًا حسب اسم الميزة لترتيب العرض
ksort($features_list);


// جلب الصلاحيات الحالية من قاعدة البيانات
$current_permissions = [];
try {
    $stmt = $pdo->query("SELECT role, feature, can_access FROM role_permissions");
    while ($row = $stmt->fetch()) {
        $current_permissions[$row['role']][$row['feature']] = $row['can_access'];
    }
} catch (PDOException $e) {
    // التعامل مع خطأ جلب الصلاحيات
    error_log("Failed to fetch permissions: " . $e->getMessage());
    // يمكنك تعيين رسالة خطأ هنا إذا أردت
}


$view_file = 'views/permissions_form.php';
include 'views/layout.php';