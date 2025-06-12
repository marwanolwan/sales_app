<?php // views/reports/trend_analysis.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>اتجاه المبيعات والأهداف لسنة <?php echo htmlspecialchars($filter_year); ?></h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد بيانات لعرضها.</p>
        <?php else: ?>
        <div class="chart-container full-width" style="height: 500px;">
            <canvas id="trendAnalysisChart"></canvas>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendAnalysisChart');
    if (ctx && <?php echo json_encode(!empty($report_data)); ?>) {
        const labels = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        const salesData = <?php echo json_encode(array_column($report_data, 'total_sales')); ?>;
        const targetData = <?php echo json_encode(array_column($report_data, 'total_target')); ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'المبيعات المحققة',
                        data: salesData,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        fill: false,
                        tension: 0.1
                    },
                    {
                        label: 'الهدف',
                        data: targetData,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        fill: false,
                        borderDash: [5, 5] // خط متقطع للهدف
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(value) { return value.toLocaleString(); } } }
                }
            }
        });
    }
});
</script>