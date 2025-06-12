<?php // views/posm/history.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>هنا يمكنك تتبع جميع الحركات التي تمت على هذه المادة الترويجية، من إدخالها للمخزن وحتى تسليمها للعملاء.</p>

<div class="actions-bar">
    <a href="index.php?page=posm&action=items_list" class="button-link" style="background-color: #6c757d;">العودة لقائمة المواد</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>تاريخ الحركة</th>
                <th>نوع الحركة</th>
                <th>الكمية</th>
                <th>المندوب / الموظف</th>
                <th>العميل</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($stock_history)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        لا يوجد سجل حركات لهذه المادة بعد.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($stock_history as $movement): ?>
                    <?php
                        // تحديد كلاس CSS بناءً على نوع الحركة لتمييزها بصريًا
                        $row_class = '';
                        switch ($movement['movement_type']) {
                            case 'Stock In':
                                $row_class = 'stock-in-row';
                                $movement_type_text = 'إدخال للمخزن';
                                break;
                            case 'Dispatch to Rep':
                                $row_class = 'dispatch-row';
                                $movement_type_text = 'صرف لمندوب';
                                break;
                            case 'Deliver to Customer':
                                $row_class = 'deliver-row';
                                $movement_type_text = 'تسليم لعميل';
                                break;
                            default:
                                $movement_type_text = $movement['movement_type'];
                        }
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td><?php echo date('Y-m-d H:i', strtotime($movement['movement_date'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($movement_type_text); ?></strong>
                        </td>
                        <td>
                            <span class="quantity-text <?php echo ($movement['movement_type'] === 'Stock In') ? 'positive' : 'negative'; ?>">
                                <?php echo ($movement['movement_type'] === 'Stock In' ? '+' : '-') . ' ' . (int)$movement['quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($movement['rep_name'] ?? '---'); ?></td>
                        <td><?php echo htmlspecialchars($movement['customer_name'] ?? '---'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($movement['notes'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =====| بداية كود CSS المدمج |===== -->
<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.actions-bar {
    margin-bottom: 20px;
}
.table-container {
    overflow-x: auto;
}
tr.stock-in-row {
    background-color: #e2f0d9; /* أخضر فاتح */
}
tr.dispatch-row {
    background-color: #fff3cd; /* أصفر فاتح */
}
tr.deliver-row {
    background-color: #f8d7da; /* أحمر فاتح */
}
.quantity-text {
    font-weight: bold;
    font-size: 1.1em;
}
.quantity-text.positive {
    color: #28a745;
}
.quantity-text.negative {
    color: #dc3545;
}
</style>