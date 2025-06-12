<?php // views/posm/items_list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>هنا يمكنك تعريف جميع المواد الترويجية التي تستخدمها الشركة، مثل البوسترات، العينات، الهدايا، إلخ.</p>

<div class="actions-bar">
    <a href="index.php?page=posm&action=add_item" class="button-link add-btn">إضافة مادة جديدة</a>
    <a href="index.php?page=posm&action=dashboard" class="button-link" style="background-color: #6c757d;">العودة للوحة التحكم</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>كود المادة</th>
                <th>اسم المادة الترويجية</th>
                <th>الوصف</th>
                <th style="text-align: center;">الرصيد الحالي بالمخزن</th>
                <th style="width: 220px;">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items_list)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        لم يتم تعريف أي مواد ترويجية بعد. ابدأ بإضافة مادة جديدة.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($items_list as $item): ?>
                    <tr>
                        <td><?php echo $item['item_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($item['description'] ?? '')); ?></td>
                        <td style="text-align: center;">
                            <?php 
                                $stock = (int)($item['current_stock'] ?? 0);
                                $stock_class = $stock > 10 ? 'stock-ok' : ($stock > 0 ? 'stock-low' : 'stock-empty');
                            ?>
                            <span class="stock-badge <?php echo $stock_class; ?>">
                                <?php echo $stock; ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <a href="index.php?page=posm&action=history&id=<?php echo $item['item_id']; ?>" class="button-link" style="background-color: #5bc0de;">
                                عرض السجل
                            </a>
                            <a href="index.php?page=posm&action=edit_item&id=<?php echo $item['item_id']; ?>" class="button-link edit-btn">
                                تعديل
                            </a>
 
                    <!-- =====| بداية الإضافة: نموذج الحذف |===== -->
                    <?php if ($stock == 0): // السماح بالحذف فقط إذا كان الرصيد صفرًا ?>
                        <form action="actions/posm_item_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه المادة بشكل نهائي؟ سيتم حذف سجل حركاتها أيضًا.');">
                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                            <?php csrf_input(); ?>
                            <button type="submit" class="button-link delete-btn">حذف</button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="button-link disabled-btn" title="لا يمكن حذف المادة لأن رصيدها الحالي ليس صفرًا.">حذف</button>
                    <?php endif; ?>
                    <!-- =====| نهاية الإضافة |===== -->                            <?php if ($stock == 0): ?>
                                <!-- <form action="actions/posm_item_delete.php" ... > -->
                            <?php endif; ?>
                        </td>
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
.actions-cell {
    display: flex;
    gap: 8px;
    align-items: center;
}
.actions-cell .button-link {
    padding: 5px 10px;
    font-size: 0.9em;
}

/* Stock Badge Styles */
.stock-badge {
    display: inline-block;
    padding: 6px 15px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    font-size: 1.1em;
}
.stock-ok {
    background-color: #28a745; /* Success */
}
.stock-low {
    background-color: #ffc107; /* Warning */
    color: #212529;
}
.stock-empty {
    background-color: #dc3545; /* Danger */
}
</style>