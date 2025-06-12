<?php // views/monthly_sales/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/monthly_sales_save.php" method="POST">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $sale_id): ?>
        <input type="hidden" name="sale_id" value="<?php echo $sale_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="representative_id">مندوب المبيعات:</label>
        <select id="representative_id" name="representative_id" required <?php echo $action == 'edit' ? 'disabled' : ''; ?>>
            <option value="">-- اختر المندوب --</option>
            <?php foreach ($representatives as $rep): ?>
                <option value="<?php echo $rep['user_id']; ?>" <?php echo (isset($sale_data['representative_id']) && $sale_data['representative_id'] == $rep['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rep['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($action == 'edit' && isset($sale_data['representative_id'])): ?>
            <input type="hidden" name="representative_id" value="<?php echo $sale_data['representative_id']; ?>">
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="year">السنة:</label>
        <select id="year" name="year" required <?php echo $action == 'edit' ? 'disabled' : ''; ?>>
            <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo (isset($sale_data['year']) ? $sale_data['year'] : $selected_year) == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
         <?php if ($action == 'edit'): ?><input type="hidden" name="year" value="<?php echo $sale_data['year']; ?>"><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="month">الشهر:</label>
        <select id="month" name="month" required <?php echo $action == 'edit' ? 'disabled' : ''; ?>>
            <?php foreach ($months_array as $num => $name): ?>
                <option value="<?php echo $num; ?>" <?php echo (isset($sale_data['month']) ? $sale_data['month'] : $selected_month) == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($action == 'edit'): ?><input type="hidden" name="month" value="<?php echo $sale_data['month']; ?>"><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="net_sales_amount">صافي مبلغ المبيعات (نقدي):</label>
        <input type="number" step="0.01" id="net_sales_amount" name="net_sales_amount" value="<?php echo htmlspecialchars($sale_data['net_sales_amount'] ?? '0.00'); ?>" required min="0">
    </div>
    <div class="form-group">
        <label for="notes">ملاحظات (اختياري):</label>
        <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($sale_data['notes'] ?? ''); ?></textarea>
    </div>
    
    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'تسجيل المبيعات' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=monthly_sales&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month; ?>" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>