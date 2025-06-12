<?php // views/sales_targets/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<!-- Filter Form -->
<form action="index.php?page=sales_targets" method="GET" class="filter-form">
    <input type="hidden" name="page" value="sales_targets">
    <div style="display:flex; gap:15px; align-items:flex-end;">
        <div class="form-group" style="flex:1;">
            <label for="filter_year">السنة:</label>
            <select id="filter_year" name="year" class="form-control" onchange="this.form.submit()">
                <?php for ($y = date('Y') + 1; $y >= date('Y') - 3; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <label for="filter_month">الشهر:</label>
            <select id="filter_month" name="month" class="form-control" onchange="this.form.submit()">
                <?php foreach ($months_array as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo $selected_month == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="button-link" style="padding: 8px 15px;">عرض</button>
    </div>
</form>

<!-- Actions Bar -->
<div class="actions-bar">
    <a href="index.php?page=sales_targets&action=add&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month; ?>" class="button-link add-btn">إضافة هدف (يدوي)</a>
    <a href="index.php?page=sales_targets&action=import_targets" class="button-link" style="background-color:#fd7e14;">استيراد أهداف من Excel</a>
</div>

<h4>الأهداف النقدية لشهر <?php echo $months_array[$selected_month] . " " . $selected_year; ?>:</h4>

<?php if (!empty($targets_list)): ?>
<table>
    <thead>
        <tr>
            <th>المندوب</th>
            <th>مبلغ الهدف</th>
            <th>أدخل بواسطة</th>
            <th>تاريخ الإدخال</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($targets_list as $target_item): ?>
        <tr>
            <td><?php echo htmlspecialchars($target_item['representative_name']); ?></td>
            <td><?php echo number_format($target_item['target_amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($target_item['creator_name'] ?? 'N/A'); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($target_item['created_at'])); ?></td>
            <td>
                <a href="index.php?page=sales_targets&action=edit&id=<?php echo $target_item['target_id']; ?>" class="button-link edit-btn">تعديل</a>
                <form action="actions/sales_target_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد؟');">
                    <input type="hidden" name="target_id" value="<?php echo $target_item['target_id']; ?>">
                    <input type="hidden" name="redirect_year" value="<?php echo $selected_year; ?>">
                    <input type="hidden" name="redirect_month" value="<?php echo $selected_month; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
     <p class="info-message">لا توجد أهداف مسجلة حاليًا لهذه الفترة الزمنية.</p>
<?php endif; ?>

<style>
.filter-form { margin-bottom: 20px; padding:10px; background-color:#f8f9fa; border-radius:5px; }
.actions-bar { margin-bottom: 15px; display:flex; gap:10px; }
</style>