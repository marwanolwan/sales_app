<?php // views/posm/stock_form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>استخدم هذا النموذج لتسجيل جميع حركات مخزون المواد الترويجية، سواء كانت إضافة كميات جديدة للمخزن أو توزيعها.</p>

<div class="form-container">
    <form action="actions/posm_stock_save.php" method="POST" id="stock-form">
        <?php csrf_input(); ?>

        <fieldset>
            <legend>تفاصيل حركة المخزون</legend>
            
            <div class="form-group">
                <label for="movement_type">نوع الحركة:</label>
                <select name="movement_type" id="movement_type" class="form-control" required>
                    <option value="">-- اختر نوع الحركة --</option>
                    <option value="Stock In">إدخال للمخزن (إضافة رصيد)</option>
                    <option value="Dispatch to Rep">صرف للمروج (من المخزن الرئيسي)</option>
                    <option value="Deliver to Customer">تسليم لعميل (من عهدة المروج)</option>
                </select>
            </div>

            <div id="dynamic-fields-container">
                <!-- الحقول الديناميكية ستظهر هنا -->

                <!-- حقل المادة (مشترك بين جميع الحركات) -->
                <div class="form-group common-field">
                    <label for="item_id">المادة الترويجية:</label>
                    <select name="item_id" id="item_id" class="select2-enable" required>
                        <option value="">-- اختر أو ابحث عن مادة --</option>
                        <?php foreach($posm_items as $item): ?>
                            <option value="<?php echo $item['item_id']; ?>">
                                <?php echo htmlspecialchars($item['item_name'] . ' (' . ($item['item_code'] ?? 'N/A') . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- حقل المندوب (يظهر عند الصرف والتسليم) -->
                 <div class="form-group" data-movement-type="Dispatch to Rep,Deliver to Customer" style="display: none;">
                    <label for="user_id">المروج:</label>
                    <select name="user_id" id="user_id" class="select2-enable">
                        <option value="">-- اختر المروج --</option>
                        <?php foreach($promoters as $promoter): ?>
                            <option value="<?php echo $promoter['user_id']; ?>"><?php echo htmlspecialchars($promoter['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- حقل العميل (يظهر عند التسليم فقط) -->
                <div class="form-group" data-movement-type="Deliver to Customer" style="display: none;">
                    <label for="customer_id">العميل:</label>
                    <select name="customer_id" id="customer_id" class="select2-enable">
                        <option value="">-- اختر العميل --</option>
                        <?php foreach($customers as $customer): ?>
                             <option value="<?php echo $customer['customer_id']; ?>"><?php echo htmlspecialchars($customer['name'] . ' (' . $customer['customer_code'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- حقل الكمية (مشترك بين جميع الحركات) -->
                <div class="form-group common-field">
                    <label for="quantity">الكمية:</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                </div>

                <!-- حقل الملاحظات (مشترك بين جميع الحركات) -->
                <div class="form-group common-field">
                    <label for="notes">ملاحظات (اختياري):</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
                </div>

            </div>

        </fieldset>

        <div class="form-actions">
            <button type="submit" class="button-link add-btn">
                تنفيذ الحركة
            </button>
            <a href="index.php?page=posm" class="button-link" style="background-color:#6c757d;">
                إلغاء
            </a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // تفعيل Select2
    $('.select2-enable').select2({
        placeholder: "-- اختر --",
        dir: "rtl",
        width: '100%'
    });

    const movementTypeSelect = $('#movement_type');
    const dynamicFieldsContainer = $('#dynamic-fields-container');

    function toggleDynamicFields() {
        const selectedType = movementTypeSelect.val();
        
        // إخفاء جميع الحقول الديناميكية أولاً
        dynamicFieldsContainer.find('.form-group:not(.common-field)').hide();
        dynamicFieldsContainer.find('select:not(#item_id), input:not(#quantity)').prop('required', false);

        if (!selectedType) {
            // إذا لم يتم اختيار نوع، أخفِ كل شيء ما عدا القائمة نفسها
            dynamicFieldsContainer.find('.form-group').hide();
            return;
        }

        // إظهار الحقول المشتركة
        dynamicFieldsContainer.find('.common-field').show();
        dynamicFieldsContainer.find('#item_id, #quantity').prop('required', true);

        // إظهار الحقول بناءً على النوع المختار
        dynamicFieldsContainer.find('.form-group').each(function() {
            const types = $(this).data('movement-type');
            if (types && types.split(',').includes(selectedType)) {
                $(this).show();
                // جعل الحقل إلزاميًا إذا كان ظاهرًا
                $(this).find('select, input').prop('required', true);
            }
        });
    }

    // استدعاء الدالة عند تغيير نوع الحركة
    movementTypeSelect.on('change', toggleDynamicFields);

    // استدعاء الدالة عند تحميل الصفحة لإعداد الحالة الأولية
    toggleDynamicFields();
});
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 25px;
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}
.form-container fieldset {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 25px;
    border-radius: 8px;
}
.form-container legend {
    font-weight: bold;
    font-size: 1.1em;
    color: var(--primary-color);
    padding: 0 10px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}
.form-actions {
    text-align: left;
    margin-top: 25px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
/* لجعل Select2 يعمل بشكل جيد */
.select2-container {
    width: 100% !important;
}
</style>