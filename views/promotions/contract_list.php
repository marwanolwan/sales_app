<?php // views/promotions/contract_list.php ?>
<div class="card">
    <div class="card-header">
        <h2>قائمة العقود السنوية</h2>
        <div class="actions">
            <a href="index.php?page=promotions&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> ملف الزبون
            </a>
            <a href="index.php?page=annual_contracts&action=add&customer_id=<?php echo $customer_id; ?>" class="button-link add-btn">
                <i class="fas fa-plus"></i> إضافة عقد سنوي جديد
            </a>
        </div>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>سنة العقد</th>
                    <th>الهدف 1 (قيمة/خصم%)</th>
                    <th>الهدف 2 (قيمة/خصم%)</th>
                    <th>الهدف 3 (قيمة/خصم%)</th>
                    <th>ملف العقد</th>
                    <th>ملاحظات</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $contract): ?>
                <tr>
                    <td><?php echo htmlspecialchars($contract['year']); ?></td>
                    <td><?php echo number_format($contract['target_1_value'] ?? 0, 2) . " / " . ($contract['target_1_bonus'] ?? 0) . "%"; ?></td>
                    <td><?php echo number_format($contract['target_2_value'] ?? 0, 2) . " / " . ($contract['target_2_bonus'] ?? 0) . "%"; ?></td>
                    <td><?php echo number_format($contract['target_3_value'] ?? 0, 2) . " / " . ($contract['target_3_bonus'] ?? 0) . "%"; ?></td>
                    <td>
                        <?php if ($contract['contract_file_path']): ?>
                            <a href="uploads/annual_contracts/<?php echo htmlspecialchars($contract['contract_file_path']); ?>" target="_blank" class="button-link">عرض/تحميل</a>
                        <?php else: echo 'لا يوجد'; endif; ?>
                    </td>
                    <td><?php echo nl2br(htmlspecialchars($contract['notes'] ?? '')); ?></td>
                    <td class="actions-cell">
                        <a href="index.php?page=annual_contracts&action=edit&customer_id=<?php echo $customer_id; ?>&id=<?php echo $contract['contract_id']; ?>" class="button-link edit-btn">تعديل</a>
                        <form action="actions/annual_contract_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                            <input type="hidden" name="contract_id" value="<?php echo $contract['contract_id']; ?>">
                            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                            <?php csrf_input(); ?>
                            <button type="submit" class="button-link delete-btn">حذف</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($contracts)): ?>
                <tr><td colspan="7">لا توجد عقود سنوية لهذا العميل.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>