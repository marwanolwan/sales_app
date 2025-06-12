<?php // views/promotions/type_form.php (هذا النموذج سيتم تضمينه في صفحة القائمة) ?>
<div class="card" id="add_form">
    <div class="card-header">
        <h2><?php echo $page_title; ?></h2>
    </div>
    <div class="card-body">
        <form action="actions/promotion_type_save.php" method="POST">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="promo_type_id" value="<?php echo $promo_type_id; ?>">
            <?php endif; ?>
            <?php csrf_input(); ?>
            <div class="form-group">
                <label for="name">اسم نوع الدعاية:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($promo_type_data['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">الوصف (اختياري):</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($promo_type_data['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_annual" value="1" <?php echo (isset($promo_type_data['is_annual']) && $promo_type_data['is_annual']) ? 'checked' : ''; ?>>
                    مخصص للحملات السنوية فقط
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="button-link add-btn">
                    <i class="fas fa-save"></i> <?php echo $action == 'add' ? 'إضافة النوع' : 'حفظ التعديلات'; ?>
                </button>
                <a href="index.php?page=promotion_types" class="button-link" style="background-color:#6c757d;">إلغاء</a>
            </div>
        </form>
    </div>
</div>