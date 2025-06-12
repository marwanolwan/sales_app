<?php // views/promotions/report_summary.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=promotions&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color: #6c757d;">
        <i class="fas fa-arrow-left"></i> العودة إلى ملف الزبون
    </a>
    <button onclick="window.print();" class="button-link"><i class="fas fa-print"></i> طباعة التقرير</button>
</div>

<div class="report-section card">
    <div class="card-header"><h3>2. حملات الدعاية السنوية</h3></div>
    <div class="card-body">
        <?php if(empty($annual_campaigns)): ?>
            <p>لا توجد بيانات.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>نوع الدعاية</th><th>تاريخ البدء</th><th>الانتهاء</th><th>مدة العقد</th><th>الإيجار الشهري</th><th>إجمالي العقد</th><th>ملاحظات</th></tr>
            </thead>
            <tbody>
            <?php foreach($annual_campaigns as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['promo_type_name']); ?></td>
                    <td><?php echo htmlspecialchars($c['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($c['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($c['contract_duration_months']); ?></td>
                    <td><?php echo number_format($c['monthly_value'], 2); ?></td>
                    <td><?php echo number_format($c['total_value'], 2); ?></td>
                    <td><?php echo htmlspecialchars($c['notes'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div class="report-section card">
    <div class="card-header"><h3>3. حملات الدعاية المؤقتة</h3></div>
    <div class="card-body">
         <?php if(empty($temp_campaigns)): ?>
            <p>لا توجد بيانات.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>الوصف</th><th>تاريخ البدء</th><th>الانتهاء</th><th>الحالة</th><th>القيمة</th><th>ملاحظات</th></tr>
            </thead>
            <tbody>
            <?php foreach($temp_campaigns as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['description']); ?></td>
                    <td><?php echo htmlspecialchars($c['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($c['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($c['status']); ?></td>
                    <td><?php echo number_format($c['value'], 2); ?></td>
                    <td><?php echo htmlspecialchars($c['notes'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div class="report-section card">
    <div class="card-header"><h3>4. العقود السنوية</h3></div>
    <div class="card-body">
         <?php if(empty($contracts)): ?>
            <p>لا توجد بيانات.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>سنة العقد</th><th>الهدف 1 (قيمة/خصم%)</th><th>الهدف 2 (قيمة/خصم%)</th><th>الهدف 3 (قيمة/خصم%)</th><th>ملاحظات</th></tr>
            </thead>
            <tbody>
            <?php foreach($contracts as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['year']); ?></td>
                    <td><?php echo number_format($c['target_1_value'] ?? 0, 2) . " / " . ($c['target_1_bonus'] ?? 0) . "%"; ?></td>
                    <td><?php echo number_format($c['target_2_value'] ?? 0, 2) . " / " . ($c['target_2_bonus'] ?? 0) . "%"; ?></td>
                    <td><?php echo number_format($c['target_3_value'] ?? 0, 2) . " / " . ($c['target_3_bonus'] ?? 0) . "%"; ?></td>
                    <td><?php echo htmlspecialchars($c['notes'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .content-area, .content-area * { visibility: visible; }
    .content-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
    .actions-bar, .main-header, .sidebar, .main-footer-bottom { display: none; }
}
</style>