<?php // views/reports/item_target_performance.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; // تضمين الفلاتر المشتركة ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج التقرير</h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد أهداف أو مبيعات للأصناف تطابق الفلاتر المحددة.</p>
        <?php else: ?>
        <div class="report-container-grid">
            <div class="table-container full-width">
                <table>
                    <thead>
                        <tr>
                            <th>المندوب</th>
                            <th>الصنف</th>
                            <th>الكمية المستهدفة</th>
                            <th>الكمية المباعة</th>
                            <th>الكمية المتبقية/الزائدة</th>
                            <th>نسبة تحقيق الهدف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($report_data as $item): 
                            $target = (float)($item['target_quantity'] ?? 0);
                            $sold = (float)($item['total_sold'] ?? 0);
                            $difference = $sold - $target;
                            // حساب النسبة مع التعامل مع الهدف الصفري والقيم السالبة
                            if ($target > 0) {
                                $percentage = ($sold / $target) * 100;
                            } elseif ($sold > 0) {
                                $percentage = 100.0; // تم البيع بدون هدف، يعتبر تحقيق 100%
                            } else {
                                $percentage = 0.0;
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['representative_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo number_format($target, 2); ?></td>
                            <td><?php echo number_format($sold, 2); ?></td>
                            <td style="font-weight: bold; color: <?php echo $difference >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                                <?php echo number_format($difference, 2); ?>
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
                    </tbody>
                </table>
            </div>
            <div class="chart-container full-width" style="margin-top: 30px; height: 500px;">
                 <canvas id="itemTargetPerformanceChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('itemTargetPerformanceChart');
    if (ctx && <?php echo json_encode(!empty($report_data)); ?>) {
        
        const labels = <?php echo json_encode(array_map(function($item) { return $item['representative_name'] . ' - ' . $item['product_name']; }, $report_data)); ?>;
        const targetData = <?php echo json_encode(array_column($report_data, 'target_quantity')); ?>;
        const soldData = <?php echo json_encode(array_column($report_data, 'total_sold')); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'الهدف',
                        data: targetData,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'المبيعات',
                        data: soldData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // عرض الأعمدة بشكل أفقي لتسهيل قراءة الأسماء الطويلة
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            color: '#333'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#333'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
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
.progress-bar-container { width: 100%; background-color: #e9ecef; border-radius: 5px; overflow: hidden; }
.progress-bar { color: white; text-align: center; white-space: nowrap; line-height: 1.5; padding: 2px 5px; border-radius: 5px; font-size: 0.8em; min-width: 30px; box-sizing: border-box; text-shadow: 1px 1px 1px rgba(0,0,0,0.3); transition: width 0.6s ease; }
</style>