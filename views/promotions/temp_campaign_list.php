<?php // views/promotions/temp_campaign_list.php ?>

<div class="card">
    <div class="card-header">
        <h2>قائمة حملات الدعاية المؤقتة</h2>
        <div class="actions">
            <a href="index.php?page=promotions&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> ملف الزبون
            </a>
            <a href="index.php?page=temp_campaigns&action=add&customer_id=<?php echo $customer_id; ?>" class="button-link add-btn">
                <i class="fas fa-plus"></i> إضافة حملة مؤقتة جديدة
            </a>
        </div>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>الوصف</th>
                    <th>نوع الدعاية</th>
                    <th>القيمة</th>
                    <th>البدء</th>
                    <th>الانتهاء</th>
                    <th>الحالة</th>
                    <th>الصور</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td><?php echo htmlspecialchars($campaign['description']); ?></td>
                    <td><?php echo htmlspecialchars($campaign['promo_type_name']); ?></td>
                    <td><?php echo number_format($campaign['value'], 2); ?></td>
                    <td><?php echo htmlspecialchars($campaign['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($campaign['end_date']); ?></td>
                    <td><span class="badge status-<?php echo $campaign['status']; ?>"><?php echo htmlspecialchars($campaign['status']); ?></span></td>
                    <td>
                        <a href="index.php?page=temp_campaigns&action=photos&customer_id=<?php echo $customer_id; ?>&campaign_id=<?php echo $campaign['temp_campaign_id']; ?>" class="button-link">
                            إدارة (<?php echo $campaign['photo_count']; ?>)
                        </a>
                    </td>
                    <td class="actions-cell">
                        <a href="index.php?page=temp_campaigns&action=edit&customer_id=<?php echo $customer_id; ?>&campaign_id=<?php echo $campaign['temp_campaign_id']; ?>" class="button-link edit-btn">تعديل</a>
                        <form action="actions/temp_campaign_delete.php" method="POST" onsubmit="return confirm('سيتم حذف الحملة وجميع صورها، هل أنت متأكد؟');" style="display:inline;">
                            <input type="hidden" name="campaign_id" value="<?php echo $campaign['temp_campaign_id']; ?>">
                            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                            <?php csrf_input(); ?>
                            <button type="submit" class="button-link delete-btn">حذف</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($campaigns)): ?>
                <tr><td colspan="8">لا توجد حملات مؤقتة لهذا العميل.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>