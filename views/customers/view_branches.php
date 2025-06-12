<?php // views/customers/view_branches.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="toolbar">
    <div class="toolbar-actions">
        <a href="index.php?page=customers&action=add&main_id_for_branch=<?php echo $main_customer_data['customer_id']; ?>" class="button-link add-btn">إضافة فرع جديد لهذا الحساب</a>
        <a href="index.php?page=customers" class="button-link" style="background-color:#6c757d;">العودة لقائمة العملاء</a>
    </div>
</div>

<div class="content-block">
    <h4>الفروع المرتبطة بالحساب الرئيسي: <?php echo htmlspecialchars($main_customer_data['name']); ?> (<?php echo htmlspecialchars($main_customer_data['customer_code']); ?>)</h4>
    
    <?php if (empty($branches)): ?>
        <p class="info-message">لا توجد فروع مرتبطة بهذا الحساب الرئيسي حاليًا.</p>
    <?php else: ?>
        <div class="table-responsive">
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
                        <td><?php echo htmlspecialchars($branch['category_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($branch['representative_name'] ?? 'N/A'); ?></td>
                        <td><?php echo $branch['status'] == 'active' ? 'نشط' : 'غير نشط'; ?></td>
                        <td>
                            <a href="index.php?page=customers&action=edit&id=<?php echo $branch['customer_id']; ?>" class="button-link edit-btn">تعديل الفرع</a>
                            <!-- يمكنك إضافة نموذج الحذف هنا -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>