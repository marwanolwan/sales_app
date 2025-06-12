<?php // views/regions/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<a href="index.php?page=regions&action=add" class="button-link add-btn">إضافة منطقة جديدة</a>

<table>
    <thead>
        <tr>
            <th>الرقم</th>
            <th>اسم المنطقة</th>
            <th>الوصف</th>
            <th>عدد المشرفين</th>
            <th>تاريخ الإنشاء</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($regions as $region): ?>
        <tr>
            <td><?php echo htmlspecialchars($region['region_id']); ?></td>
            <td><?php echo htmlspecialchars($region['name']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($region['description'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars($region['supervisor_count']); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($region['created_at'])); ?></td>
            <td>
                <a href="index.php?page=regions&action=edit&id=<?php echo $region['region_id']; ?>" class="button-link edit-btn">تعديل</a>
                <form action="actions/region_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد؟');">
                    <input type="hidden" name="region_id" value="<?php echo $region['region_id']; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($regions)): ?>
            <tr><td colspan="6">لا يوجد مناطق لعرضها.</td></tr>
        <?php endif; ?>
    </tbody>
</table>