<?php // views/monthly_sales/import.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <h3>استيراد المبيعات الشهرية من ملف Excel</h3>
    <p>
        قم بإعداد ملف Excel بالترتيب التالي للأعمدة. سيتم تجاهل الصف الأول إذا كان يحتوي على عناوين.<br>
        <strong style="display: block; margin-top: 10px;">
           A: اسم المندوب (الاسم الكامل كما هو مسجل في النظام، إلزامي)<br>
           B: السنة (مثال: 2024، إلزامي)<br>
           C: الشهر (رقم الشهر 1-12، إلزامي)<br>
           D: صافي مبلغ المبيعات (رقم، إلزامي)<br>
           E: ملاحظات (اختياري)
        </strong>
    </p>
    <p>
        <a href="sample_monthly_sales_import_template.xlsx" download>تحميل نموذج ملف Excel للمبيعات</a>
        (تأكد من وجود هذا الملف في المسار الصحيح)
    </p>

    <form action="actions/monthly_sales_import_preview.php" method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <div class="form-group">
            <label for="sales_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
            <input type="file" id="sales_excel_file" name="sales_excel_file" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="button-link">معاينة استيراد المبيعات</button>
        <a href="index.php?page=monthly_sales" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </form>
</div>