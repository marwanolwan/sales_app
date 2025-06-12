<?php // views/customer_categories/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/customer_category_save.php" method="POST">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $category_id): ?>
        <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="name">اسم التصنيف:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category_data['name'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="description">الوصف (اختياري):</label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($category_data['description'] ?? ''); ?></textarea>
    </div>
    
    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة التصنيف' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=customer_categories" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>