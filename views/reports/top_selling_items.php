<?php // views/reports/top_selling_items.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; // تضمين نموذج الفلاتر المشترك ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج التقرير (مرتبة حسب الكمية المباعة)</h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد بيانات مبيعات تطابق الفلاتر المحددة.</p>
        <?php else: ?>
        <div class="report-container">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الصنف</th>
                            <th>الكمية المباعة</th>
                            <th>نسبة المساهمة (من الكمية)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // حساب الكمية المتبقية لـ "باقي الأصناف"
                        $other_quantity = $grand_total_quantity;
                        foreach($report_data as $index => $item): 
                            $item_quantity = $item['total_quantity'] ?? 0;
                            // حساب النسبة المئوية بناءً على الكمية
                            $percentage = ($grand_total_quantity > 0) ? ($item_quantity / $grand_total_quantity) * 100 : 0;
                            $other_quantity -= $item_quantity;
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item_quantity, 2); ?></td>
                            <td>
                                <div class="progress-bar-container" title="<?php echo number_format($percentage, 2); ?>%">
                                    <div class="progress-bar" style="width: <?php echo number_format($percentage, 2); ?>%;">
                                        <span class="progress-label"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="chart-container">
                <canvas id="topItemsChart"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('topItemsChart');
    if (ctx && <?php echo json_encode(!empty($report_data)); ?>) {
        
        // استخدام الكميات لتغذية الرسم البياني
        const labels = <?php echo json_encode(array_column($report_data, 'name')); ?>;
        const data = <?php echo json_encode(array_column($report_data, 'total_quantity')); ?>;
        
        // إضافة "باقي الأصناف" إلى الرسم البياني إذا كانت هناك كمية متبقية
        const otherQuantity = <?php echo $other_quantity > 0.01 ? $other_quantity : 0; // استخدام هامش صغير لتجنب القيم الصغيرة جدًا ?>;
        if (otherQuantity > 0) {
            labels.push('باقي الأصناف');
            data.push(otherQuantity);
        }

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'الكمية المباعة',
                    data: data,
                    backgroundColor: [ // مجموعة ألوان متنوعة
                        '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', 
                        '#FF9F40', '#8DDF3E', '#E7E9ED', '#F47A2E', '#C9CBCF'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            // تخصيص النص الذي يظهر عند المرور على الرسم البياني
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                                    const percentage = total > 0 ? (context.parsed / total * 100).toFixed(2) : 0;
                                    // تنسيق الرقم مع فواصل الآلاف
                                    label += number_format(context.parsed, 2) + ` (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: { // إضافة مكتبة Chart.js Datalabels لعرض النسب على الرسم
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += parseFloat(data);
                            });
                            let percentage = sum > 0 ? (value * 100 / sum).toFixed(1) + '%' : '0%';
                            return percentage;
                        },
                        color: '#fff',
                        textShadowColor: 'black',
                        textShadowBlur: 4,
                    }
                }
            }
        });
    }

    // دالة جافاسكريبت لتنسيق الأرقام مثل PHP
    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
});
</script>

<!-- لإظهار النسب المئوية على الرسم البياني، يجب إضافة هذه المكتبة في layout.php -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script> -->
<!-- ويجب تسجيلها في بداية كود الجافاسكريبت: Chart.register(ChartDataLabels); -->


<style>
/* يمكنك نقل هذه الأنماط إلى ملف style.css الرئيسي */
.report-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    align-items: flex-start;
}
.table-container {
    flex: 3;
    min-width: 450px;
    overflow-x: auto;
}
.chart-container {
    flex: 2;
    min-width: 350px;
    position: relative;
    height: 400px;
}
.progress-bar-container {
    width: 100%;
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}
.progress-bar {
    background-color: #28a745; /* لون أخضر */
    color: white;
    text-align: center; /* توسيط النص */
    white-space: nowrap;
    line-height: 1.5;
    padding: 2px 5px;
    border-radius: 5px;
    font-size: 0.8em;
    min-width: 25px; /* لعرض النسبة حتى لو كانت صغيرة */
    box-sizing: border-box;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.2);
    transition: width 0.6s ease;
}
/* تنسيق خاص للجدول ليكون أكثر وضوحًا */
.table-container table th:nth-child(3),
.table-container table td:nth-child(3),
.table-container table th:nth-child(4),
.table-container table td:nth-child(4) {
    text-align: center;
}
.table-container table td:nth-child(2) {
    font-weight: 500;
}
</style>