<?php // views/products/import.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <h3>استيراد المنتجات من ملف Excel</h3>
    <p>قم بإعداد ملف Excel بالترتيب التالي للأعمدة (سيتم تجاهل الصف الأول إذا كان عنوانًا مطابقًا لـ "رمز المنتج"):<br>
       A: رمز المنتج (إلزامي، فريد)<br>
       B: اسم المنتج (إلزامي)<br>
       C: عائلة المنتج (اسم الشركة كما هو مدخل في النظام، اختياري)<br>
       D: وحدة البيع (إلزامي، مثال: قطعة، كرتونة)<br>
       E: تفاصيل التعبئة (اختياري، مثال: 12 قطعة/كرتونة)<br>
       F: الحالة (فعال/غير فعال أو active/inactive أو 1/0، الافتراضي فعال، اختياري)<br>
    </p>
    <p><a href="sample_product_import_template.xlsx" download>تحميل نموذج ملف Excel للمنتجات</a></p>

    <form action="actions/product_import_preview.php" method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <div class="form-group">
            <label for="product_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
            <input type="file" id="product_excel_file" name="product_excel_file" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="button-link">معاينة الاستيراد</button>
        <a href="index.php?page=products" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </form>
</div>