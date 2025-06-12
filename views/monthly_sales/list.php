<?php // views/monthly_sales/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="index.php" method="GET" class="filter-form">
    <input type="hidden" name="page" value="monthly_sales">
    <div style="display:flex; gap:15px; align-items:center; margin-bottom: 20px;">
        <div class="form-group" style="flex:1;">
            <label for="filter_year">السنة:</label>
            <select id="filter_year" name="year" class="form-control">
                <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label for="filter_month">الشهر:</label>
            <select id="filter_month" name="month" class="form-control">
                <?php foreach ($months_array as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo $selected_month == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="button-link" style="align-self:flex-end;">عرض</button>
    </div>
</form>

<div style="margin-bottom: 15px; display:flex; gap:10px;">
    <a href="index.php?page=monthly_sales&action=add&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month; ?>" class="button-link add-btn">تسجيل مبيعات (يدوي)</a>
    <a href="index.php?page=monthly_sales&action=import" class="button-link" style="background-color:#fd7e14;">استيراد مبيعات من Excel</a>
</div>    

<?php if (empty($monthly_sales_list)): ?>
    <p class="info-message">لا توجد مبيعات مسجلة حاليًا لشهر <?php echo $months_array[$selected_month] . " " . $selected_year; ?> للمستخدمين المتاحين.</p>
<?php else: ?>
    <h4>المبيعات المسجلة لشهر <?php echo $months_array[$selected_month] . " " . $selected_year; ?>:</h4>
    <table>
        <thead>
            <tr>
                <th>المندوب</th>
                <th>صافي المبيعات</th>
                <th>ملاحظات</th>
                <th>سجل بواسطة</th>
                <th>تاريخ التسجيل</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monthly_sales_list as $sale_item): ?>
            <tr>
                <td><?php echo htmlspecialchars($sale_item['representative_name']); ?></td>
                <td><?php echo number_format($sale_item['net_sales_amount'], 2); ?></td>
                <td><?php echo nl2br(htmlspecialchars($sale_item['notes'] ?? '-')); ?></td>
                <td><?php echo htmlspecialchars($sale_item['recorder_name'] ?? 'غير معروف'); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($sale_item['recorded_at'])); ?></td>
                <td>
                    <a href="index.php?page=monthly_sales&action=edit&id=<?php echo $sale_item['sale_id']; ?>" class="button-link edit-btn">تعديل</a>
                    <form action="actions/monthly_sales_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد؟');">
                        <input type="hidden" name="sale_id" value="<?php echo $sale_item['sale_id']; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link delete-btn">حذف</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>