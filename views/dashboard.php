<?php
// views/dashboard.php
$month_name = [1=>"يناير", 2=>"فبراير", 3=>"مارس", 4=>"أبريل", 5=>"مايو", 6=>"يونيو", 7=>"يوليو", 8=>"أغسطس", 9=>"سبتمبر", 10=>"أكتوبر", 11=>"نوفمبر", 12=>"ديسمبر"][date('n')];
$year_name = date('Y');
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>ملخص الأداء العام لشهر: <strong><?php echo $month_name . ' ' . $year_name; ?></strong></p>

<!-- 1. بطاقات الملخص السريع (KPIs) -->
<div class="dashboard-grid kpi-grid">
    <div class="summary-card">
        <h3>إجمالي المبيعات النقدية</h3>
        <p class="summary-number"><?php echo number_format($total_cash_sales, 0); ?></p>
        <div class="kpi-target">الهدف: <?php echo number_format($total_cash_target, 0); ?></div>
    </div>
    <div class="summary-card">
        <h3>تحقيق الهدف الإجمالي</h3>
        <p class="summary-number"><?php echo $overall_achievement_percentage; ?>%</p>
        <div class="kpi-progress">
            <div class="kpi-progress-bar" style="width: <?php echo min(100, $overall_achievement_percentage); ?>%;"></div>
        </div>
    </div>
    <div class="summary-card">
        <h3>العملاء الجدد</h3>
        <p class="summary-number"><?php echo $new_customers_count; ?></p>
        <div class="kpi-target">عميل جديد هذا الشهر</div>
    </div>
    <div class="summary-card">
        <h3>تنوع الأصناف المباعة</h3>
        <p class="summary-number"><?php echo $distinct_items_count; ?></p>
        <div class="kpi-target">صنف مختلف تم بيعه</div>
    </div>
</div>

<!-- 2. قسم الرسم البياني لأداء المندوبين -->
<div class="dashboard-grid">
    <div class="dashboard-card full-width-card">
        <h3>أداء المندوبين مقابل الهدف الشهري</h3>
        <?php if (!empty($reps_achievement_data)): ?>
            <div class="chart-container" style="height: <?php echo max(300, count($reps_achievement_data) * 40); ?>px;">
                <canvas id="repsAchievementChart"></canvas>
            </div>
        <?php else: ?>
            <p class="info-message">لا توجد بيانات أهداف أو مبيعات لعرض أداء المندوبين.</p>
        <?php endif; ?>
    </div>
</div>

<!-- 3. قسم القوائم للأفضل أداءً -->
<div class="dashboard-grid two-columns">
    <div class="dashboard-card list-card">
        <h3>أفضل 5 أصناف مبيعًا (قيمةً)</h3>
         <?php if (!empty($top_products)): ?>
            <ol class="performance-list ranked">
                <?php foreach ($top_products as $product): ?>
                    <li>
                        <span class="list-item-name"><?php echo htmlspecialchars($product['name']); ?></span>
                        <span class="list-item-value"><?php echo number_format($product['total_sales_value'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="info-message">لا توجد بيانات مبيعات أصناف لهذا الشهر.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card list-card">
        <h3>أفضل 5 عملاء (قيمةً)</h3>
        <?php if (!empty($top_customers)): ?>
            <ol class="performance-list ranked">
                <?php foreach ($top_customers as $customer): ?>
                    <li>
                        <span class="list-item-name"><?php echo htmlspecialchars($customer['name']); ?></span>
                        <span class="list-item-value"><?php echo number_format($customer['total_sales_value'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="info-message">لا توجد بيانات مبيعات للعملاء لهذا الشهر.</p>
        <?php endif; ?>
    </div>
</div>

<style>
/* يمكنك نقل هذا إلى ملف style.css الرئيسي */
.dashboard-grid { display: grid; gap: 20px; margin-bottom: 20px; }
.kpi-grid { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.two-columns { grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }
.dashboard-card, .summary-card { padding: 20px; border-radius: 8px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.dashboard-card.full-width-card { grid-column: 1 / -1; }
.dashboard-card h3, .summary-card h3 { margin-top: 0; color: #333; font-size: 1rem; margin-bottom: 15px; }
.summary-card { text-align: center; }
.summary-number { font-size: 2.5em; font-weight: 700; color: #007bff; margin-bottom: 5px; line-height: 1; }
.kpi-target { font-size: 0.85rem; color: #888; }
.kpi-progress { height: 8px; background-color: #e9ecef; border-radius: 4px; margin-top: 10px; }
.kpi-progress-bar { height: 100%; background-color: #28a745; border-radius: 4px; }
.chart-container { position: relative; width: 100%; }
.list-card .info-message { text-align: center; margin-top: 20px; }
.performance-list { list-style: none; padding: 0; margin: 0; }
.performance-list li { display: flex; align-items: center; justify-content: space-between; padding: 12px 5px; border-bottom: 1px solid #f0f0f0; }
.performance-list li:last-child { border-bottom: none; }
.performance-list .list-item-name { color: #444; }
.performance-list .list-item-value { font-weight: bold; color: #0056b3; white-space: nowrap; }
.performance-list.ranked { counter-reset: rank; }
.performance-list.ranked li { counter-increment: rank; }
.performance-list.ranked li::before { content: counter(rank); min-width: 24px; height: 24px; background-color: #f0f0f0; border-radius: 50%; display: inline-block; text-align: center; line-height: 24px; margin-left: 15px; font-weight: bold; color: #555; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const repsAchievementCtx = document.getElementById('repsAchievementChart');
    if (repsAchievementCtx && typeof Chart !== 'undefined') {
        new Chart(repsAchievementCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($reps_achievement_data, 'full_name')); ?>,
                datasets: [{
                    label: 'نسبة تحقيق الهدف (%)',
                    data: <?php echo json_encode(array_column($reps_achievement_data, 'achievement_percentage')); ?>,
                    backgroundColor: (context) => {
                        const value = context.raw;
                        if (value >= 100) return 'rgba(40, 167, 69, 0.8)';
                        if (value >= 75) return 'rgba(255, 193, 7, 0.8)';
                        return 'rgba(220, 53, 69, 0.8)';
                    },
                    borderColor: (context) => {
                        const value = context.raw;
                        if (value >= 100) return 'rgba(40, 167, 69, 1)';
                        if (value >= 75) return 'rgba(255, 193, 7, 1)';
                        return 'rgba(220, 53, 69, 1)';
                    },
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: { 
                    x: { 
                        beginAtZero: true,
                        ticks: { callback: (value) => value + "%" },
                        suggestedMax: 110
                    } 
                },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>