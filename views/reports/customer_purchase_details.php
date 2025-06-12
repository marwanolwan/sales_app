<?php // views/reports/customer_purchase_details.php ?>

<div class="printable-area">
    <h2><?php echo htmlspecialchars($page_title); ?></h2>
    
    <div class="actions-bar no-print">
        <?php
        $back_params = $_GET;
        unset($back_params['customer_id']); // إزالة customer_id للعودة للقائمة
        $back_url = 'index.php?' . http_build_query($back_params);
        ?>
        <a href="<?php echo $back_url; ?>" class="button-link" style="background-color: #6c757d;">
            <i class="fas fa-arrow-left"></i> العودة لقائمة الزبائن
        </a>
        <button onclick="window.print();" class="button-link">
            <i class="fas fa-print"></i> طباعة التقرير
        </button>
    </div>

    <div class="card report-summary-card">
        <div class="card-header"><h3>ملخص التغطية</h3></div>
        <div class="card-body">
            <p>نسبة تغطية الأصناف (الأصناف التي تم شراؤها من إجمالي الأصناف النشطة في الشركة):</p>
            <div class="progress-bar-container large-progress">
                <div class="progress-bar" style="width: <?php echo number_format($coverage_percentage, 2); ?>%;">
                    <?php echo number_format($coverage_percentage, 2); ?>%
                </div>
            </div>
            <p style="text-align: center; margin-top: 5px;">(<?php echo $bought_count; ?> صنف من أصل <?php echo $total_active_count; ?> صنف)</p>
        </div>
    </div>
    
    <div class="report-container-grid">
        <div class="card">
            <div class="card-header success-header">
                <h3><i class="fas fa-check-circle"></i> الأصناف المشتراة (<?php echo $bought_count; ?>)</h3>
            </div>
            <div class="card-body">
                <?php if(empty($bought_items)): ?>
                    <p class="info-message">لم يقم العميل بشراء أي صنف خلال الفترة المحددة.</p>
                <?php else: ?>
                <table>
                    <thead><tr><th>الصنف</th><th>الكمية</th><th>شهور الشراء</th></tr></thead>
                    <tbody>
                    <?php foreach($bought_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['total_quantity'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['purchase_months']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header danger-header">
                <h3><i class="fas fa-times-circle"></i> الأصناف غير المشتراة (<?php echo count($not_bought_items); ?>)</h3>
            </div>
            <div class="card-body">
                 <?php if(empty($not_bought_items)): ?>
                    <p class="info-message">ممتاز! قام العميل بشراء جميع الأصناف النشطة.</p>
                <?php else: ?>
                <ul class="item-list">
                    <?php foreach($not_bought_items as $item): ?>
                        <li><?php echo htmlspecialchars($item['name']); ?> (<?php echo htmlspecialchars($item['product_code']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* يمكنك نقل هذا إلى style.css */
.report-container-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
.card-header.success-header { background-color: #d4edda; color: #155724; }
.card-header.danger-header { background-color: #f8d7da; color: #721c24; }
.item-list { list-style: none; padding: 0; margin: 0; max-height: 400px; overflow-y: auto; }
.item-list li { padding: 8px; border-bottom: 1px solid #f0f0f0; }
.item-list li:last-child { border-bottom: none; }
.progress-bar-container.large-progress { height: 30px; line-height: 30px; font-size: 1.1em; }
.progress-bar-container.large-progress .progress-bar { line-height: 30px; }

@media print {
    /* ... (نفس كود الطباعة من الرد السابق) ... */
}
</style>