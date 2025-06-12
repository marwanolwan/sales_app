<?php // views/product_families/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/product_family_save.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $family_id): ?>
        <input type="hidden" name="family_id" value="<?php echo $family_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="name">اسم عائلة المنتج (الشركة/العلامة التجارية):</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($family_data['name'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="description">الوصف (اختياري):</label>
        <textarea id="description" name="description"><?php echo htmlspecialchars($family_data['description'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="logo_image">شعار الشركة (اختياري, jpg, jpeg, png, gif, حد أقصى 2MB):</label>
        <input type="file" id="logo_image" name="logo_image" accept="image/jpeg,image/png,image/gif">
        
        <?php if ($action == 'edit' && !empty($family_data['logo_image_path'])): ?>
            <p style="margin-top: 10px;">
                <strong>الشعار الحالي:</strong><br>
                <img src="<?php echo PRODUCT_FAMILIES_LOGO_DIR . htmlspecialchars($family_data['logo_image_path']); ?>" alt="شعار <?php echo htmlspecialchars($family_data['name']);?>" style="max-width: 150px; max-height: 75px; vertical-align: middle; border: 1px solid #ddd; padding: 5px; background: #fff;">
                <input type="hidden" name="current_logo_path" value="<?php echo htmlspecialchars($family_data['logo_image_path']); ?>">
                <label style="margin-left: 15px; display: inline-block; vertical-align: middle;">
                    <input type="checkbox" name="remove_logo" value="1"> إزالة الشعار الحالي
                </label>
            </p>
        <?php endif; ?>
    </div>
     <div class="form-group">
        <label for="is_active">
            <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo (isset($family_data['is_active']) && $family_data['is_active']) || $action == 'add' ? 'checked' : ''; ?>>
            فعال
        </label>
    </div>
    
    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=product_families" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>