<?php // views/reports/product_distribution_details.php ?>

<div class="printable-area">
    <h2><?php echo htmlspecialchars($page_title); ?></h2>

    <div class="actions-bar no-print">
        <?php
        $back_params = $_GET;
        unset($back_params['product_id']);
        $back_url = 'index.php?' . http_build_query($back_params);
        ?>
        <a href="<?php echo $back_url; ?>" class="button-link" style="background-color: #6c757d;">
            <i class="fas fa-arrow-left"></i> العودة لقائمة الأصناف
        </a>
        <button onclick="window.print();" class="button-link">
            <i class="fas fa-print"></i> طباعة
        </button>
    </div>

    <div class="report-container-grid">
        <div class="card">
            <div class="card-header success-header">
                <h3><i class="fas fa-check-circle"></i> نقاط البيع التي اشترت (<?php echo count($customers_who_bought); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if(empty($customers_who_bought)): ?>
                    <p class="info-message">لم يقم أي عميل بشراء هذا الصنف ضمن الفلاتر المحددة.</p>
                <?php else: ?>
                    <!-- =====| بداية التعديل: استخدام جدول لعرض التفاصيل |===== -->
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>نقطة البيع</th>
                                    <th>الكمية الإجمالية</th>
                                    <th>شهور الشراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($customers_who_bought as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                        <td><?php echo number_format($customer['total_quantity_bought'], 2); ?></td>
                                        <td>(<?php echo htmlspecialchars($customer['purchase_months']); ?>)</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- =====| نهاية التعديل |===== -->
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header danger-header">
                <h3><i class="fas fa-times-circle"></i> نقاط البيع التي لم تشترِ (<?php echo count($customers_who_did_not_buy); ?>)</h3>
            </div>
            <div class="card-body">
                 <?php if(empty($customers_who_did_not_buy)): ?>
                    <p class="info-message">ممتاز! جميع العملاء ضمن الفلاتر قاموا بشراء هذا الصنف.</p>
                <?php else: ?>
                    <ul class="item-list">
                    <?php foreach($customers_who_did_not_buy as $customer): ?>
                         <li><?php echo htmlspecialchars($customer['name']); ?> (<?php echo htmlspecialchars($customer['representative_name']); ?>)</li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* نفس الأنماط من الرد السابق */
.report-container-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px; }
.card-header.success-header { background-color: #d4edda; color: #155724; }
.card-header.danger-header { background-color: #f8d7da; color: #721c24; }
.item-list { list-style: none; padding: 0; margin: 0; max-height: 500px; overflow-y: auto; }
.item-list li { padding: 8px; border-bottom: 1px solid #f0f0f0; }
.item-list li:last-child { border-bottom: none; }

/* تنسيق للجدول الجديد */
.table-container { max-height: 500px; overflow-y: auto; }
.table-container table { width: 100%; }
.table-container td:nth-child(2), .table-container td:nth-child(3) { text-align: center; }

@media print { /* ... */ }
</style>