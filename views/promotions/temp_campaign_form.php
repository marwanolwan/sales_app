<?php // views/promotions/temp_campaign_form.php ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($page_title); ?></h2>
    </div>
    <div class="card-body">
        <form action="actions/temp_campaign_save.php" method="POST">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            <?php endif; ?>
            <?php csrf_input(); ?>

            <div class="form-group">
                <label for="promo_type_id">نوع الدعاية:</label>
                <select id="promo_type_id" name="promo_type_id" required>
                    <option value="">-- اختر النوع --</option>
                    <?php foreach ($temp_promo_types as $type): ?>
                    <option value="<?php echo $type['promo_type_id']; ?>" <?php echo (isset($campaign_data['promo_type_id']) && $campaign_data['promo_type_id'] == $type['promo_type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">وصف الحملة (مثال: حملة تذوق جرين كولا):</label>
                <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($campaign_data['description'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">تاريخ البدء:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($campaign_data['start_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">تاريخ الانتهاء:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($campaign_data['end_date'] ?? ''); ?>">
                </div>
            </div>
             <div class="form-row">
                <div class="form-group">
                    <label for="value">القيمة:</label>
                    <input type="number" step="0.01" id="value" name="value" value="<?php echo htmlspecialchars($campaign_data['value'] ?? '0.00'); ?>">
                </div>
                <div class="form-group">
                    <label for="status">الحالة:</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo (isset($campaign_data['status']) && $campaign_data['status'] == 'active') ? 'selected' : ''; ?>>نشطة</option>
                        <option value="inactive" <?php echo (isset($campaign_data['status']) && $campaign_data['status'] == 'inactive') ? 'selected' : ''; ?>>غير نشطة</option>
                        <option value="completed" <?php echo (isset($campaign_data['status']) && $campaign_data['status'] == 'completed') ? 'selected' : ''; ?>>مكتملة</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="notes">ملاحظات:</label>
                <textarea id="notes" name="notes"><?php echo htmlspecialchars($campaign_data['notes'] ?? ''); ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="button-link add-btn"><?php echo $action == 'add' ? 'إضافة الحملة' : 'حفظ التعديلات'; ?></button>
                <a href="index.php?page=temp_campaigns&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color:#6c757d;">إلغاء</a>
            </div>
        </form>
    </div>
</div>