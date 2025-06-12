<?php // views/assets/types_form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="form-container">
    <form action="actions/asset_type_save.php" method="POST">
        <?php csrf_input(); ?>
        
        <!-- تمرير نوع الإجراء (إضافة أو تعديل) -->
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        
        <!-- تمرير ID النوع في حالة التعديل -->
        <?php if ($action == 'edit_type' && !empty($type_id)): ?>
            <input type="hidden" name="type_id" value="<?php echo $type_id; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="type_name">اسم نوع الأصل:</label>
            <input type="text" 
                   id="type_name" 
                   name="type_name" 
                   value="<?php echo htmlspecialchars($type_data['type_name'] ?? ''); ?>" 
                   placeholder="مثال: ثلاجة عرض باب واحد" 
                   required>
        </div>

        <div class="form-group">
            <label for="description">الوصف (اختياري):</label>
            <textarea id="description" 
                      name="description" 
                      rows="4" 
                      placeholder="أضف أي تفاصيل إضافية عن هذا النوع من الأصول..."><?php echo htmlspecialchars($type_data['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="button-link add-btn">
                <?php echo ($action == 'add_type') ? 'إضافة النوع' : 'حفظ التغييرات'; ?>
            </button>
            <a href="index.php?page=assets&action=types" class="button-link" style="background-color:#6c757d;">
                إلغاء
            </a>
        </div>
    </form>
</div>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.form-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 25px;
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}
.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.form-actions {
    text-align: left; /* محاذاة الأزرار لليسار (أو اليمين في واجهة إنجليزية) */
    margin-top: 25px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>