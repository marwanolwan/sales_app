<?php // views/sales_targets/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/sales_target_save.php" method="POST">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($target_data['year'] ?? $selected_year); ?>">
    <input type="hidden" name="month" value="<?php echo htmlspecialchars($target_data['month'] ?? $selected_month); ?>">
    <?php if ($action == 'edit' && $target_id): ?>
        <input type="hidden" name="target_id" value="<?php echo $target_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="representative_id">مندوب المبيعات:</label>
        <select id="representative_id" name="representative_id" required <?php echo $action == 'edit' ? 'disabled' : ''; ?>>
            <option value="">-- اختر المندوب --</option>
            <?php foreach ($representatives as $rep): ?>
                <option value="<?php echo $rep['user_id']; ?>" <?php echo (isset($target_data['representative_id']) && $target_data['representative_id'] == $rep['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rep['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($action == 'edit' && isset($target_data['representative_id'])): ?>
            <input type="hidden" name="representative_id" value="<?php echo $target_data['representative_id']; ?>">
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="target_amount">مبلغ الهدف (نقدي):</label>
        <input type="number" step="0.01" id="target_amount" name="target_amount" value="<?php echo htmlspecialchars($target_data['target_amount'] ?? '0.00'); ?>" required min="0">
    </div>
    
    <p>
        <strong>الفترة:</strong> 
        <?php echo htmlspecialchars($months_array[$target_data['month'] ?? $selected_month] ?? ''); ?>
        / 
        <?php echo htmlspecialchars($target_data['year'] ?? $selected_year); ?>
    </p>

    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة الهدف' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=sales_targets&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month; ?>" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>