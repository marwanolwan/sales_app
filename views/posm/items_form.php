<?php // views/posm/items_form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>استخدم هذا النموذج لتعريف المواد الترويجية التي يتم توزيعها في السوق.</p>

<div class="form-container">
    <form action="actions/posm_item_save.php" method="POST">
        <?php csrf_input(); ?>
        
        <!-- تمرير نوع الإجراء (إضافة أو تعديل) -->
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        
        <!-- تمرير ID المادة في حالة التعديل -->
        <?php if ($action == 'edit_item' && !empty($item_id)): ?>
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group col-md-8">
                <label for="item_name">اسم المادة الترويجية:</label>
                <input type="text" 
                       id="item_name" 
                       name="item_name" 
                       value="<?php echo htmlspecialchars($item_data['item_name'] ?? ''); ?>" 
                       placeholder="مثال: بوستر حملة الصيف 2025" 
                       required>
            </div>
            <div class="form-group col-md-4">
                <label for="item_code">كود المادة (اختياري):</label>
                <input type="text" 
                       id="item_code" 
                       name="item_code" 
                       value="<?php echo htmlspecialchars($item_data['item_code'] ?? ''); ?>"
                       placeholder="مثال: POSM001">
            </div>
        </div>

        <div class="form-group">
            <label for="description">الوصف (اختياري):</label>
            <textarea id="description" 
                      name="description" 
                      rows="4" 
                      placeholder="أضف أي تفاصيل إضافية عن هذه المادة، مثل الأبعاد، المادة المصنعة منها، إلخ."><?php echo htmlspecialchars($item_data['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="button-link add-btn">
                <?php echo ($action == 'add_item') ? 'إضافة المادة' : 'حفظ التغييرات'; ?>
            </button>
            <a href="index.php?page=posm&action=items_list" class="button-link" style="background-color:#6c757d;">
                إلغاء
            </a>
        </div>
    </form>
</div>

<!-- =====| بداية كود CSS المدمج |===== -->
<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 25px;
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #343a40;
}
.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* للتأكد من أن padding لا يؤثر على العرض */
}
.form-group textarea {
    resize: vertical; /* السماح بتغيير ارتفاع حقل النص */
}

/* Form Row for side-by-side fields */
.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.form-group.col-md-8 {
    flex: 1 1 60%;
}
.form-group.col-md-4 {
    flex: 1 1 30%;
}
@media (max-width: 768px) {
    .form-group.col-md-8, .form-group.col-md-4 {
        flex-basis: 100%;
    }
}

.form-actions {
    text-align: left; /* محاذاة الأزرار لليسار */
    margin-top: 25px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>