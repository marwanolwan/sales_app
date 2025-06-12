<?php // views/users/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<a href="index.php?page=users&action=add" class="button-link add-btn">إضافة مستخدم جديد</a>

<table>
    <thead>
        <tr>
            <th>الرقم</th>
            <th>اسم المستخدم</th>
            <th>الاسم الكامل</th>
            <th>الدور</th>
            <th>المنطقة (للمشرف)</th>
            <th>المشرف (للعضو)</th>
            <th>الحالة</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
            <td><?php echo htmlspecialchars($roles_translation[$user['role']] ?? $user['role']); ?></td>
            <td><?php echo htmlspecialchars($user['region_name'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($user['supervisor_name'] ?? 'N/A'); ?></td>
            <td><?php echo $user['is_active'] ? 'نشط' : 'غير نشط'; ?></td>
            <td>
                <a href="index.php?page=users&action=edit&id=<?php echo $user['user_id']; ?>" class="button-link edit-btn">تعديل</a>
                
                <?php if ($user['user_id'] != 1 && $user['user_id'] != $_SESSION['user_id']): ?>
                <form action="actions/user_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المستخدم؟');">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn">حذف</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr><td colspan="8">لا يوجد مستخدمون لعرضهم.</td></tr>
        <?php endif; ?>
    </tbody>
</table>