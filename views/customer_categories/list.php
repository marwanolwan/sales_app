<?php // views/customer_categories/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=customer_categories&action=add" class="button-link add-btn">إضافة تصنيف جديد</a>
</div>

<table>
    <thead>
        <tr>
            <th>الرقم</th>
            <th>اسم التصنيف</th>
            <th>الوصف</th>
            <th>عدد العملاء</th>
            <th>تاريخ الإنشاء</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
        <tr>
            <td><?php echo htmlspecialchars($category['category_id']); ?></td>
            <td><?php echo htmlspecialchars($category['name']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($category['description'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars($category['customer_count']); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($category['created_at'])); ?></td>
            <td>
                <a href="index.php?page=customer_categories&action=edit&id=<?php echo $category['category_id']; ?>" class="button-link edit-btn">تعديل</a>
                
                <form action="actions/customer_category_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا التصنيف؟');">
                    <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn" <?php if ($category['customer_count'] > 0) echo 'disabled title="لا يمكن الحذف لوجود عملاء مرتبطين"'; ?>>
                        حذف
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
            <tr><td colspan="6">لا يوجد تصنيفات عملاء لعرضها.</td></tr>
        <?php endif; ?>
    </tbody>
</table>