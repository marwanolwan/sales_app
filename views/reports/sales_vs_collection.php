<?php // views/reports/sales_vs_collection.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

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
            <p class="info-message">لا توجد بيانات مبيعات أو تحصيل تطابق الفلاتر المحددة.</p>
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
                            <th>إجمالي المبيعات</th>
                            <th>إجمالي التحصيل</th>
                            <th>الرصيد المتبقي</th>
                            <th>نسبة التحصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total_sales = 0;
                        $grand_total_collections = 0;
                        foreach($report_data as $item): 
                            $sales = (float)($item['total_sales'] ?? 0);
                            $collections = (float)($item['total_collections'] ?? 0);
                            $balance = $sales - $collections;
                            $percentage = ($sales > 0) ? ($collections / $sales) * 100 : 0;
                            $grand_total_sales += $sales;
                            $grand_total_collections += $collections;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['group_name']); ?></td>
                            <td><?php echo number_format($sales, 2); ?></td>
                            <td><?php echo number_format($collections, 2); ?></td>
                            <td style="font-weight: bold; color: <?php echo $balance <= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                                <?php echo number_format($balance, 2); ?>
                            </td>
                            <td>
                                <div class="progress-bar-container" title="<?php echo number_format($percentage, 2); ?>%">
                                    <div class="progress-bar" 
                                         style="width: <?php echo min(100, max(0, $percentage)); ?>%; background-color: <?php echo $percentage >= 100 ? '#28a745' : ($percentage >= 80 ? '#17a2b8' : ($percentage >= 50 ? '#ffc107' : '#dc3545')); ?>;">
                                        <span class="progress-label"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td>الإجمالي العام</td>
                            <td><?php echo number_format($grand_total_sales, 2); ?></td>
                            <td><?php echo number_format($grand_total_collections, 2); ?></td>
                            <?php 
                                $grand_balance = $grand_total_sales - $grand_total_collections;
                                $grand_percentage = ($grand_total_sales > 0) ? ($grand_total_collections / $grand_total_sales) * 100 : 0;
                            ?>
                            <td style="color: <?php echo $grand_balance <= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;"><?php echo number_format($grand_balance, 2); ?></td>
                            <td><?php echo number_format($grand_percentage, 2); ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="chart-container full-width" style="margin-top: 30px; height: 500px;">
                 <canvas id="salesVsCollectionChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesVsCollectionChart');
    if (ctx && <?php echo json_encode(!empty($report_data)); ?>) {
        
        const labels = <?php echo json_encode(array_column($report_data, 'group_name')); ?>;
        const salesData = <?php echo json_encode(array_column($report_data, 'total_sales')); ?>;
        const collectionData = <?php echo json_encode(array_column($report_data, 'total_collections')); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'إجمالي المبيعات',
                        data: salesData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    },
                    {
                        label: 'إجمالي التحصيل',
                        data: collectionData,
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
.progress-bar-container { width: 100%; background-color: #e9ecef; border-radius: 5px; overflow: hidden; }
.progress-bar { color: white; text-align: center; white-space: nowrap; line-height: 1.5; padding: 2px 5px; border-radius: 5px; font-size: 0.8em; min-width: 30px; box-sizing: border-box; text-shadow: 1px 1px 1px rgba(0,0,0,0.3); transition: width 0.6s ease; }
</style>