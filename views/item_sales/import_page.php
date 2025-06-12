<?php // views/item_sales/import_page.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<!-- قسم استيراد الملف -->
<div class="content-block">
    <h3>استيراد مبيعات الأصناف من ملف Excel</h3>
    <p>
        الأعمدة المتوقعة: A: السنة | B: الشهر | C: رمز العميل | D: رمز الصنف | E: اسم مستخدم المندوب | F: الكمية المباعة <br>
        (اختياري) G: سعر الوحدة | (اختياري) H: القيمة الإجمالية
    </p>
    <p><a href="sample_item_sales_import_template.xlsx" download>تحميل نموذج ملف Excel</a></p>
    <form action="actions/item_sales_import_preview.php" method="POST" enctype="multipart/form-data">
        <?php csrf_input(); ?>
        <div class="form-group">
            <label for="item_sales_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
            <input type="file" id="item_sales_excel_file" name="item_sales_excel_file" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="button-link">معاينة الاستيراد</button>
    </form>
</div>

<!-- قسم عرض المعاينة إذا كانت البيانات موجودة -->
<?php if ($action === 'preview' && !empty($preview_data)): ?>
<div class="content-block" style="margin-top: 20px;">
    <h3>معاينة بيانات الاستيراد</h3>
    <?php if (!empty($import_errors)): ?>
        <div class="message error-message" style="max-height: 200px; overflow-y: auto;">
            <strong>تم العثور على أخطاء (الصفوف التي بها أخطاء لن يتم استيرادها):</strong><br>
            <?php foreach ($import_errors as $errors_for_row): ?>
                - <?php echo implode('<br>- ', array_map('htmlspecialchars', $errors_for_row)); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="max-height: 400px; overflow-y: auto;">
        <table class="preview-table">
            <thead>
                <tr><th>#</th><th>السنة</th><th>الشهر</th><th>رمز العميل</th><th>رمز الصنف</th><th>مندوب</th><th>الكمية</th><th>ملاحظات</th></tr>
            </thead>
            <tbody>
                <?php $i=0; foreach ($preview_data as $row_key => $row): $i++; ?>
                <tr <?php echo isset($import_errors[$row_key]) ? 'style="background-color: #ffe0e0;"' : 'style="background-color: #e6ffe6;"'; ?>>
                    <td><?php echo $i; ?></td>
                    <td><?php echo htmlspecialchars($row['A'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['B'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['C'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['D'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['E'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['F'] ?? ''); ?></td>
                    <td>
                        <?php if (isset($import_errors[$row_key])): ?>
                            <span style="color:red;">خطأ</span>
                        <?php else: echo '<span style="color:green;">صالح</span>'; endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
    <form action="actions/item_sales_import_confirm.php" method="POST" style="display:inline-block;">
        <?php csrf_input(); ?>
        <input type="hidden" name="confirmed_file_name" value="<?php echo htmlspecialchars($import_file_name); ?>">
        <button type="submit" class="button-link add-btn">تأكيد واستيراد البيانات الصالحة</button>
    </form>
    <a href="index.php?page=item_sales_import" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</div>
<?php endif; ?>

<!-- قسم حذف مبيعات شهر كامل -->
<div class="content-block" style="margin-top: 30px; border-color: #dd0000; background-color: #fff5f5;">
    <h3 style="color: #dd0000;">حذف مبيعات الأصناف لشهر كامل</h3>
    <p><strong>تحذير:</strong> هذا الإجراء سيقوم بحذف جميع سجلات مبيعات الأصناف للشهر والسنة المحددين. لا يمكن التراجع عن هذا الإجراء.</p>
    <form action="actions/item_sales_delete_monthly.php" method="POST" onsubmit="return confirm('هل أنت متأكد تمامًا من رغبتك في الحذف؟');">
        <?php csrf_input(); ?>
        <div style="display:flex; gap: 15px; align-items: flex-end;">
            <div class="form-group">
                <label for="delete_year">اختر السنة:</label>
                <select name="delete_year" id="delete_year" class="form-control" required>
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="delete_month">اختر الشهر:</label>
                <select name="delete_month" id="delete_month" class="form-control" required>
                    <?php foreach ($months_map_items as $num => $name): ?>
                        <option value="<?php echo $num; ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="button-link delete-btn">حذف مبيعات الشهر</button>
        </div>
    </form>
</div>

<style>
.content-block { padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
.preview-table { width: 100%; font-size: 0.9em; }
</style>