<?php // views/reports/rep_effectiveness.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج التقرير</h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد بيانات لعرضها بناءً على الفلاتر المحددة.</p>
        <?php else: ?>
            <div class="table-container full-width">
            <table>
                <thead>
                    <tr>
                        <th>المندوب</th>
                        <th>إجمالي المبيعات</th>
                        <th>تحقيق الهدف (%)</th>
                        <th>عملاء نشطين</th>
                        <th>عملاء جدد</th>
                        <th>أصناف مباعة (فريدة)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // حساب المتوسطات والإجماليات إذا كان هناك أكثر من مندوب
                    $total_reps = count($report_data);
                    $avg_data = [
                        'total_sales' => array_sum(array_column($report_data, 'total_sales')) / $total_reps,
                        'achievement' => 0, // سيتم حسابه بشكل منفصل
                        'active_customer_count' => array_sum(array_column($report_data, 'active_customer_count')) / $total_reps,
                        'new_customer_count' => array_sum(array_column($report_data, 'new_customer_count')) / $total_reps,
                        'unique_sku_count' => array_sum(array_column($report_data, 'unique_sku_count')) / $total_reps,
                    ];
                    $total_target_for_avg = array_sum(array_column($report_data, 'total_target'));
                    $total_sales_for_avg = array_sum(array_column($report_data, 'total_sales'));
                    $avg_data['achievement'] = ($total_target_for_avg > 0) ? ($total_sales_for_avg / $total_target_for_avg * 100) : 0;

                    foreach($report_data as $rep_id => $data): 
                        $target = (float)($data['total_target'] ?? 0);
                        $sales = (float)($data['total_sales'] ?? 0);
                        $percentage = ($target > 0) ? ($sales / $target) * 100 : 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($data['full_name']); ?></td>
                        <td><?php echo number_format($sales, 2); ?></td>
                        <td><?php echo number_format($percentage, 2); ?>%</td>
                        <td><?php echo htmlspecialchars($data['active_customer_count']); ?></td>
                        <td><?php echo htmlspecialchars($data['new_customer_count']); ?></td>
                        <td><?php echo htmlspecialchars($data['unique_sku_count']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="chart-container-wrapper">
            <h3>التحليل البصري للأداء</h3>
            <div id="effectivenessChartsContainer"></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('effectivenessChartsContainer');
    if (container && <?php echo json_encode(!empty($report_data)); ?>) {
        
        const reportData = <?php echo json_encode(array_values($report_data)); ?>;
        const averageData = <?php echo json_encode(isset($avg_data) ? $avg_data : null); ?>;
        const labels = ['تحقيق الهدف %', 'عملاء نشطين', 'عملاء جدد', 'أصناف مباعة'];
        const filterRepId = '<?php echo $filter_rep_id; ?>';

        // إيجاد القيم القصوى لتطبيع البيانات (0-100)
        const maxActiveCustomers = Math.max(...reportData.map(d => d.active_customer_count), 1);
        const maxNewCustomers = Math.max(...reportData.map(d => d.new_customer_count), 1);
        const maxUniqueSkus = Math.max(...reportData.map(d => d.unique_sku_count), 1);

        // دالة لإنشاء رسم بياني
        const createRadarChart = (elementId, title, datasets) => {
            const chartWrapper = document.createElement('div');
            chartWrapper.className = 'chart-item';
            const chartTitle = document.createElement('h4');
            chartTitle.innerText = title;
            const canvas = document.createElement('canvas');
            canvas.id = elementId;
            
            chartWrapper.appendChild(chartTitle);
            chartWrapper.appendChild(canvas);
            container.appendChild(chartWrapper);

            new Chart(canvas, {
                type: 'radar',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: { r: { angleLines: { display: true }, suggestedMin: 0, suggestedMax: 100 } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        };

        const colors = [
            { bg: 'rgba(54, 162, 235, 0.2)', border: 'rgb(54, 162, 235)' },
            { bg: 'rgba(255, 99, 132, 0.2)', border: 'rgb(255, 99, 132)' }
        ];

        if (filterRepId !== 'all' && reportData.length === 1) {
            // عرض رسم بياني واحد للمندوب المختار ومقارنته بالمتوسط
            const repData = reportData[0];
            const target = parseFloat(repData.total_target) || 0;
            const sales = parseFloat(repData.total_sales) || 0;
            const achievement = target > 0 ? (sales / target * 100) : 0;

            const datasets = [{
                label: repData.full_name,
                data: [
                    Math.min(100, achievement),
                    (repData.active_customer_count / maxActiveCustomers) * 100,
                    (repData.new_customer_count / maxNewCustomers) * 100,
                    (repData.unique_sku_count / maxUniqueSkus) * 100
                ],
                backgroundColor: colors[0].bg,
                borderColor: colors[0].border,
                borderWidth: 2
            }];

            if (averageData && reportData.length > 1) { // لا تقارن بالمتوسط إذا كان هو الوحيد
                 datasets.push({
                    label: 'متوسط الفريق',
                    data: [
                        Math.min(100, averageData.achievement),
                        (averageData.active_customer_count / maxActiveCustomers) * 100,
                        (averageData.new_customer_count / maxNewCustomers) * 100,
                        (averageData.unique_sku_count / maxUniqueSkus) * 100
                    ],
                    backgroundColor: colors[1].bg,
                    borderColor: colors[1].border,
                    borderWidth: 2
                 });
            }
            
            createRadarChart('radar-single-rep', 'تحليل أداء: ' + repData.full_name, datasets);

        } else {
            // عرض رسم بياني منفصل لكل مندوب
            reportData.forEach((repData, index) => {
                const target = parseFloat(repData.total_target) || 0;
                const sales = parseFloat(repData.total_sales) || 0;
                const achievement = target > 0 ? (sales / target * 100) : 0;

                const dataset = [{
                    label: repData.full_name,
                    data: [
                        Math.min(100, achievement),
                        (repData.active_customer_count / maxActiveCustomers) * 100,
                        (repData.new_customer_count / maxNewCustomers) * 100,
                        (repData.unique_sku_count / maxUniqueSkus) * 100
                    ],
                    backgroundColor: colors[0].bg,
                    borderColor: colors[0].border,
                    borderWidth: 2
                }];

                createRadarChart('radar-rep-' + index, 'تحليل أداء: ' + repData.full_name, dataset);
            });
        }
    }
});
</script>

<style>
/* يمكنك نقل هذا إلى style.css */
.chart-container-wrapper {
    margin-top: 30px;
}
#effectivenessChartsContainer {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}
.chart-item {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #fff;
}
.chart-item h4 {
    text-align: center;
    margin-bottom: 15px;
    color: #333;
}
</style>