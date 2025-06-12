<?php // views/promotions/annual_campaign_list.php ?>

<div class="card">
    <div class="card-header">
        <h2>قائمة حملات الدعاية السنوية</h2>
        <div class="actions">
            <a href="index.php?page=promotions&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> العودة إلى ملف الزبون
            </a>
            <a href="index.php?page=annual_campaigns&action=add&customer_id=<?php echo $customer_id; ?>" class="button-link add-btn">
                <i class="fas fa-plus"></i> إضافة حملة سنوية جديدة
            </a>
        </div>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>نوع الدعاية</th>
                    <th>تاريخ البدء</th>
                    <th>تاريخ الانتهاء</th>
                    <th>مدة العقد (أشهر)</th>
                    <th>الإيجار الشهري</th>
                    <th>إجمالي قيمة العقد</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td><?php echo htmlspecialchars($campaign['promo_type_name']); ?></td>
                    <td><?php echo htmlspecialchars($campaign['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($campaign['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($campaign['contract_duration_months']); ?></td>
                    <td><?php echo number_format($campaign['monthly_value'], 2); ?></td>
                    <td><?php echo number_format($campaign['total_value'], 2); ?></td>
                    <td class="actions-cell">
                        <a href="index.php?page=annual_campaigns&action=edit&customer_id=<?php echo $customer_id; ?>&campaign_id=<?php echo $campaign['annual_campaign_id']; ?>" class="button-link edit-btn">تعديل</a>
                        <a href="index.php?page=annual_campaigns&action=photos&customer_id=<?php echo $customer_id; ?>&campaign_id=<?php echo $campaign['annual_campaign_id']; ?>" class="button-link" style="background-color:var(--success-color);">إدارة الصور الشهرية</a>
                        <form action="actions/annual_campaign_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                            <input type="hidden" name="campaign_id" value="<?php echo $campaign['annual_campaign_id']; ?>">
                            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                            <?php csrf_input(); ?>
                            <button type="submit" class="button-link delete-btn">حذف</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                 <?php if (empty($campaigns)): ?>
                <tr><td colspan="7">لا توجد حملات سنوية لهذا العميل.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>