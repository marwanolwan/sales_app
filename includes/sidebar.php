<?php
// sidebar.php

// الحصول على الصفحة الحالية لتحديد الرابط النشط
$current_page = $_GET['page'] ?? 'home';

// قائمة الصفحات المتعلقة بالترويج لتحديد القسم النشط
$promotion_pages = [
    'promotions', 'promotion_types', 'annual_campaigns', 
    'temp_campaigns', 'annual_contracts', 'promotion_reports'
];
$is_promotion_section = in_array($current_page, $promotion_pages);
?>
<aside class="sidebar">
    <nav>
        <ul>
            <?php if (check_permission('view_dashboard_summaries')): ?>
                <li><a href="index.php?page=dashboard" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">لوحة التحكم</a></li>
            <?php endif; ?>

            <li class="menu-header">إدارة النظام</li>
            <?php if (check_permission('manage_users')): ?>
                <li><a href="index.php?page=users" class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">إدارة المستخدمين</a></li>
            <?php endif; ?>
            <?php if (check_permission('manage_regions')): ?>
                <li><a href="index.php?page=regions" class="<?php echo ($current_page == 'regions') ? 'active' : ''; ?>">إدارة المناطق</a></li>
            <?php endif; ?>
             <?php if (check_permission('manage_permissions')): ?>
                <li><a href="index.php?page=permissions" class="<?php echo ($current_page == 'permissions') ? 'active' : ''; ?>">إدارة الصلاحيات</a></li>
            <?php endif; ?>

            <li class="menu-header">إدارة المنتجات والعملاء</li>
            <?php if (check_permission('manage_customer_categories')): ?>
                <li><a href="index.php?page=customer_categories" class="<?php echo ($current_page == 'customer_categories') ? 'active' : ''; ?>">تصنيفات العملاء</a></li>
            <?php endif; ?>
            <?php if (check_permission('manage_customers')): ?>
                <li><a href="index.php?page=customers" class="<?php echo ($current_page == 'customers') ? 'active' : ''; ?>">إدارة العملاء</a></li>
            <?php endif; ?>
            <?php if (check_permission('manage_product_families')): ?>
                <li><a href="index.php?page=product_families" class="<?php echo ($current_page == 'product_families') ? 'active' : ''; ?>">عائلات المنتجات</a></li>
            <?php endif; ?>
            <?php if (check_permission('manage_products')): ?>
                <li><a href="index.php?page=products" class="<?php echo ($current_page == 'products') ? 'active' : ''; ?>">إدارة المنتجات</a></li>
            <?php endif; ?>

            <!-- =====| بداية القسم الجديد |===== -->
            <li class="menu-header">إدارة الترويج</li>
            <?php if (check_permission('manage_promotions')): // الصلاحية الجديدة ?>
                <li><a href="index.php?page=promotion_types" class="<?php echo ($current_page == 'promotion_types') ? 'active' : ''; ?>">أنواع الدعاية</a></li>
                <li><a href="index.php?page=promotions" class="<?php echo $is_promotion_section && $current_page != 'promotion_types' ? 'active' : ''; ?>">خدمات الدعاية والعقود</a></li>
            <?php endif; ?>
            <!-- =====| نهاية القسم الجديد |===== -->


            <li class="menu-header">إدارة المبيعات والأهداف</li>
             <?php if (check_permission('manage_sales_targets')): ?>
                <li><a href="index.php?page=sales_targets" class="<?php echo ($current_page == 'sales_targets') ? 'active' : ''; ?>">الأهداف النقدية</a></li>
            <?php endif; ?>
            <?php if (check_permission('manage_monthly_sales')): ?>
                <li><a href="index.php?page=monthly_sales" class="<?php echo ($current_page == 'monthly_sales') ? 'active' : ''; ?>">المبيعات النقدية</a></li>
            <?php endif; ?>
            <?php if (check_permission('manage_item_targets')): ?>
                 <li><a href="index.php?page=item_targets" class="<?php echo ($current_page == 'item_targets') ? 'active' : ''; ?>">أهداف الأصناف (كميات)</a></li>
            <?php endif; ?>
            <?php if (check_permission('item_sales')): ?>
                 <li><a href="index.php?page=item_sales" class="<?php echo ($current_page == 'item_sales') ? 'active' : ''; ?>">مبيعات الأصناف (كميات)</a></li>
            <?php endif; ?>
             <?php if (check_permission('manage_pricing')): ?>
                            <li><a href="index.php?page=pricing" class="<?php echo ($current_page == 'pricing') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-tags"></i> إدارة التسعير
                </a></li>
            <?php endif; ?>
<?php if (check_permission('manage_collections')): ?>
    <li><a href="index.php?page=collections" class="<?php echo ($current_page == 'collections') ? 'active' : ''; ?>">إدارة التحصيل</a></li>
<?php endif; ?>

<!-- =====| بداية القسم الجديد |===== -->
            <?php if (check_permission('view_market_surveys') || check_permission('manage_market_share')): ?>
            <li class="menu-header">تحليل السوق</li>
                <?php if (check_permission('view_market_surveys')): ?>
                    <li><a href="index.php?page=market_surveys" class="<?php echo ($current_page == 'market_surveys') ? 'active' : ''; ?>">دراسات السوق والأسعار</a></li>
                <?php endif; ?>
                <?php if (check_permission('manage_market_share')): ?>
                    <li><a href="index.php?page=market_share" class="<?php echo ($current_page == 'market_share') ? 'active' : ''; ?>">الحصة السوقية</a></li>
                <?php endif; ?>
            <?php endif; ?>
            <!-- =====| نهاية القسم الجديد |===== -->
                <li class="menu-header">إدارة المهام</li>
                <li><a href="index.php?page=tasks" class="<?php echo ($current_page == 'tasks') ? 'active' : ''; ?>">لوحة المهام</a></li>
            <!-- في sidebar.php -->
             <!-- في sidebar.php -->
              
                <?php if (check_permission('manage_assets')): // صلاحية جديدة ?>
                <li class="menu-header">الأصول والمواد الترويجية</li>
                <li><a href="index.php?page=assets" class="<?php echo ($current_page == 'assets') ? 'active' : ''; ?>">الأصول الثابتة</a></li>
                <li><a href="index.php?page=posm" class="<?php echo ($current_page == 'posm') ? 'active' : ''; ?>">المواد الترويجية (POSM)</a></li>
                <?php endif; ?>

                <?php if (check_permission('manage_tickets')): // صلاحية جديدة يجب إضافتها ?>
                <li class="menu-header">الدعم والشكاوى</li>
                <li><a href="index.php?page=tickets" class="<?php echo ($current_page == 'tickets') ? 'active' : ''; ?>">نظام الشكاوي</a></li>
                <?php endif; ?>
                            <li class="menu-header">التقارير والتحليلات</li>
                <?php if (check_permission('view_reports')): ?>
                    <li><a href="index.php?page=reports" class="<?php echo ($current_page == 'reports') ? 'active' : ''; ?>">مركز التقارير</a></li>
                <?php endif; ?>
<!-- في sidebar.php -->
        </ul>
    </nav>
</aside>
<style>
/* يمكنك إضافة هذه الأنماط إلى ملف style.css الرئيسي */
.sidebar .menu-header {
    padding: 10px 15px;
    margin-top: 15px;
    font-size: 0.8em;
    font-weight: bold;
    color: #888;
    text-transform: uppercase;
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
}
.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
/* تمييز الرابط النشط */
.sidebar a.active {
    background-color: #007bff;
    color: white;
    border-right: 3px solid #0056b3;
}
.sidebar a:hover {
    background-color: #f0f0f0;
}
</style>