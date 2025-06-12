<?php // views/collections/form.php ?>

<h2><?php echo $action == 'add' ? "إضافة تحصيل لشهر {$months_map[$filter_month]} {$filter_year}" : "تعديل سجل التحصيل"; ?></h2>

<form action="actions/collection_save.php" method="POST">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="year" value="<?php echo $collection_data['year'] ?? $filter_year; ?>">
    <input type="hidden" name="month" value="<?php echo $collection_data['month'] ?? $filter_month; ?>">
    <?php if ($action == 'edit'): ?>
        <input type="hidden" name="collection_id" value="<?php echo $collection_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="representative_id">المندوب:</label>
        <select name="representative_id" id="representative_id" required <?php if($action == 'edit') echo 'disabled'; ?>>
            <option value="">-- اختر المندوب --</option>
            <?php foreach($representatives as $rep): ?>
                <option value="<?php echo $rep['user_id']; ?>" <?php echo (isset($collection_data['representative_id']) && $collection_data['representative_id'] == $rep['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rep['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if($action == 'edit'): ?>
            <input type="hidden" name="representative_id" value="<?php echo $collection_data['representative_id']; ?>">
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="collection_amount">المبلغ المحصل:</label>
        <input type="number" step="0.01" name="collection_amount" id="collection_amount" value="<?php echo htmlspecialchars($collection_data['collection_amount'] ?? '0.00'); ?>" required>
    </div>

    <div class="form-group">
        <label for="notes">ملاحظات:</label>
        <textarea name="notes" id="notes" rows="4"><?php echo htmlspecialchars($collection_data['notes'] ?? ''); ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="button-link add-btn"><?php echo $action == 'add' ? 'حفظ' : 'تحديث'; ?></button>
        <a href="index.php?page=collections&year=<?php echo $filter_year; ?>&month=<?php echo $filter_month; ?>" class="button-link" style="background-color: #6c757d;">إلغاء</a>
    </div>
</form>