<?php // views/users/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/user_save.php" method="POST" id="userForm">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $user_id_to_edit): ?>
        <input type="hidden" name="user_id" value="<?php echo $user_id_to_edit; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label for="username">اسم المستخدم:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="full_name">الاسم الكامل:</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
        <label for="role">الدور:</label>
        <select id="role" name="role" required onchange="toggleConditionalFields()">
            <option value="">اختر الدور...</option>
            <option value="admin" <?php echo (isset($user_data['role']) && $user_data['role'] == 'admin') ? 'selected' : ''; ?>>مدير النظام</option>
            <option value="supervisor" <?php echo (isset($user_data['role']) && $user_data['role'] == 'supervisor') ? 'selected' : ''; ?>>مشرف مبيعات</option>
            <option value="representative" <?php echo (isset($user_data['role']) && $user_data['role'] == 'representative') ? 'selected' : ''; ?>>مندوب مبيعات</option>
            <option value="promoter" <?php echo (isset($user_data['role']) && $user_data['role'] == 'promoter') ? 'selected' : ''; ?>>مروج مبيعات</option>
        </select>
    </div>
    
    <div class="form-group" id="region_field" style="display:none;">
        <label for="region_id">المنطقة (لمشرف المبيعات):</label>
        <select id="region_id" name="region_id">
            <option value="">اختر المنطقة...</option>
            <?php foreach ($regions as $region): ?>
                <option value="<?php echo $region['region_id']; ?>" <?php echo (isset($user_data['region_id']) && $user_data['region_id'] == $region['region_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($region['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group" id="supervisor_field" style="display:none;">
        <label for="supervisor_id">المشرف (للمندوب/المروج):</label>
        <select id="supervisor_id" name="supervisor_id">
            <option value="">اختر المشرف...</option>
            <?php foreach ($supervisors as $supervisor): ?>
                 <?php if (!($action == 'edit' && $user_id_to_edit == $supervisor['user_id'])): ?>
                <option value="<?php echo $supervisor['user_id']; ?>" <?php echo (isset($user_data['supervisor_id']) && $user_data['supervisor_id'] == $supervisor['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($supervisor['full_name']); ?>
                </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="password">كلمة المرور <?php echo $action == 'edit' ? '(اتركها فارغة لعدم التغيير)' : ''; ?>:</label>
        <input type="password" id="password" name="password" <?php echo $action == 'add' ? 'required' : ''; ?>>
    </div>
    <div class="form-group">
        <label for="confirm_password">تأكيد كلمة المرور:</label>
        <input type="password" id="confirm_password" name="confirm_password" <?php echo $action == 'add' ? 'required' : ''; ?>>
    </div>
    
    <?php if (!($action == 'edit' && $user_data['user_id'] == 1)): ?>
    <div class="form-group">
        <label for="is_active">
            <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo (isset($user_data['is_active']) && $user_data['is_active']) || $action == 'add' ? 'checked' : ''; ?>>
            مستخدم نشط
        </label>
    </div>
    <?php else: ?>
        <input type="hidden" name="is_active" value="1">
    <?php endif;?>


    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=users" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>

<script>
    // هذا الكود يمكن نقله إلى ملف js/script.js ليبقى الكود أنظف
    function toggleConditionalFields() {
        var role = document.getElementById('role').value;
        var regionField = document.getElementById('region_field');
        var supervisorField = document.getElementById('supervisor_field');
        var regionSelect = document.getElementById('region_id');
        var supervisorSelect = document.getElementById('supervisor_id');

        regionField.style.display = 'none';
        supervisorField.style.display = 'none';
        regionSelect.required = false;
        supervisorSelect.required = false;

        if (role === 'supervisor') {
            regionField.style.display = 'block';
            regionSelect.required = true;
        } else if (role === 'representative' || role === 'promoter') {
            supervisorField.style.display = 'block';
            supervisorSelect.required = true;
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleConditionalFields();
    });
</script>