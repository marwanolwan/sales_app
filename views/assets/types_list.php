<?php // views/assets/types_list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>هنا يمكنك تعريف الفئات الرئيسية للأصول التي تملكها الشركة، مثل "ثلاجة عرض" أو "ستاند معدني".</p>

<div class="actions-bar">
    <a href="index.php?page=assets&action=add_type" class="button-link add-btn">إضافة نوع جديد</a>
    <a href="index.php?page=assets" class="button-link" style="background-color: #6c757d;">العودة لقائمة الأصول</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم نوع الأصل</th>
                <th>الوصف</th>
                <th>عدد الأصول من هذا النوع</th>
                <th style="width: 180px;">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($asset_types)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        لم يتم تعريف أي أنواع للأصول بعد.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($asset_types as $type): ?>
                    <tr>
                        <td><?php echo $type['type_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($type['type_name']); ?></strong></td>
                        <td><?php echo nl2br(htmlspecialchars($type['description'] ?? '')); ?></td>
                        <td><?php echo $type['asset_count']; ?></td>
                        <td class="actions-cell">
                            <a href="index.php?page=assets&action=edit_type&type_id=<?php echo $type['type_id']; ?>" class="button-link edit-btn">
                                تعديل
                            </a>
                            <?php if ($type['asset_count'] == 0): // السماح بالحذف فقط إذا لم يكن النوع مستخدمًا ?>
                                <form action="actions/asset_type_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟');">
                                    <input type="hidden" name="type_id" value="<?php echo $type['type_id']; ?>">
                                    <?php csrf_input(); ?>
                                    <button type="submit" class="button-link delete-btn">حذف</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="button-link disabled-btn" title="لا يمكن الحذف لأن هذا النوع مستخدم من قبل <?php echo $type['asset_count']; ?> أصل.">حذف</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.actions-bar {
    margin-bottom: 20px;
}
.table-container {
    overflow-x: auto;
}
.actions-cell {
    display: flex;
    gap: 8px;
}
.disabled-btn {
    background-color: #ccc;
    border-color: #ccc;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>