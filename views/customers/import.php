<?php // views/customers/import.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <h3>استيراد العملاء من ملف Excel</h3>
    <p>قم بإعداد ملف Excel بالترتيب التالي للأعمدة (سيتم تجاهل الصف الأول إذا كان عنوانًا مطابقًا لـ "رمز العميل"):<br>
       A: رمز العميل (إلزامي، فريد)<br>
       B: اسم العميل (إلزامي)<br>
       C: نوع العميل (اسم التصنيف كما هو مدخل في النظام، اختياري)<br>
       D: العنوان (اختياري)<br>
       E: اسم المندوب (الاسم الكامل للمندوب كما هو مدخل في النظام، اختياري)<br>
       F: اسم المروج (الاسم الكامل للمروج كما هو مدخل في النظام، اختياري)<br>
       G: خط العرض (رقم عشري، اختياري)<br>
       H: خط الطول (رقم عشري، اختياري)<br>
       I: حالة العميل (active أو inactive، الافتراضي active)<br>
       J: تاريخ فتح الملف (YYYY-MM-DD أو تاريخ Excel)<br>
    </p>
    <p><a href="sample_customer_import_template.xlsx" download>تحميل نموذج ملف Excel للعملاء</a></p>

    <form action="actions/customer_import_preview.php" method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <div class="form-group">
            <label for="customer_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
            <input type="file" id="customer_excel_file" name="customer_excel_file" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="button-link">معاينة الاستيراد</button>
        <a href="index.php?page=customers" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </form>
</div>