<?php
// views/item_sales/import_preview.php
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <p>الرجاء مراجعة البيانات. الصفوف التي تحتوي على أخطاء (باللون الأحمر) لن يتم استيرادها.</p>
    
    <?php if ($has_errors): ?>
        <div class="message error-message">
            <strong>تم العثور على أخطاء في الملف. لن يتم استيراد الصفوف التي بها أخطاء.</strong>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="preview-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>السنة</th>
                    <th>الشهر</th>
                    <th>رمز العميل</th>
                    <th>رمز الصنف</th>
                    <th>مندوب</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($preview_data as $row_num => $row): ?>
                <tr class="<?php echo isset($import_errors[$row_num]) ? 'row-error' : 'row-success'; ?>">
                    <td><?php echo htmlspecialchars($row_num); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['year']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['month']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['customer_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['product_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['rep_username']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['quantity_sold']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['unit_price']); ?></td>
                    <td><?php echo htmlspecialchars($row['data']['total_value']); ?></td>
                    <td>
                        <?php if (isset($import_errors[$row_num])): ?>
                            <ul class="error-list">
                            <?php foreach ($import_errors[$row_num] as $error_msg): ?>
                                <li><?php echo htmlspecialchars($error_msg); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span style="color: green;">صالح للاستيراد</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
    <div class="form-actions">
        <form action="actions/item_sales_import_confirm.php" method="POST" style="display:inline-block;">
            <?php csrf_input(); ?>
            <button type="submit" class="button-link" <?php echo empty($preview_data) ? 'disabled' : ''; ?>>
                تأكيد واستيراد البيانات الصالحة
            </button>
        </form>
        <a href="index.php?page=item_sales" class="button-link" style="background-color:#6c757d;">إلغاء والعودة</a>
    </div>
</div>
<style>
/* يمكنك نقل هذا إلى ملف style.css الرئيسي */
.preview-table {
    font-size: 0.9em;
}
.preview-table .row-error { 
    background-color: #f8d7da !important;
    color: #721c24;
}
.preview-table .row-success { 
    background-color: #d4edda !important; 
    color: #155724;
}
.preview-table .error-list { 
    margin: 0; 
    padding-right: 15px; 
    color: #721c24; 
    list-style-type: square; 
    text-align: right;
}
</style>