<?php // views/permissions_form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<p>قم بتحديد الصلاحيات لكل دور وظيفي. يتم فرض صلاحيات معينة لمدير النظام بشكل تلقائي لضمان استقرار النظام.</p>

<form action="actions/permission_save.php" method="POST">
    <?php csrf_input(); ?>
    <table>
        <thead>
            <tr>
                <th>الميزة / القسم</th>
                <?php foreach ($roles as $role): ?>
                    <th><?php echo htmlspecialchars($roles_translation[$role]); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($features_list as $feature_key => $feature_name): ?>
            <tr>
                <td><?php echo htmlspecialchars($feature_name); ?></td>
                <?php foreach ($roles as $role): ?>
                <td style="text-align: center;">
                    <?php
                    // التحقق من الصلاحية الحالية
                    $is_checked = isset($current_permissions[$role][$feature_key]) && $current_permissions[$role][$feature_key] == 1;
                    
                    // تحديد الحقول التي لا يمكن تعديلها (صلاحيات أساسية للمدير)
                    $is_disabled = false;
                    $forced_admin_permissions = ['manage_permissions', 'view_dashboard_summaries'];
                    if ($role == 'admin' && in_array($feature_key, $forced_admin_permissions)) {
                        $is_checked = true;
                        $is_disabled = true;
                    }
                    ?>
                    <input type="checkbox" 
                           name="permissions[<?php echo htmlspecialchars($role); ?>][<?php echo htmlspecialchars($feature_key); ?>]" 
                           value="1"
                           <?php echo $is_checked ? 'checked' : ''; ?>
                           <?php echo $is_disabled ? 'disabled' : ''; ?>>
                    <?php if($is_disabled): // إذا كان الحقل معطلاً، يجب إرسال قيمته كحقل مخفي لضمان الحفظ الصحيح ?>
                        <input type="hidden" name="permissions[<?php echo htmlspecialchars($role); ?>][<?php echo htmlspecialchars($feature_key); ?>]" value="1">
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($features_list)): ?>
                <tr><td colspan="<?php echo count($roles) + 1; ?>">لا يوجد ميزات معرفة في النظام.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
    <button type="submit" class="button-link">حفظ التغييرات</button>
</form>