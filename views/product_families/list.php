<?php // views/product_families/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<a href="index.php?page=product_families&action=add" class="button-link add-btn">إضافة عائلة منتج جديدة</a>

<table>
    <thead>
        <tr>
            <th>الشعار</th>
            <th>اسم العائلة (الشركة)</th>
            <th>الوصف</th>
            <th>عدد المنتجات</th>
            <th>الحالة</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($product_families as $family): ?>
        <tr style="<?php echo !$family['is_active'] ? 'opacity:0.6; text-decoration:line-through;' : ''; ?>">
            <td>
                <?php if (!empty($family['logo_image_path']) && file_exists(PRODUCT_FAMILIES_LOGO_DIR . $family['logo_image_path'])): ?>
                    <img src="<?php echo PRODUCT_FAMILIES_LOGO_DIR . htmlspecialchars($family['logo_image_path']); ?>" alt="شعار <?php echo htmlspecialchars($family['name']);?>" style="max-width: 80px; max-height: 40px;">
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($family['name']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($family['description'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars($family['product_count']); ?></td>
            <td><?php echo $family['is_active'] ? 'فعال' : 'غير فعال'; ?></td>
            <td>
                <a href="index.php?page=product_families&action=edit&id=<?php echo $family['family_id']; ?>" class="button-link edit-btn">تعديل</a>
                <form action="actions/product_family_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه العائلة؟ سيتم حذف الشعار المرتبط بها أيضًا.');">
                    <input type="hidden" name="family_id" value="<?php echo $family['family_id']; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($product_families)): ?>
            <tr><td colspan="6">لا يوجد عائلات منتجات (شركات) لعرضها.</td></tr>
        <?php endif; ?>
    </tbody>
</table>