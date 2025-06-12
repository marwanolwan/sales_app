<?php // views/regions/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/region_save.php" method="POST">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $region_id): ?>
        <input type="hidden" name="region_id" value="<?php echo $region_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="name">اسم المنطقة:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($region_data['name'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="description">الوصف (اختياري):</label>
        <textarea id="description" name="description"><?php echo htmlspecialchars($region_data['description'] ?? ''); ?></textarea>
    </div>
    
    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=regions" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>