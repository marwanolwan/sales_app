<?php // views/customers/form.php

// تحديد عنوان النموذج بناءً على الإجراء
$form_title = $action == 'add' ? 'إضافة عميل جديد' : 'تعديل بيانات العميل: ' . htmlspecialchars($customer_data['name'] ?? '');
if ($action == 'add' && isset($_GET['main_account'])) {
    $form_title = 'إضافة حساب عميل رئيسي (مجمع)';
} elseif ($action == 'add' && isset($_GET['main_id_for_branch'])) {
    $main_acc_for_branch_id = (int)$_GET['main_id_for_branch'];
    // جلب اسم الحساب الرئيسي لعرضه في العنوان
    $stmt_main_name = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
    $stmt_main_name->execute([$main_acc_for_branch_id]);
    $main_acc_name = $stmt_main_name->fetchColumn();
    if ($main_acc_name) {
        $form_title = 'إضافة فرع جديد للحساب: ' . htmlspecialchars($main_acc_name);
    }
}
?>

<h2><?php echo $form_title; ?></h2>

<form action="actions/customer_save.php" method="POST" id="customerForm">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && $customer_id): ?>
        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <div class="form-group">
        <label>
            <input type="checkbox" id="is_main_account" name="is_main_account" value="1" 
                   <?php echo (isset($customer_data['is_main_account']) && $customer_data['is_main_account']) || ($action == 'add' && isset($_GET['main_account'])) ? 'checked' : ''; ?>
                   onchange="toggleMainAccountFields()">
            هل هذا حساب عميل رئيسي (مجمع)؟
        </label>
    </div>

    <div class="form-group" id="customer_code_group">
        <label for="customer_code">رمز العميل:</label>
        <input type="text" id="customer_code" name="customer_code" value="<?php echo htmlspecialchars($customer_data['customer_code'] ?? ''); ?>">
        <small id="customer_code_note" style="display:none; color: #666;">سيتم إنشاء رمز تلقائي للحساب الرئيسي عند الحفظ.</small>
    </div>
    
    <div class="form-group">
        <label for="name">اسم العميل/الفرع:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer_data['name'] ?? ''); ?>" required>
    </div>

    <div class="form-group" id="main_account_id_group">
        <label for="main_account_id">ربط بحساب رئيسي (إذا كان فرعًا):</label>
        <select id="main_account_id" name="main_account_id">
            <option value="">-- لا يوجد --</option>
            <?php 
            $pre_selected_main_id = $customer_data['main_account_id'] ?? ($_GET['main_id_for_branch'] ?? null);
            foreach ($main_accounts as $main_acc): 
                // منع الحساب من أن يكون فرعًا لنفسه
                if ($action == 'edit' && $customer_id == $main_acc['customer_id']) continue;
            ?>
                <option value="<?php echo $main_acc['customer_id']; ?>" 
                        <?php echo ($pre_selected_main_id == $main_acc['customer_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($main_acc['name']) . " (" . htmlspecialchars($main_acc['customer_code']) . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="category_id">نوع العميل:</label>
        <select id="category_id" name="category_id">
            <option value="">اختر النوع...</option>
            <?php foreach ($customer_categories as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($customer_data['category_id']) && $customer_data['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="address">العنوان:</label>
        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($customer_data['address'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label for="representative_id">مندوب المبيعات:</label>
        <select id="representative_id" name="representative_id">
            <option value="">اختر المندوب...</option>
            <?php foreach ($representatives as $rep): ?>
                <option value="<?php echo $rep['user_id']; ?>" <?php echo (isset($customer_data['representative_id']) && $customer_data['representative_id'] == $rep['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rep['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="promoter_id">مروج المبيعات:</label>
        <select id="promoter_id" name="promoter_id">
            <option value="">اختر المروج...</option>
            <?php foreach ($promoters as $promo): ?>
                <option value="<?php echo $promo['user_id']; ?>" <?php echo (isset($customer_data['promoter_id']) && $customer_data['promoter_id'] == $promo['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($promo['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="display: flex; gap: 1rem;">
        <div class="form-group" style="flex:1;">
            <label for="latitude">خط العرض:</label>
            <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($customer_data['latitude'] ?? ''); ?>">
        </div>
        <div class="form-group" style="flex:1;">
            <label for="longitude">خط الطول:</label>
            <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($customer_data['longitude'] ?? ''); ?>">
        </div>
    </div>
    
    <div class="form-group">
        <label for="opening_date">تاريخ فتح الملف:</label>
        <input type="date" id="opening_date" name="opening_date" value="<?php echo htmlspecialchars($customer_data['opening_date'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="status">حالة العميل:</label>
        <select id="status" name="status">
            <option value="active" <?php echo (!isset($customer_data['status']) || $customer_data['status'] == 'active') ? 'selected' : ''; ?>>نشط</option>
            <option value="inactive" <?php echo (isset($customer_data['status']) && $customer_data['status'] == 'inactive') ? 'selected' : ''; ?>>غير نشط</option>
        </select>
    </div>

    <button type="submit" class="button-link"><?php echo $action == 'add' ? 'إضافة' : 'حفظ التعديلات'; ?></button>
    <a href="index.php?page=customers" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</form>

<script>
    function toggleMainAccountFields() {
        const isMainCheckbox = document.getElementById('is_main_account');
        const customerCodeInput = document.getElementById('customer_code');
        const customerCodeNote = document.getElementById('customer_code_note');
        const mainAccountIdGroup = document.getElementById('main_account_id_group');
        const mainAccountSelect = document.getElementById('main_account_id');

        if (isMainCheckbox.checked) {
            customerCodeInput.readOnly = true;
            customerCodeInput.value = '(سيتم إنشاؤه تلقائياً)';
            customerCodeNote.style.display = 'block';
            mainAccountIdGroup.style.display = 'none';
            mainAccountSelect.value = '';
        } else {
            customerCodeInput.readOnly = false;
            if (customerCodeInput.value === '(سيتم إنشاؤه تلقائياً)') {
                customerCodeInput.value = '<?php echo htmlspecialchars($customer_data['customer_code'] ?? ''); ?>';
            }
            customerCodeNote.style.display = 'none';
            mainAccountIdGroup.style.display = 'block';
        }
    }
    // استدعاء الدالة عند تحميل الصفحة للتأكد من الحالة الصحيحة
    document.addEventListener('DOMContentLoaded', toggleMainAccountFields);
</script>