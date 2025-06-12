<?php // views/item_sales_import/main.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="item-sales-management">
    
    <!-- قسم استيراد الملف -->
    <div class="content-block" style="margin-bottom: 30px;">
        <h3>استيراد مبيعات الأصناف من ملف Excel</h3>
        <p>
            الأعمدة المتوقعة في الملف (الصف الأول يمكن أن يكون للعناوين وسيتم تجاهله):<br>
            <strong style="display: block; margin-top: 10px;">
                A: السنة (مثال: 2024)<br>
                B: الشهر (رقم من 1-12)<br>
                C: رمز العميل (إلزامي)<br>
                D: رمز الصنف (إلزامي)<br>
                E: اسم مستخدم المندوب (إلزامي)<br>
                F: الكمية المباعة (يمكن أن تكون سالبة للمرتجعات)<br>
                G: سعر الوحدة (اختياري)<br>
                H: القيمة الإجمالية (اختياري)
            </strong>
        </p>
        <p><a href="sample_item_sales_import_template.xlsx" download>تحميل نموذج ملف Excel لمبيعات الأصناف</a></p>
        
        <form action="actions/item_sales_import_preview.php" method="POST" enctype="multipart/form-data">
            <?php csrf_input(); ?>
            <div class="form-group">
                <label for="item_sales_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
                <input type="file" id="item_sales_excel_file" name="item_sales_excel_file" accept=".xls,.xlsx" required>
            </div>
            <button type="submit" class="button-link">معاينة الاستيراد</button>
        </form>
    </div>

    <!-- قسم حذف مبيعات شهر كامل -->
    <div class="content-block" style="border: 2px solid #dc3545; background-color: #f8d7da;">
        <h3 style="color: #721c24;">حذف مبيعات الأصناف لشهر كامل</h3>
        <p style="color: #721c24;"><strong>تحذير:</strong> هذا الإجراء سيقوم بحذف جميع سجلات مبيعات الأصناف للشهر والسنة المحددين. لا يمكن التراجع عن هذا الإجراء.</p>
        
        <form action="actions/item_sales_delete_monthly.php" method="POST" onsubmit="return confirm('هل أنت متأكد تمامًا من رغبتك في حذف جميع مبيعات الأصناف للشهر والسنة المحددين؟ هذا الإجراء لا يمكن التراجع عنه.');">
            <?php csrf_input(); ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="delete_year_select">اختر السنة:</label>
                    <select name="delete_year" id="delete_year_select" class="form-control" required>
                        <option value="">-- اختر السنة --</option>
                        <?php for ($y_del = date('Y'); $y_del >= date('Y') - 5; $y_del--): ?>
                            <option value="<?php echo $y_del; ?>"><?php echo $y_del; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="delete_month_select">اختر الشهر:</label>
                    <select name="delete_month" id="delete_month_select" class="form-control" required>
                        <option value="">-- اختر الشهر --</option>
                        <?php foreach ($months_map as $num_del => $name_del): ?>
                            <option value="<?php echo $num_del; ?>"><?php echo htmlspecialchars($name_del); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4" style="align-self: flex-end;">
                    <button type="submit" class="button-link delete-btn" style="width:100%;">حذف مبيعات الشهر</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* You can move this to your main CSS file */
.content-block { padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; }
.form-row { display: flex; flex-wrap: wrap; margin-right: -5px; margin-left: -5px; }
.form-group { position: relative; width: 100%; padding-right: 5px; padding-left: 5px; margin-bottom: 1rem; }
@media (min-width: 768px) {
    .form-group.col-md-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
}
</style>