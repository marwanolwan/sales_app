<?php // views/pricing/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/pricing_save.php" method="POST" id="pricingForm">
    <?php csrf_input(); ?>
    <?php if ($action == 'edit'): ?>
        <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">
    <?php endif; ?>

    <div class="form-section">
        <div class="form-group">
            <label for="product_id">الصنف الأساسي <span class="required">*</span></label>
            <select name="product_id" id="product_id" required <?php if($action == 'edit') echo 'disabled'; ?> class="searchable-select" style="width: 100%;">
                <option value="">-- اختر أو ابحث عن صنف --</option>
                <?php foreach($products as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>" <?php echo (isset($offer_data['product_id']) && $offer_data['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($product['name'] . ' (' . $product['product_code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
             <?php if($action == 'edit'): ?>
                <input type="hidden" name="product_id" value="<?php echo $offer_data['product_id']; ?>">
            <?php endif; ?>
        </div>

        <div id="levels-container">
            <?php 
            $levels = $offer_data['levels'] ?? [[]];
            foreach ($levels as $index => $level): 
            ?>
            <div class="price-level-card" data-level-index="<?php echo $index; ?>">
                <input type="hidden" name="levels[<?php echo $index; ?>][level_id]" value="<?php echo $level['level_id'] ?? ''; ?>">
                <div class="level-header">
                    <h4>المستوى <?php echo $index + 1; ?></h4>
                    <button type="button" class="button-link-sm danger-btn remove-level-btn">إزالة المستوى</button>
                </div>
                <div class="level-grid">
                    <div class="form-group">
                        <label>الكمية المشروطة (كرتونة)<span class="required">*</span></label>
                        <input type="number" step="1" min="1" name="levels[<?php echo $index; ?>][condition_quantity]" value="<?php echo htmlspecialchars($level['condition_quantity'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>سعر الكرتونة للشرط<span class="required">*</span></label>
                        <input type="number" step="0.001" min="0" name="levels[<?php echo $index; ?>][price_per_unit]" value="<?php echo htmlspecialchars($level['price_per_unit'] ?? ''); ?>" required>
                    </div>
                     <div class="form-group">
                        <label>بونص من نفس الصنف (كرتونة)</label>
                        <input type="number" step="0.01" min="0" name="levels[<?php echo $index; ?>][bonus_same_item_quantity]" value="<?php echo htmlspecialchars($level['bonus_same_item_quantity'] ?? '0'); ?>">
                    </div>
                    <div class="form-group">
                        <label>قطع/كرتونة</label>
                        <input type="number" step="1" min="1" name="levels[<?php echo $index; ?>][pieces_per_unit]" value="<?php echo htmlspecialchars($level['pieces_per_unit'] ?? '1'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>ملاحظات المستوى</label>
                    <input type="text" name="levels[<?php echo $index; ?>][notes]" value="<?php echo htmlspecialchars($level['notes'] ?? ''); ?>" placeholder="ملاحظات خاصة بهذا المستوى (اختياري)">
                </div>
                
                <div class="bonus-items-container">
                    <h5>أصناف البونص الإضافية (اختياري):</h5>
                    <?php 
                    $bonus_items = $level['bonus_items'] ?? [];
                    foreach($bonus_items as $b_index => $bonus):
                    ?>
                    <div class="bonus-item-row">
                        <select name="levels[<?php echo $index; ?>][bonus_items][<?php echo $b_index; ?>][bonus_product_id]" class="searchable-select" style="width: 40%;">
                            <option value="">-- اختر صنف البونص --</option>
                            <?php foreach($products as $p): ?>
                                <option value="<?php echo $p['product_id']; ?>" <?php echo (isset($bonus['bonus_product_id']) && $bonus['bonus_product_id'] == $p['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" step="0.01" min="0" name="levels[<?php echo $index; ?>][bonus_items][<?php echo $b_index; ?>][bonus_quantity]" placeholder="الكمية" value="<?php echo htmlspecialchars($bonus['bonus_quantity'] ?? ''); ?>">
                        <input type="number" step="0.001" min="0" name="levels[<?php echo $index; ?>][bonus_items][<?php echo $b_index; ?>][bonus_price]" placeholder="سعر كرتونة البونص" value="<?php echo htmlspecialchars($bonus['bonus_price'] ?? ''); ?>">
                        <button type="button" class="remove-bonus-btn">إزالة</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- *** هذا هو الزر الذي تم تصحيح الكلاس الخاص به *** -->
                <button type="button" class="button-link-sm add-bonus-btn" style="background-color: #17a2b8;">+ إضافة صنف بونص آخر</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-level-btn" class="button-link" style="margin-top: 15px;">+ إضافة مستوى سعر جديد</button>
    </div>

    <div class="form-actions">
        <button type="submit" class="button-link save-btn">حفظ التسعير</button>
        <a href="index.php?page=pricing" class="button-link" style="background-color: #6c757d;">إغلاق</a>
    </div>
</form>

<script>
$(document).ready(function() {
    // دالة لتفعيل Select2 على عنصر معين أو على كل العناصر
    function initializeSelect2(element) {
        $(element).select2({
            placeholder: "-- اختر أو ابحث --",
            allowClear: true,
            dir: "rtl",
            width: 'style' // يجعل العرض يتناسب مع العنصر الأصلي
        });
    }

    // تفعيل Select2 على جميع القوائم الموجودة عند تحميل الصفحة
    initializeSelect2('.searchable-select');

    // === منطق إضافة وإزالة المستويات والبونص ===
    const levelsContainer = document.getElementById('levels-container');
    const addLevelBtn = document.getElementById('add-level-btn');
    let levelIndex = levelsContainer.children.length;

    addLevelBtn.addEventListener('click', function() {
        const newLevelHtml = `
            <div class="price-level-card" data-level-index="${levelIndex}">
                <input type="hidden" name="levels[${levelIndex}][level_id]" value="">
                <div class="level-header">
                    <h4>المستوى ${levelIndex + 1}</h4>
                    <button type="button" class="button-link-sm danger-btn remove-level-btn">إزالة المستوى</button>
                </div>
                <div class="level-grid">
                    <div class="form-group"><label>الكمية المشروطة (كرتونة)<span class="required">*</span></label><input type="number" step="1" min="1" name="levels[${levelIndex}][condition_quantity]" required></div>
                    <div class="form-group"><label>سعر الكرتونة للشرط<span class="required">*</span></label><input type="number" step="0.001" min="0" name="levels[${levelIndex}][price_per_unit]" required></div>
                    <div class="form-group"><label>بونص من نفس الصنف (كرتونة)</label><input type="number" step="0.01" min="0" name="levels[${levelIndex}][bonus_same_item_quantity]" value="0"></div>
                    <div class="form-group"><label>قطع/كرتونة</label><input type="number" step="1" min="1" name="levels[${levelIndex}][pieces_per_unit]" value="1"></div>
                </div>
                <div class="form-group"><label>ملاحظات المستوى</label><input type="text" name="levels[${levelIndex}][notes]" placeholder="ملاحظات خاصة بهذا المستوى (اختياري)"></div>
                <div class="bonus-items-container"><h5>أصناف البونص الإضافية (اختياري):</h5></div>
                <button type="button" class="button-link-sm add-bonus-btn" style="background-color: #17a2b8;">+ إضافة صنف بونص آخر</button>
            </div>`;
        levelsContainer.insertAdjacentHTML('beforeend', newLevelHtml);
        levelIndex++;
    });

    levelsContainer.addEventListener('click', function(e) {
        // إزالة مستوى سعر
        if (e.target.classList.contains('remove-level-btn')) {
            if (levelsContainer.children.length > 1) {
                e.target.closest('.price-level-card').remove();
            } else {
                alert('يجب وجود مستوى سعر واحد على الأقل.');
            }
        }

        // إضافة صنف بونص
        if (e.target.classList.contains('add-bonus-btn')) {
            const parentLevelCard = e.target.closest('.price-level-card');
            const currentLevelIndex = parentLevelCard.dataset.levelIndex;
            const bonusContainer = parentLevelCard.querySelector('.bonus-items-container');
            let bonusIndex = bonusContainer.querySelectorAll('.bonus-item-row').length;
            
            const productOptions = document.getElementById('product_id').innerHTML;
            
            const newBonusHtml = `
                <div class="bonus-item-row">
                    <select name="levels[${currentLevelIndex}][bonus_items][${bonusIndex}][bonus_product_id]" class="searchable-select" style="width: 40%;">
                        ${productOptions}
                    </select>
                    <input type="number" step="0.01" min="0" name="levels[${currentLevelIndex}][bonus_items][${bonusIndex}][bonus_quantity]" placeholder="الكمية" required>
                    <input type="number" step="0.001" min="0" name="levels[${currentLevelIndex}][bonus_items][${bonusIndex}][bonus_price]" placeholder="سعر كرتونة البونص">
                    <button type="button" class="remove-bonus-btn">إزالة</button>
                </div>`;
            
            const newRow = $(newBonusHtml);
            $(bonusContainer).append(newRow);
            initializeSelect2(newRow.find('.searchable-select'));
        }

        // إزالة صنف بونص
        if (e.target.classList.contains('remove-bonus-btn')) {
            e.target.closest('.bonus-item-row').remove();
        }
    });
});
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.form-section { border: 1px solid #e0e0e0; padding: 20px; border-radius: 8px; background-color: #fdfdfd; }
.price-level-card { border: 1px solid #ccc; border-radius: 6px; padding: 15px; margin-bottom: 20px; background-color: #fff; }
.level-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
.level-header h4 { margin: 0; color: #007bff; }
.level-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
.bonus-items-container { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc; }
.bonus-items-container h5 { margin-top: 0; margin-bottom: 10px; }
.bonus-item-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
.bonus-item-row select { flex: 3; }
.bonus-item-row input { flex: 1; }
.remove-bonus-btn { background-color: #dc3545; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; }
.form-actions { margin-top: 20px; text-align: left; }
span.required { color: red; }
</style>