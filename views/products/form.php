<?php // views/products/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/product_save.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $product_id): ?>
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="product_code">رمز المنتج:</label>
        <input type="text" id="product_code" name="product_code" value="<?php echo htmlspecialchars($product_data['product_code'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="name">اسم المنتج:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product_data['name'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="family_id">عائلة المنتج (الشركة):</label>
        <select id="family_id" name="family_id">
            <option value="">-- اختر --</option>
            <?php foreach ($product_families_list as $fam): ?>
                <option value="<?php echo $fam['family_id']; ?>" <?php echo (isset($product_data['family_id']) && $product_data['family_id'] == $fam['family_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($fam['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="unit">وحدة البيع (قطعة, كرتونة, الخ):</label>
        <input type="text" id="unit" name="unit" value="<?php echo htmlspecialchars($product_data['unit'] ?? ''); ?>" required placeholder="مثال: كرتونة">
    </div>
    <div class="form-group">
        <label for="packaging_details">تفاصيل التعبئة (اختياري):</label>
        <input type="text" id="packaging_details" name="packaging_details" value="<?php echo htmlspecialchars($product_data['packaging_details'] ?? ''); ?>" placeholder="مثال: 6 قطع/كرتونة">
    </div>
    <div class="form-group">
        <label for="product_image">صورة المنتج (اختياري, jpg, png, gif):</label>
        <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/gif">
        <?php if ($action == 'edit' && !empty($product_data['product_image_path'])): ?>
            <p style="margin-top: 5px;">
                الصورة الحالية: <img src="<?php echo PRODUCTS_IMAGE_DIR . htmlspecialchars($product_data['product_image_path']); ?>" alt="صورة المنتج" style="max-width: 100px; vertical-align: middle;">
                <input type="hidden" name="current_image_path" value="<?php echo htmlspecialchars($product_data['product_image_path']); ?>">
                <label style="margin-left: 10px;"><input type="checkbox" name="remove_image" value="1"> إزالة الصورة</label>
            </p>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="is_active_prod">
            <input type="checkbox" id="is_active_prod" name="is_active" value="1" <?php echo (isset($product_data['is_active']) && $product_data['is_active']) || $action == 'add' ? 'checked' : ''; ?>>
            منتج فعال
        </label>
    </div>
<div class="form-group">
        <label for="is_new_product">
            <input type="checkbox" id="is_new_product" name="is_new_product" value="1" 
                   <?php echo (isset($product_data['is_new_product']) && $product_data['is_new_product']) ? 'checked' : ''; ?>
                   onchange="toggleNewProductDate()">
            تمييز كـ "منتج جديد"
        </label>
    </div>
    
    <div class="form-group" id="new_product_date_field" style="display: <?php echo (isset($product_data['is_new_product']) && $product_data['is_new_product']) ? 'block' : 'none'; ?>;">
        <label for="new_product_end_date">تاريخ انتهاء فترة "المنتج الجديد" (اختياري):</label>
        <input type="date" id="new_product_end_date" name="new_product_end_date" 
               value="<?php echo htmlspecialchars($product_data['new_product_end_date'] ?? ''); ?>">
        <small>بعد هذا التاريخ، لن يعتبر المنتج جديدًا تلقائيًا في التقارير.</small>
    </div>
    <!-- =====| نهاية الإضافة |===== -->
    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=products" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>
<script>
function toggleNewProductDate() {
    const checkbox = document.getElementById('is_new_product');
    const dateField = document.getElementById('new_product_date_field');
    dateField.style.display = checkbox.checked ? 'block' : 'none';
}
// تأكد من أن jQuery متاح من layout.php
$(document).ready(function() {
    toggleNewProductDate(); // استدعاء الدالة عند تحميل الصفحة
});
</script>