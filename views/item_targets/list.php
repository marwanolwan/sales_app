<?php // views/item_targets/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block" style="margin-bottom: 20px;">
    <form action="index.php" method="GET" class="filter-form">
        <input type="hidden" name="page" value="item_targets">
        <h4>عرض أهداف الأصناف لـ:</h4>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="year_filter">السنة:</label>
                <select name="year" id="year_filter" class="form-control" onchange="this.form.submit()">
                    <?php for($y = date('Y') + 1; $y >= date('Y') - 3; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected':''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="month_filter">الشهر:</label>
                <select name="month" id="month_filter" class="form-control" onchange="this.form.submit()">
                    <?php foreach($months_map as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo ($selected_month == $num) ? 'selected':''; ?>><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($current_user_role === 'admin' || $current_user_role === 'supervisor'): ?>
            <div class="form-group col-md-3">
                <label for="representative_id_filter">المندوب:</label>
                <select name="representative_id_filter" id="representative_id_filter" class="form-control" onchange="this.form.submit()">
                    <option value="all">جميع المندوبين <?php if($current_user_role === 'supervisor') echo "(التابعين لك)";?></option>
                    <?php foreach($representatives as $rep): ?>
                    <option value="<?php echo $rep['user_id']; ?>" <?php echo ($selected_rep_filter == $rep['user_id']) ? 'selected':''; ?>>
                        <?php echo htmlspecialchars($rep['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<div style="margin-bottom: 15px;">
    <a href="index.php?page=item_targets&action=add&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month; ?>&representative_id_filter=<?php echo $selected_rep_filter; ?>" class="button-link add-btn">إضافة هدف جديد يدويًا</a>
</div>

<?php if (empty($item_targets_grouped)): ?>
    <p class="info-message">لا توجد أهداف مسجلة تطابق الفلاتر الحالية.</p>
<?php else: ?>
    <h4>الأهداف المسجلة لـ <?php echo htmlspecialchars($months_map[$selected_month] ?? ''); ?> <?php echo $selected_year; ?></h4>
    
    <?php foreach ($item_targets_grouped as $rep_name => $targets): ?>
        <div class="rep-targets-block">
            <h3>المندوب: <?php echo htmlspecialchars($rep_name); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>رمز الصنف</th>
                        <th>اسم الصنف</th>
                        <th>الكمية المستهدفة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($targets as $target): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($target['product_code']); ?></td>
                        <td><?php echo htmlspecialchars($target['product_name']); ?></td>
                        <td><?php echo number_format((float)$target['target_quantity'], 2); ?></td>
                        <td>
                            <a href="index.php?page=item_targets&action=edit&id=<?php echo $target['item_target_id']; ?>" class="button-link edit-btn">تعديل</a>
                            <form action="actions/item_target_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد؟');">
                                <input type="hidden" name="item_target_id" value="<?php echo $target['item_target_id']; ?>">
                                <?php csrf_input(); ?>
                                <button type="submit" class="button-link delete-btn">حذف</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
/* You can move this to your main CSS file */
.filter-form .form-row { display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px; }
.filter-form .form-group { position: relative; width: 100%; padding-right: 5px; padding-left: 5px; margin-bottom: 1rem; }
@media (min-width: 768px) {
    .filter-form .form-group.col-md-3 { flex: 0 0 25%; max-width: 25%; }
}
.rep-targets-block {
    margin-bottom: 30px;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    background-color: #f9f9f9;
}
.rep-targets-block h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #007bff;
    color: #007bff;
}
</style>