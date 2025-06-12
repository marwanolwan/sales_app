<?php // views/assets/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/asset_save.php" method="POST" class="asset-form">
    <?php csrf_input(); ?>
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit' && !empty($asset_id)): ?>
        <input type="hidden" name="asset_id" value="<?php echo $asset_id; ?>">
    <?php endif; ?>

    <fieldset>
        <legend>المعلومات الأساسية للأصل</legend>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="serial_number">الرقم التسلسلي (فريد):</label>
                <input type="text" name="serial_number" id="serial_number" value="<?php echo htmlspecialchars($asset_data['serial_number'] ?? ''); ?>" placeholder="e.g., FRDG-2025-001" required>
            </div>
            <div class="form-group col-md-6">
                <label for="type_id">نوع الأصل:</label>
                <select name="type_id" id="type_id" class="form-control" required>
                    <option value="">-- اختر النوع --</option>
                    <?php foreach($asset_types as $type): ?>
                        <option value="<?php echo $type['type_id']; ?>" <?php echo (isset($asset_data['type_id']) && $asset_data['type_id'] == $type['type_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="description">وصف إضافي أو ملاحظات:</label>
            <textarea name="description" id="description" rows="3" placeholder="مثال: موديل الثلاجة، تاريخ الشراء، إلخ."><?php echo htmlspecialchars($asset_data['description'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>الحالة والموقع الحالي</legend>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="status">الحالة:</label>
                <select name="status" id="status" class="form-control" required onchange="toggleCustomerField()">
                    <?php foreach($statuses as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($asset_data['status']) && $asset_data['status'] == $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- هذا الحقل سيظهر ويختفي بناءً على الحالة -->
            <div class="form-group col-md-4" id="customer-field" style="display:none;">
                <label for="customer_id">العميل:</label>
                <select name="customer_id" id="customer_id" class="select2-enable">
                    <option value="">-- اختر أو ابحث عن عميل --</option>
                    <?php foreach($customers as $customer): ?>
                         <option value="<?php echo $customer['customer_id']; ?>" <?php echo (isset($asset_data['customer_id']) && $asset_data['customer_id'] == $customer['customer_id']) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($customer['name'] . ' (' . $customer['customer_code'] . ')'); ?>
                         </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- هذا الحقل سيظهر ويختفي بناءً على الحالة -->
            <div class="form-group col-md-4" id="date-field" style="display:none;">
                <label for="deployed_date">تاريخ الوضع لدى العميل:</label>
                <input type="date" name="deployed_date" id="deployed_date" class="form-control" value="<?php echo htmlspecialchars($asset_data['deployed_date'] ?? ''); ?>">
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="button-link add-btn">
            <?php echo $action == 'add' ? 'إضافة الأصل' : 'حفظ التغييرات'; ?>
        </button>
        <a href="index.php?page=assets" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </div>
</form>

<script>
function toggleCustomerField() {
    const statusSelect = document.getElementById('status');
    const customerField = document.getElementById('customer-field');
    const dateField = document.getElementById('date-field');
    const customerSelect = document.getElementById('customer_id');

    if (statusSelect.value === 'With Customer') {
        customerField.style.display = 'block';
        dateField.style.display = 'block';
        customerSelect.required = true; // جعل اختيار العميل إلزاميًا في هذه الحالة
    } else {
        customerField.style.display = 'none';
        dateField.style.display = 'none';
        customerSelect.required = false;
        customerSelect.value = ''; // تفريغ القيمة عند الإخفاء
        document.getElementById('deployed_date').value = ''; // تفريغ التاريخ
    }
}

// تأكد من أن jQuery و Select2 متاحان من layout.php
$(document).ready(function() {
    $('.select2-enable').select2({ 
        width: '100%', 
        dir: 'rtl',
        placeholder: '-- اختر أو ابحث عن عميل --'
    });
    
    // استدعاء الدالة عند تحميل الصفحة للتأكد من الحالة الصحيحة للحقول
    toggleCustomerField(); 
});
</script>

<style>
/* CSS مخصص للنموذج */
.asset-form fieldset {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    background-color: #fdfdfd;
}
.asset-form legend {
    font-weight: bold;
    font-size: 1.1em;
    color: var(--primary-color);
    padding: 0 10px;
}
.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.form-group {
    flex-grow: 1;
}
.form-group.col-md-4 {
    flex-basis: calc(33.333% - 14px); /* (100% / 3) - جزء من الـ gap */
}
.form-group.col-md-6 {
    flex-basis: calc(50% - 10px); /* (100% / 2) - جزء من الـ gap */
}
@media (max-width: 768px) {
    .form-group.col-md-4, .form-group.col-md-6 {
        flex-basis: 100%;
    }
}
.form-actions {
    text-align: left;
    margin-top: 20px;
}
/* لجعل Select2 يعمل بشكل جيد مع التنسيقات */
.select2-container {
    width: 100% !important;
}
</style>