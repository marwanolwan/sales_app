<?php // views/market_share/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/market_share_save.php" method="POST">
    <?php csrf_input(); ?>
    <fieldset>
        <legend>تحديد فترة التقرير</legend>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="report_period">فترة التقرير (YYYY-MM):</label>
                <input type="month" id="report_period" name="report_period" value="<?php echo htmlspecialchars($report_period); ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="region_id">المنطقة (اختياري، اتركه فارغًا للسوق الكلي):</label>
                <select name="region_id" id="region_id">
                    <option value="">-- السوق الكلي --</option>
                    <?php foreach($regions as $region): ?>
                        <option value="<?php echo $region['region_id']; ?>" <?php echo ($region_id == $region['region_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($region['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="button" onclick="document.location.href=`index.php?page=market_share&action=add&report_period=${document.getElementById('report_period').value}®ion_id=${document.getElementById('region_id').value}`">تحميل بيانات الفترة</button>
    </fieldset>

    <fieldset>
        <legend>إدخال كميات المبيعات المقدرة</legend>
        <div id="entries-container">
            <!-- منتجاتنا -->
            <h4 style="color:var(--primary-color)">منتجاتنا (الكميات الفعلية من النظام)</h4>
            <?php foreach($our_products as $p): 
                $existing_our_product = array_filter($existing_entries, function($e) use ($p) {
                    return $e['product_id_internal'] == $p['product_id'];
                });
                $existing_value = !empty($existing_our_product) ? current($existing_our_product)['estimated_quantity_sold'] : ($our_sales_data[$p['product_id']] ?? '');
            ?>
            <div class="form-row entry-row">
                <input type="hidden" name="entry[our_<?php echo $p['product_id']; ?>][is_our_product]" value="1">
                <input type="hidden" name="entry[our_<?php echo $p['product_id']; ?>][product_id_internal]" value="<?php echo $p['product_id']; ?>">
                <div class="form-group col-md-6">
                    <label>اسم المنتج:</label>
                    <input type="text" name="entry[our_<?php echo $p['product_id']; ?>][product_name]" value="<?php echo htmlspecialchars($p['name']); ?>" readonly>
                </div>
                <div class="form-group col-md-6">
                    <label>الكمية المباعة:</label>
                    <input type="number" step="0.01" name="entry[our_<?php echo $p['product_id']; ?>][quantity]" value="<?php echo htmlspecialchars($existing_value); ?>" placeholder="أدخل الكمية أو اتركها تلقائية">
                </div>
            </div>
            <?php endforeach; ?>
            
            <hr>
            <!-- منتجات المنافسين -->
            <h4 style="color:var(--danger-color)">منتجات المنافسين</h4>
            <?php 
            $competitor_entries = array_filter($existing_entries, function($e){ return !$e['is_our_product']; });
            foreach($competitor_entries as $index => $comp): ?>
                <div class="form-row entry-row">
                    <input type="hidden" name="entry[comp_<?php echo $index; ?>][is_our_product]" value="0">
                    <div class="form-group col-md-6"><label>اسم المنتج:</label><input type="text" name="entry[comp_<?php echo $index; ?>][product_name]" value="<?php echo htmlspecialchars($comp['product_name']); ?>" required></div>
                    <div class="form-group col-md-5"><label>الكمية المباعة:</label><input type="number" step="0.01" name="entry[comp_<?php echo $index; ?>][quantity]" value="<?php echo htmlspecialchars($comp['estimated_quantity_sold']); ?>" required></div>
                    <div class="form-group col-md-1"><label> </label><button type="button" class="remove-competitor-btn" onclick="this.closest('.entry-row').remove()">X</button></div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-competitor-btn" class="button-link" style="background-color:var(--info-color);">+ إضافة منتج منافس</button>
    </fieldset>

    <div style="margin-top:20px;">
        <button type="submit" class="button-link add-btn">حفظ بيانات الحصة السوقية</button>
    </div>
</form>

<script>
let compIndex = <?php echo count($competitor_entries ?? []); ?>;
document.getElementById('add-competitor-btn').addEventListener('click', function() {
    const container = document.getElementById('entries-container');
    const newIndex = `new_${compIndex++}`;
    const newRow = document.createElement('div');
    newRow.className = 'form-row entry-row';
    newRow.innerHTML = `
        <input type="hidden" name="entry[${newIndex}][is_our_product]" value="0">
        <div class="form-group col-md-6"><label>اسم المنتج:</label><input type="text" name="entry[${newIndex}][product_name]" required></div>
        <div class="form-group col-md-5"><label>الكمية المباعة:</label><input type="number" step="0.01" name="entry[${newIndex}][quantity]" required></div>
        <div class="form-group col-md-1"><label> </label><button type="button" class="remove-competitor-btn" onclick="this.closest('.entry-row').remove()">X</button></div>
    `;
    container.appendChild(newRow);
});
</script>
<style>
.entry-row { align-items: flex-end; }
</style>