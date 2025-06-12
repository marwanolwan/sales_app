<?php // views/promotions/customer_dashboard.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<a href="index.php?page=promotions" class="back-link">« العودة إلى قائمة الزبائن</a>

<div class="services-grid">
    <div class="service-card">
        <i class="fas fa-calendar-alt service-icon"></i>
        <h3>الدعاية السنوية</h3>
        <p>إدارة حملات الدعاية السنوية داخل نقاط البيع للزبون.</p>
        <a href="index.php?page=annual_campaigns&customer_id=<?php echo $customer_id; ?>" class="button-link">إدارة الدعاية السنوية</a>
    </div>
    <div class="service-card">
        <i class="fas fa-pencil-alt service-icon"></i>
        <h3>الدعاية المؤقتة</h3>
        <p>إدارة حملات الدعاية والترويجية الخاصة بالزبون.</p>
        <a href="index.php?page=temp_campaigns&customer_id=<?php echo $customer_id; ?>" class="button-link">إدارة الدعاية المؤقتة</a>
    </div>
    <div class="service-card">
        <i class="fas fa-file-contract service-icon"></i>
        <h3>العقود السنوية</h3>
        <p>إنشاء وتتبع العقود السنوية المبرمة مع الزبون.</p>
        <a href="index.php?page=annual_contracts&customer_id=<?php echo $customer_id; ?>" class="button-link">إدارة العقود</a>
    </div>
    <div class="service-card">
        <i class="fas fa-chart-pie service-icon"></i>
        <h3>التقارير</h3>
        <p>عرض تقارير شاملة وتحليلات لأداء الزبون وحملاته.</p>
        <a href="index.php?page=promotion_reports&customer_id=<?php echo $customer_id; ?>" class="button-link">عرض التقرير</a>
    </div>
</div>

<style>
/* يمكنك إضافة أيقونات FontAwesome لمظهر أفضل */
.back-link { display: inline-block; margin-bottom: 20px; color: var(--primary-color); }
.services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
.service-card { text-align: center; padding: 30px 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.service-icon { font-size: 3em; color: var(--primary-color); margin-bottom: 15px; }
.service-card h3 { margin: 10px 0; }
.service-card p { color: #666; margin-bottom: 20px; min-height: 40px; }
.service-card .button-link { background-color: #555; }
</style>