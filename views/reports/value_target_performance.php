<?php // views/reports/value_target_performance.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; // تضمين الفلاتر المشتركة ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج التقرير
            <small>(المستوى: 
            <?php 
                if($group_by_level === 'region') echo 'مناطق';
                elseif($group_by_level === 'supervisor') echo 'مشرفين';
                else echo 'مندوبين';
            ?>)
            </small>
        </h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد أهداف أو مبيعات تطابق الفلاتر المحددة.</p>
        <?php else: ?>
        <div class="report-container-grid">
            <div class="table-container full-width">
                <table>
                    <thead>
                        <tr>
                            <th>
                                <?php 
                                    if($group_by_level === 'region') echo 'المنطقة';
                                    elseif($group_by_level === 'supervisor') echo 'المشرف';
                                    else echo 'المندوب';
                                ?>
                            </th>
                            <?php if ($group_by_level === 'representative'): ?>
                                <th>المشرف</th>
                            <?php elseif ($group_by_level === 'supervisor'): ?>
                                <th>المنطقة</th>
                            <?php endif; ?>
                            <th>إجمالي الهدف</th>
                            <th>إجمالي المبيعات</th>
                            <th>الفرق</th>
                            <th>نسبة تحقيق الهدف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_target = 0;
                        $grand_total_sales = 0;
                        foreach($report_data as $item): 
                            $target = (float)($item['total_target'] ?? 0);
                            $sales = (float)($item['total_sales'] ?? 0);
                            $difference = $sales - $target;
                            $percentage = ($target > 0) ? ($sales / $target) * 100 : 0;
                            $grand_total_target += $target;
                            $grand_total_sales += $sales;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['group_name']); ?></td>
                            <?php if ($group_by_level === 'representative'): ?>
                                <td><?php echo htmlspecialchars($item['supervisor_name']); ?></td>
                            <?php elseif ($group_by_level === 'supervisor'): ?>
                                <td><?php echo htmlspecialchars($item['region_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo number_format($target, 2); ?></td>
                            <td><?php echo number_format($sales, 2); ?></td>
                            <td style="font-weight: bold; color: <?php echo $difference >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                                <?php echo number_format($difference, 2); ?>
                            </td>
                            <td>
                                <div class="progress-bar-container" title="<?php echo number_format($percentage, 2); ?>%">
                                    <div class="progress-bar" style="width: <?php echo min(100, max(0, $percentage)); ?>%; background-color: <?php echo $percentage >= 100 ? '#28a745' : ($percentage >= 80 ? '#17a2b8' : ($percentage >= 50 ? '#ffc107' : '#dc3545')); ?>;">
                                        <span class="progress-label"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="<?php echo ($group_by_level === 'region') ? 1 : 2; ?>">الإجمالي العام</td>
                            <td><?php echo number_format($grand_total_target, 2); ?></td>
                            <td><?php echo number_format($grand_total_sales, 2); ?></td>
                            <?php 
                                $grand_difference = $grand_total_sales - $grand_total_target;
                                $grand_percentage = ($grand_total_target > 0) ? ($grand_total_sales / $grand_total_target) * 100 : 0;
                            ?>
                            <td style="font-weight: bold; color: <?php echo $grand_difference >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;"><?php echo number_format($grand_difference, 2); ?></td>
                            <td><?php echo number_format($grand_percentage, 2); ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="chart-container full-width" style="margin-top: 30px; height: 500px;">
                 <canvas id="valueTargetPerformanceChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('valueTargetPerformanceChart');
    if (ctx && <?php echo json_encode(!empty($report_data)); ?>) {
        
        const labels = <?php echo json_encode(array_column($report_data, 'group_name')); ?>;
        const targetData = <?php echo json_encode(array_column($report_data, 'total_target')); ?>;
        const salesData = <?php echo json_encode(array_column($report_data, 'total_sales')); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'الهدف',
                        data: targetData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    },
                    {
                        label: 'المبيعات المحققة',
                        data: salesData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true, ticks: { callback: function(value) { return value.toLocaleString(); } } },
                }
            }
        });
    }
});
</script>

<style>
/* نفس الأنماط من التقارير السابقة */
.table-container.full-width { flex-basis: 100%; }
.chart-container.full-width { flex-basis: 100%; }
tr.total-row { background-color: #f8f9fa; font-weight: bold; }
</style>