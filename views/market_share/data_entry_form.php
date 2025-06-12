<?php // views/market_share/data_entry_form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form id="periodSelectorForm" action="index.php" method="GET">
    <input type="hidden" name="page" value="market_share">
    <input type="hidden" name="action" value="data_entry">
    <fieldset>
        <legend>1. اختر العميل وفترة التقرير</legend>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="customer_id_entry">العميل:</label>
                <select name="customer_id" id="customer_id_entry" class="select2-enable" required>
                    <option value="">-- اختر أو ابحث عن عميل --</option>
                    <?php foreach ($customers_list as $customer): ?>
                        <option value="<?php echo $customer['customer_id']; ?>" <?php echo ($customer_id_entry == $customer['customer_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['name'] . ' (' . $customer['customer_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="report_period_entry">فترة التقرير (شهر):</label>
                <input type="month" id="report_period_entry" name="report_period" value="<?php echo htmlspecialchars($report_period_entry); ?>" required>
            </div>
        </div>
        <button type="submit" class="button-link">تحميل / عرض بيانات الفترة</button>
    </fieldset>
</form>

<?php if ($customer_id_entry): ?>
<form action="actions/market_share_save.php" method="POST">
    <?php csrf_input(); ?>
    <input type="hidden" name="customer_id" value="<?php echo $customer_id_entry; ?>">
    <input type="hidden" name="report_period" value="<?php echo $report_period_entry; ?>">

    <fieldset>
        <legend>2. أدخل كميات المبيعات</legend>
        <div id="entries-container">
            <!-- سيتم إضافة الحقول هنا ديناميكياً -->
        </div>
        <div style="margin-top: 15px;">
            <button type="button" id="add-our-product-btn" class="button-link" style="background-color: var(--primary-color);">+ إضافة منتج لنا</button>
            <button type="button" id="add-competitor-btn" class="button-link" style="background-color: var(--info-color);">+ إضافة منتج منافس</button>
        </div>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit" class="button-link add-btn">حفظ البيانات</button>
    </div>
</form>
<?php endif; ?>

<!-- قوالب JavaScript لإنشاء الحقول ديناميكياً -->
<template id="our-product-template">
    <div class="form-row entry-row">
        <input type="hidden" name="entry[new_{index}][is_our_product]" value="1">
        <div class="form-group col-md-6">
            <label>منتجنا:</label>
            <select name="entry[new_{index}][product_id_internal]" class="select2-product" required>
                <option value="">-- اختر منتج --</option>
                <?php foreach($our_products as $p): ?>
                    <option value="<?php echo $p['product_id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>"><?php echo htmlspecialchars($p['name'] . ' (' . $p['product_code'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="entry[new_{index}][product_name]" class="product-name-hidden">
        </div>
        <div class="form-group col-md-5"><label>الكمية المباعة:</label><input type="number" step="0.01" name="entry[new_{index}][quantity]" required></div>
        <div class="form-group col-md-1"><label> </label><button type="button" class="remove-btn" onclick="this.closest('.entry-row').remove()">X</button></div>
    </div>
</template>

<template id="competitor-product-template">
    <div class="form-row entry-row">
        <input type="hidden" name="entry[new_{index}][is_our_product]" value="0">
        <div class="form-group col-md-6"><label>منتج منافس:</label><input type="text" name="entry[new_{index}][product_name]" required></div>
        <div class="form-group col-md-5"><label>الكمية المباعة:</label><input type="number" step="0.01" name="entry[new_{index}][quantity]" required></div>
        <div class="form-group col-md-1"><label> </label><button type="button" class="remove-btn" onclick="this.closest('.entry-row').remove()">X</button></div>
    </div>
</template>

<script>
$(document).ready(function() {
    $('.select2-enable').select2({
        placeholder: "-- اختر أو ابحث --",
        dir: "rtl",
        width: '100%'
    });

    function initializeProductSelect(element) {
        $(element).select2({
            placeholder: "-- اختر منتج --",
            dir: "rtl",
            width: '100%'
        }).on('change', function() {
            // تحديث الحقل المخفي باسم المنتج عند الاختيار
            const selectedOption = $(this).find('option:selected');
            const productName = selectedOption.data('name');
            $(this).closest('.entry-row').find('.product-name-hidden').val(productName);
        });
    }
    
    let entryIndex = 0;
    const container = $('#entries-container');

    $('#add-our-product-btn').on('click', function() {
        const template = $('#our-product-template').html().replace(/{index}/g, entryIndex++);
        container.append(template);
        initializeProductSelect(container.find('.select2-product:last'));
    });

    $('#add-competitor-btn').on('click', function() {
        const template = $('#competitor-product-template').html().replace(/{index}/g, entryIndex++);
        container.append(template);
    });

    // ملء النموذج بالبيانات الموجودة عند تحميل الصفحة
    const existingEntries = <?php echo json_encode($existing_entries); ?>;
    existingEntries.forEach(entry => {
        if (parseInt(entry.is_our_product) === 1) {
            $('#add-our-product-btn').click();
            const newRow = container.find('.entry-row:last');
            newRow.find('select[name*="[product_id_internal]"]').val(entry.product_id_internal).trigger('change');
            newRow.find('input[name*="[quantity]"]').val(entry.quantity_sold);
        } else {
            $('#add-competitor-btn').click();
            const newRow = container.find('.entry-row:last');
            newRow.find('input[name*="[product_name]"]').val(entry.product_name);
            newRow.find('input[name*="[quantity]"]').val(entry.quantity_sold);
        }
    });
});
</script>

<style>
.remove-btn { background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; }
.entry-row { align-items: flex-end; }
</style>