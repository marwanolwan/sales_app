<?php // views/market_surveys/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/market_survey_save.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <?php if ($action == 'edit'): ?>
        <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">
    <?php endif; ?>
    <?php csrf_input(); ?>

    <fieldset>
        <legend>معلومات الدراسة الأساسية</legend>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="product_id">منتجنا (المُراد دراسته):</label>
                <select name="product_id" id="product_id" class="select2-enable" required>
                    <option value="">-- اختر أو ابحث عن منتج --</option>
                    <?php foreach($our_products as $p): ?>
                        <option value="<?php echo $p['product_id']; ?>" <?php echo (isset($survey_data['product_id']) && $survey_data['product_id'] == $p['product_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name'] . ' (' . $p['product_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="survey_date">تاريخ الدراسة:</label>
                <input type="date" name="survey_date" id="survey_date" value="<?php echo htmlspecialchars($survey_data['survey_date'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="customer_id">نقطة البيع (اختياري):</label>
                <select name="customer_id" id="customer_id" class="select2-enable">
                    <option value="">-- اختر أو ابحث عن نقطة بيع --</option>
                     <?php foreach($customers as $c): ?>
                        <option value="<?php echo $c['customer_id']; ?>" <?php echo (isset($survey_data['customer_id']) && $survey_data['customer_id'] == $c['customer_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name'] . ' (' . $c['customer_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="notes">ملاحظات عامة:</label>
            <textarea name="notes" id="notes" rows="3"><?php echo htmlspecialchars($survey_data['notes'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>أسعار منتجنا في هذه الدراسة</legend>
        <div class="form-row">
            <div class="form-group col-md-4"><label>سعر الجملة:</label><input type="number" step="0.01" name="our_wholesale_price" value="<?php echo htmlspecialchars($survey_data['our_wholesale_price'] ?? '0.00'); ?>"></div>
            <div class="form-group col-md-4"><label>سعر التجزئة:</label><input type="number" step="0.01" name="our_retail_price" value="<?php echo htmlspecialchars($survey_data['our_retail_price'] ?? '0.00'); ?>"></div>
            <div class="form-group col-md-4"><label>سعر الرف:</label><input type="number" step="0.01" name="our_shelf_price" value="<?php echo htmlspecialchars($survey_data['our_shelf_price'] ?? '0.00'); ?>"></div>
        </div>
    </fieldset>

    <fieldset>
        <legend>المنتجات المنافسة</legend>
        <div id="competitors-container">
            <?php 
            // دالة مساعدة لطباعة selected
            function e_selected($val1, $val2){ if($val1==$val2) echo 'selected'; }

            if (!empty($competitors_data)): 
                foreach($competitors_data as $index => $comp): ?>
                    <div class="competitor-block" id="comp-block-<?php echo $index; ?>">
                        <h4>منافس #<?php echo $index + 1; ?> <button type="button" class="remove-competitor-btn" onclick="removeCompetitor(this)">إزالة</button></h4>
                        <input type="hidden" name="competitor[<?php echo $index; ?>][id]" value="<?php echo $comp['competitor_entry_id']; ?>">
                        <div class="form-row">
                            <div class="form-group col-md-6"><label>اسم المنتج المنافس:</label><input type="text" name="competitor[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($comp['competitor_product_name']); ?>" required></div>
                            <div class="form-group col-md-6"><label>التصنيف التنافسي:</label>
                                <select name="competitor[<?php echo $index; ?>][position]">
                                    <option value="Market Leader" <?php e_selected($comp['competitive_position'], 'Market Leader');?>>Market Leader</option>
                                    <option value="Challenger" <?php e_selected($comp['competitive_position'], 'Challenger');?>>Challenger</option>
                                    <option value="Follower" <?php e_selected($comp['competitive_position'], 'Follower');?>>Follower</option>
                                    <option value="Nicher" <?php e_selected($comp['competitive_position'], 'Nicher');?>>Nicher</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3"><label>سعر جملة:</label><input type="number" step="0.01" name="competitor[<?php echo $index; ?>][wholesale]" value="<?php echo htmlspecialchars($comp['wholesale_price'] ?? ''); ?>"></div>
                            <div class="form-group col-md-3"><label>سعر تجزئة:</label><input type="number" step="0.01" name="competitor[<?php echo $index; ?>][retail]" value="<?php echo htmlspecialchars($comp['retail_price'] ?? ''); ?>"></div>
                            <div class="form-group col-md-3"><label>سعر رف:</label><input type="number" step="0.01" name="competitor[<?php echo $index; ?>][shelf]" value="<?php echo htmlspecialchars($comp['shelf_price'] ?? ''); ?>"></div>
                            <div class="form-group col-md-3"><label>عدد الأوجه:</label><input type="number" name="competitor[<?php echo $index; ?>][facings]" value="<?php echo htmlspecialchars($comp['facings_on_shelf'] ?? ''); ?>"></div>
                        </div>
                        <div class="form-group">
                            <label>إضافة صور للمنتج المنافس (يمكن تحديد أكثر من صورة):</label>
                            <input type="file" name="competitor_images_<?php echo $index; ?>[]" multiple accept="image/*">
                        </div>
                        <div class="current-images-container">
                            <?php if(!empty($comp['images'])): ?>
                                <?php foreach($comp['images'] as $image): ?>
                                    <div class="image-thumbnail" id="image-thumb-<?php echo $image['image_id']; ?>">
                                        <a href="<?php echo SURVEY_IMAGE_DIR . $image['image_path']; ?>" target="_blank">
                                            <img src="<?php echo SURVEY_IMAGE_DIR . $image['image_path']; ?>" alt="صورة منافس">
                                        </a>
                                        <button type="button" class="delete-image-btn" data-image-id="<?php echo $image['image_id']; ?>">X</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; 
            endif; ?>
        </div>
        <button type="button" id="add-competitor-btn" class="button-link" style="background-color:var(--info-color);">+ إضافة منافس</button>
    </fieldset>
    
    <div style="margin-top:20px;">
        <button type="submit" class="button-link add-btn">حفظ الدراسة</button>
        <a href="index.php?page=market_surveys" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </div>
</form>

<script>
// --- كود JAVASCRIPT ---
$(document).ready(function() {
    // تفعيل Select2
    $('.select2-enable').select2({
        placeholder: "-- اختر أو ابحث --",
        dir: "rtl",
        width: '100%'
    });

    // حذف صورة عبر AJAX
    $(document).on('click', '.delete-image-btn', function() {
        if (!confirm('هل أنت متأكد من حذف هذه الصورة؟')) return;

        const button = $(this);
        const imageId = button.data('image-id');
        const imageThumb = $('#image-thumb-' + imageId);

        // استخدام التوكن من حقل مخفي
        const csrfToken = $('input[name="csrf_token"]').val();

        $.ajax({
            url: 'actions/market_survey_image_delete.php',
            type: 'POST',
            data: { 
                image_id: imageId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    imageThumb.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert('فشل حذف الصورة: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('حدث خطأ في الاتصال بالخادم: ' + textStatus);
                console.error(jqXHR.responseText);
            }
        });
    });
});

let competitorIndex = <?php echo isset($competitors_data) ? count($competitors_data) : 0; ?>;

document.getElementById('add-competitor-btn').addEventListener('click', function() {
    const container = document.getElementById('competitors-container');
    const newIndex = competitorIndex++;
    const competitorBlock = document.createElement('div');
    competitorBlock.className = 'competitor-block';
    competitorBlock.id = `comp-block-${newIndex}`;
    
    competitorBlock.innerHTML = `
        <h4>منافس #${newIndex + 1} <button type="button" class="remove-competitor-btn" onclick="removeCompetitor(this)">إزالة</button></h4>
        <input type="hidden" name="competitor[${newIndex}][id]" value="new">
        <div class="form-row">
            <div class="form-group col-md-6"><label>اسم المنتج المنافس:</label><input type="text" name="competitor[${newIndex}][name]" required></div>
            <div class="form-group col-md-6"><label>التصنيف التنافسي:</label>
                <select name="competitor[${newIndex}][position]">
                    <option value="Market Leader">Market Leader</option>
                    <option value="Challenger">Challenger</option>
                    <option value="Follower">Follower</option>
                    <option value="Nicher">Nicher</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3"><label>سعر جملة:</label><input type="number" step="0.01" name="competitor[${newIndex}][wholesale]"></div>
            <div class="form-group col-md-3"><label>سعر تجزئة:</label><input type="number" step="0.01" name="competitor[${newIndex}][retail]"></div>
            <div class="form-group col-md-3"><label>سعر رف:</label><input type="number" step="0.01" name="competitor[${newIndex}][shelf]"></div>
            <div class="form-group col-md-3"><label>عدد الأوجه:</label><input type="number" name="competitor[${newIndex}][facings]"></div>
        </div>
        <div class="form-group">
            <label>إضافة صور (يمكن تحديد أكثر من صورة):</label>
            <input type="file" name="competitor_images_${newIndex}[]" multiple accept="image/*">
        </div>
    `;
    container.appendChild(competitorBlock);
});

function removeCompetitor(button) {
    const block = button.closest('.competitor-block');
    if (block) {
        block.remove();
    }
}
</script>


<style>
fieldset { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
legend { font-weight: bold; color: var(--primary-color); }
.competitor-block { border: 1px solid #eee; padding: 10px; margin-bottom: 15px; border-radius: 4px; background-color: #fafafa; }
.competitor-block h4 { margin-top: 0; display:flex; justify-content:space-between; align-items:center; }
.remove-competitor-btn { background-color: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8em; }
</style>