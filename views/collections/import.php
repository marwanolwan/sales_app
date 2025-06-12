<?php // views/collections/import.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="card">
    <div class="card-header">
        <h3>رفع ملف تحصيلات شهرية</h3>
    </div>
    <div class="card-body">
        <div class="instructions">
            <h4>تعليمات إعداد الملف:</h4>
            <p>الرجاء التأكد من أن ملف Excel الخاص بك منظم بالترتيب التالي للأعمدة. **سيتم تجاهل الصف الأول تلقائيًا** لأنه مخصص للعناوين.</p>
            <ul>
                <li><strong>العمود A:</strong> السنة (مثال: `2024`)</li>
                <li><strong>العمود B:</strong> الشهر (رقم من 1 إلى 12)</li>
                <li><strong>العمود C:</strong> اسم المندوب (يجب أن يكون الاسم الكامل مطابقًا لما هو مسجل في النظام)</li>
                <li><strong>العمود D:</strong> مبلغ التحصيل (رقم فقط، مثل `15000.50`)</li>
                <li><strong>العمود E:</strong> ملاحظات (اختياري)</li>
            </ul>
        </div>
        
        <div class="download-template">
            <p>لتسهيل العملية، يمكنك تحميل نموذج ملف جاهز للتعبئة.</p>
            <a href="sample_collection_import.xlsx" download class="button-link" style="background-color: var(--secondary-color);">
                <i class="fas fa-file-excel"></i> تحميل النموذج
            </a>
            <p class="small-note">(ملاحظة: يجب عليك إنشاء ملف `sample_collection_import.xlsx` ووضعه في جذر المشروع ليعمل هذا الرابط).</p>
        </div>

        <hr>

        <form action="actions/collection_import_preview.php" method="POST" enctype="multipart/form-data">
            <?php csrf_input(); ?>
            <div class="form-group">
                <label for="collection_excel_file">اختر ملف Excel (.xls, .xlsx):</label>
                <input type="file" id="collection_excel_file" name="collection_excel_file" class="form-control-file" accept=".xls,.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="button-link">
                    <i class="fas fa-eye"></i> معاينة الاستيراد
                </button>
                <a href="index.php?page=collections" class="button-link" style="background-color: #6c757d;">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
/* يمكنك نقل هذا إلى style.css */
.instructions {
    background-color: #e7f3fe;
    border-left: 5px solid #2196F3;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.instructions h4 {
    margin-top: 0;
    color: #1E88E5;
}
.instructions ul {
    padding-right: 20px;
}
.download-template {
    margin-bottom: 20px;
}
.small-note {
    font-size: 0.8em;
    color: #777;
    margin-top: 5px;
}
.form-control-file {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    display: block;
    width: 100%;
}
.form-actions {
    margin-top: 20px;
}
</style>