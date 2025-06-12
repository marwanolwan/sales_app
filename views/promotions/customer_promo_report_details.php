<?php // views/promotions/customer_promo_report_details.php ?>

<div class="printable-area">
    <h2><?php echo htmlspecialchars($page_title); ?></h2>

    <div class="actions-bar no-print">
        <a href="index.php?page=promotion_reports" class="button-link" style="background-color: #6c757d;">
            <i class="fas fa-arrow-left"></i> العودة للتقارير
        </a>
        <button onclick="window.print();" class="button-link">
            <i class="fas fa-print"></i> طباعة التقرير
        </button>
    </div>

    <!-- ======================= 1. حملات الدعاية السنوية ======================= -->
    <div class="report-section card">
        <div class="card-header"><h3>حملات الدعاية السنوية</h3></div>
        <div class="card-body">
            <?php if(empty($annual_campaigns)): ?>
                <p class="info-message">لا توجد حملات سنوية لهذا الزبون.</p>
            <?php else: ?>
                <?php foreach($annual_campaigns as $campaign): ?>
                    <div class="report-item">
                        <h4>- نوع الدعاية: <?php echo htmlspecialchars($campaign['promo_type_name']); ?></h4>
                        <ul>
                            <li><strong>فترة الحملة:</strong> من <?php echo htmlspecialchars($campaign['start_date']); ?> إلى <?php echo htmlspecialchars($campaign['end_date']); ?></li>
                            <li><strong>مدة العقد:</strong> <?php echo htmlspecialchars($campaign['contract_duration_months']); ?> شهرًا</li>
                        </ul>

                        <h5>حالة الصور الشهرية للحملة:</h5>
                        <div class="table-container monthly-photos-status">
                            <table>
                                <thead>
                                    <tr>
                                        <th>الشهر</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $month_names_ar = [1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل', 5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس', 9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر'];
                                    $months_with_photos_for_this_campaign = $annual_photos_info[$campaign['annual_campaign_id']] ?? [];
                                    $has_photo_map = [];
                                    foreach ($months_with_photos_for_this_campaign as $photo_month) {
                                        $has_photo_map[$photo_month['year'] . '-' . $photo_month['month']] = true;
                                    }
                                    $start = new DateTime($campaign['start_date']);
                                    $end = new DateTime($campaign['end_date']);
                                    $current = clone $start;

                                    while ($current <= $end) {
                                        $year = $current->format('Y');
                                        $month = $current->format('n');
                                        $key = $year . '-' . $month;
                                        $has_photos = isset($has_photo_map[$key]);
                                    ?>
                                    <tr>
                                        <td><?php echo $month_names_ar[$month] . ' ' . $year; ?></td>
                                        <td>
                                            <?php if ($has_photos): ?>
                                                <span class="status-badge status-ok"><i class="fas fa-check-circle"></i> يوجد صور</span>
                                            <?php else: ?>
                                                <span class="status-badge status-missing"><i class="fas fa-times-circle"></i> لا يوجد صور</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                        $current->modify('first day of next month');
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if($campaign['notes']): ?>
                            <p style="margin-top: 15px;"><strong>ملاحظات الحملة:</strong> <?php echo nl2br(htmlspecialchars($campaign['notes'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- =====| بداية الكود الذي تمت إعادته |===== -->

    <!-- ======================= 2. حملات الدعاية المؤقتة ======================= -->
    <div class="report-section card">
        <div class="card-header"><h3>حملات الدعاية المؤقتة</h3></div>
        <div class="card-body">
             <?php if(empty($temp_campaigns)): ?>
                <p class="info-message">لا توجد حملات مؤقتة لهذا الزبون.</p>
            <?php else: ?>
                <?php foreach($temp_campaigns as $campaign): ?>
                    <div class="report-item">
                        <h4>- <?php echo htmlspecialchars($campaign['description']); ?> (<?php echo htmlspecialchars($campaign['promo_type_name']); ?>)</h4>
                         <ul>
                            <li><strong>فترة الحملة:</strong> 
                                <?php if($campaign['start_date'] && $campaign['end_date']): ?>
                                    من <?php echo htmlspecialchars($campaign['start_date']); ?> إلى <?php echo htmlspecialchars($campaign['end_date']); ?>
                                    (<?php
                                        try {
                                            $d1 = new DateTime($campaign['start_date']);
                                            $d2 = new DateTime($campaign['end_date']);
                                            $interval = $d1->diff($d2);
                                            echo $interval->days + 1;
                                        } catch(Exception $e) { echo 0; }
                                    ?> أيام)
                                <?php else: echo 'غير محددة'; endif; ?>
                            </li>
                            <li><strong>الحالة:</strong> <?php echo htmlspecialchars($campaign['status']); ?></li>
                            <li><strong>القيمة:</strong> <?php echo number_format($campaign['value'], 2); ?></li>
                         </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ======================= 3. العقود السنوية ======================= -->
    <div class="report-section card">
        <div class="card-header"><h3>العقود السنوية</h3></div>
        <div class="card-body">
            <?php if(empty($contracts)): ?>
                <p class="info-message">لا توجد عقود سنوية لهذا الزبون.</p>
            <?php else: ?>
                <?php foreach($contracts as $contract): ?>
                    <div class="report-item">
                        <h4>- عقد سنة <?php echo htmlspecialchars($contract['year']); ?></h4>
                        <div class="table-container">
                            <table>
                                <thead><tr><th>الهدف</th><th>قيمة المبيعات المستهدفة</th><th>نسبة الخصم/البونص</th></tr></thead>
                                <tbody>
                                    <tr><td>الهدف 1</td><td><?php echo number_format($contract['target_1_value'] ?? 0, 2); ?></td><td><?php echo ($contract['target_1_bonus'] ?? 0) . "%"; ?></td></tr>
                                    <tr><td>الهدف 2</td><td><?php echo number_format($contract['target_2_value'] ?? 0, 2); ?></td><td><?php echo ($contract['target_2_bonus'] ?? 0) . "%"; ?></td></tr>
                                    <tr><td>الهدف 3</td><td><?php echo number_format($contract['target_3_value'] ?? 0, 2); ?></td><td><?php echo ($contract['target_3_bonus'] ?? 0) . "%"; ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <?php if($contract['notes']): ?>
                            <p><strong>ملاحظات العقد:</strong> <?php echo nl2br(htmlspecialchars($contract['notes'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- =====| نهاية الكود الذي تمت إعادته |===== -->
</div>

<style>
/* نفس الأنماط من الرد السابق */
.report-section { margin-bottom: 25px; }
.report-item { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dotted #ccc; }
.report-item:last-child { border-bottom: none; padding-bottom: 0; }
.report-item h4 { color: var(--primary-color); }
.report-item h5 { margin-top: 20px; margin-bottom: 10px; color: #333; }
.report-item ul { list-style: none; padding-right: 20px; }
.report-item ul li { margin-bottom: 8px; }
.monthly-photos-status table { width: 100%; max-width: 400px; margin-top: 10px; }
.monthly-photos-status th, .monthly-photos-status td { text-align: right; padding: 8px; }
.status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.9em; color: white; }
.status-badge .fas { margin-left: 5px; }
.status-badge.status-ok { background-color: var(--success-color); }
.status-badge.status-missing { background-color: var(--danger-color); }

@media print {
    body * { visibility: hidden; }
    .printable-area, .printable-area * { visibility: visible; }
    .printable-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 15px; }
    .actions-bar.no-print, .sidebar, .main-header, .main-footer-bottom { display: none; }
    .card { box-shadow: none; border: 1px solid #ccc; page-break-inside: avoid; }
}
</style>