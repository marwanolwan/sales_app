<?php // views/sales_targets/import.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <h3>استيراد الأهداف الشهرية من ملف Excel</h3>
    <p>قم بإعداد ملف Excel بالترتيب التالي للأعمدة (سيتم تجاهل الصف الأول إذا كان عنوانًا مطابقًا لـ "اسم المندوب"):<br>
       A: اسم المندوب (الاسم الكامل كما هو مسجل في النظام، إلزامي)<br>
       B: السنة (مثال: 2024، إلزامي)<br>
       C: الشهر (رقم الشهر 1-12، إلزامي)<br>
       D: مبلغ الهدف (رقم، إلزامي)<br>
    </p>
    <p><a href="sample_targets_import_template.xlsx" download>تحميل نموذج ملف Excel للأهداف</a></p>

    <form action="actions/sales_target_import_preview.php" method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <div class="form-group">
            <label for="targets_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
            <input type="file" id="targets_excel_file" name="targets_excel_file" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="button-link">معاينة الاستيراد</button>
        <a href="index.php?page=sales_targets" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </form>
</div>