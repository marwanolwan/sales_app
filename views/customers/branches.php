<?php // views/customers/branches.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=customers&action=add&main_id_for_branch=<?php echo $customer_id; ?>" class="button-link add-btn">إضافة فرع جديد لهذا الحساب</a>
    <a href="index.php?page=customers" class="button-link" style="background-color:#6c757d;">العودة لقائمة العملاء</a>
</div>

<?php if (empty($branches)): ?>
    <p class="info-message">لا توجد فروع مرتبطة بهذا الحساب الرئيسي حاليًا.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>رمز الفرع</th>
                <th>اسم الفرع</th>
                <th>النوع</th>
                <th>المندوب</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($branches as $branch): ?>
            <tr>
                <td><?php echo htmlspecialchars($branch['customer_code']); ?></td>
                <td><?php echo htmlspecialchars($branch['name']); ?></td>
                <td><?php echo htmlspecialchars($branch['category_name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($branch['representative_name'] ?? '-'); ?></td>
                <td><span class="badge <?php echo $branch['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>"><?php echo $branch['status'] == 'active' ? 'نشط' : 'غير نشط'; ?></span></td>
                <td>
                    <a href="index.php?page=customers&action=edit&id=<?php echo $branch['customer_id']; ?>" class="button-link edit-btn">تعديل</a>
                    <form action="actions/customer_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                        <input type="hidden" name="customer_id" value="<?php echo $branch['customer_id']; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link delete-btn">حذف</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>