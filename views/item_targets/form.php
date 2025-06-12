<?php // views/item_targets/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/item_target_save.php" method="POST">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action === 'edit' && $item_target_id): ?>
        <input type="hidden" name="item_target_id" value="<?php echo $item_target_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>
    
    <div class="form-group">
        <label>الفترة:</label>
        <p><strong><?php echo htmlspecialchars($months_map[$item_target_data['month'] ?? $selected_month] ?? ''); ?> / <?php echo htmlspecialchars($item_target_data['year'] ?? $selected_year); ?></strong></p>
        <input type="hidden" name="year" value="<?php echo htmlspecialchars($item_target_data['year'] ?? $selected_year); ?>">
        <input type="hidden" name="month" value="<?php echo htmlspecialchars($item_target_data['month'] ?? $selected_month); ?>">
    </div>

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="representative_id">المندوب:</label>
            <select name="representative_id" id="representative_id" class="form-control" required <?php if($action === 'edit') echo 'disabled'; ?>>
                <option value="">-- اختر المندوب --</option>
                <?php foreach($representatives as $rep): ?>
                <option value="<?php echo $rep['user_id']; ?>" <?php echo ($item_target_data['representative_id'] ?? '') == $rep['user_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rep['full_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
             <?php if($action === 'edit'): ?>
                <input type="hidden" name="representative_id" value="<?php echo $item_target_data['representative_id']; ?>">
            <?php endif; ?>
        </div>
        <div class="form-group col-md-4">
            <label for="product_id">الصنف:</label>
            <!-- **التحسين المطلوب: إضافة كلاس لتفعيل Select2** -->
             <select name="product_id" id="product_id" class="form-control select2-search" required <?php if($action === 'edit') echo 'disabled'; ?>>
                <option value="">-- اختر الصنف --</option>
                <?php foreach($products_list as $prod): ?>
                <option value="<?php echo $prod['product_id']; ?>" <?php echo ($item_target_data['product_id'] ?? '') == $prod['product_id'] ? 'selected':''; ?>>
                    <?php echo htmlspecialchars($prod['name']) . " (".htmlspecialchars($prod['product_code']).")"; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if($action === 'edit'): ?>
                <input type="hidden" name="product_id" value="<?php echo $item_target_data['product_id']; ?>">
            <?php endif; ?>
        </div>
        <div class="form-group col-md-4">
            <label for="target_quantity">الكمية المستهدفة:</label>
            <input type="number" step="0.01" name="target_quantity" id="target_quantity" class="form-control" 
                   value="<?php echo htmlspecialchars($item_target_data['target_quantity'] ?? '0.00'); ?>" required min="0">
        </div>
    </div>
    
    <button type="submit" class="button-link"><?php echo $action === 'add' ? 'إضافة الهدف' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=item_targets&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month; ?>&representative_id_filter=<?php echo $selected_rep_filter; ?>" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>

<script>
// **التحسين المطلوب: كود تفعيل Select2**
// يمكنك نقل هذا الكود إلى js/script.js ليبقى الكود أنظف
$(document).ready(function() {
    $('.select2-search').select2({
        placeholder: "ابحث عن صنف...",
        allowClear: true,
        dir: "rtl" // لدعم اللغة العربية
    });
});
</script>