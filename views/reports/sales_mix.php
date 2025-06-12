<?php // views/reports/sales_mix.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>
            نتائج التقرير
            <?php // إذا كنا في عرض تفصيلي، أضف زر العودة ?>
            <?php if($filter_family_id !== 'all'): ?>
                <a href="index.php?page=reports&type=sales_mix" class="button-link" style="font-size: 0.8em; background-color:#6c757d; float:left;">
                    <i class="fas fa-arrow-left"></i> العودة لعرض كل العائلات
                </a>
            <?php endif; ?>
        </h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد بيانات مبيعات تطابق الفلاتر المحددة.</p>
        <?php else: 
            // حساب الإجمالي الكلي للقيمة والكمية
            $grand_total_value = array_sum(array_column($report_data, 'total_value'));
            $grand_total_quantity = array_sum(array_column($report_data, 'total_quantity'));
        ?>
        <div class="report-container-grid">
            <div class="table-container full-width">
                <table>
                    <thead>
                        <tr>
                            <th><?php echo $filter_family_id === 'all' ? 'عائلة المنتج' : 'المنتج'; ?></th>
                            <th>إجمالي القيمة</th>
                            <th>إجمالي الكمية</th>
                            <th>نسبة المساهمة (من القيمة)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($report_data as $item): 
                            $value = (float)($item['total_value'] ?? 0);
                            $quantity = (float)($item['total_quantity'] ?? 0);
                            $percentage = ($grand_total_value > 0) ? ($value / $grand_total_value) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <?php if ($filter_family_id === 'all'): // إذا كنا في عرض العائلات، اجعل الاسم رابطًا ?>
                                    <a href="index.php?page=reports&type=sales_mix&family_id=<?php echo $item['family_id']; ?>&<?php echo http_build_query(array_diff_key($_GET, ['family_id'=>'', 'page'=>'', 'type'=>''])); ?>">
                                        <?php echo htmlspecialchars($item['group_name']); ?>
                                    </a>
                                <?php else: // وإلا، اعرض اسم المنتج فقط ?>
                                    <?php echo htmlspecialchars($item['group_name']); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($value, 2); ?></td>
                            <td><?php echo number_format($quantity, 2); ?></td>
                            <td>
                                <div class="progress-bar-container" title="<?php echo number_format($percentage, 2); ?>%">
                                    <div class="progress-bar" style="width: <?php echo number_format($percentage, 2); ?>%;">
                                        <span class="progress-label"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td>الإجمالي العام</td>
                            <td><?php echo number_format($grand_total_value, 2); ?></td>
                            <td><?php echo number_format($grand_total_quantity, 2); ?></td>
                            <td>100.0%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="chart-container full-width" style="margin-top: 30px; height: 500px;">
                 <canvas id="salesMixChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesMixChart');
    if (ctx && <?php echo json_encode(!empty($report_data)); ?>) {
        
        const labels = <?php echo json_encode(array_column($report_data, 'group_name')); ?>;
        const data = <?php echo json_encode(array_column($report_data, 'total_value')); ?>;

        new Chart(ctx, {
            type: 'doughnut', // يمكن استخدام 'pie' أو 'doughnut'
            data: {
                labels: labels,
                datasets: [{
                    label: 'قيمة المبيعات',
                    data: data,
                    backgroundColor: [
                        '#4BC0C0', '#FF6384', '#FFCE56', '#9966FF', '#36A2EB',
                        '#FF9F40', '#E7E9ED', '#8DDF3E', '#F47A2E', '#C9CBCF'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    const total = <?php echo $grand_total_value; ?>;
                                    const percentage = total > 0 ? (context.parsed / total * 100).toFixed(2) : 0;
                                    label += number_format(context.parsed, 2) + ` (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    // دالة number_format كما هي
    function number_format(number, decimals, dec_point, thousands_sep) { /* ... */ }
});
</script>

<style>
/* نفس الأنماط من التقارير السابقة */
</style>