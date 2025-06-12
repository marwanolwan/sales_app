<?php // views/collections/import_preview.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="card">
    <div class="card-header">
        <h3>معاينة بيانات استيراد التحصيلات</h3>
    </div>
    <div class="card-body">
        <p class="info-message">
            <i class="fas fa-info-circle"></i> 
            الرجاء مراجعة البيانات أدناه. الصفوف التي تظهر باللون الأحمر تحتوي على أخطاء ولن يتم استيرادها. إذا كانت البيانات صحيحة، اضغط على زر "تأكيد واستيراد".
        </p>
            
        <?php if (!empty($import_errors)): ?>
            <div class="message error-message" style="max-height: 200px; overflow-y: auto;">
                <strong>ملخص الأخطاء التي تم اكتشافها:</strong><br>
                <?php 
                $flat_errors = [];
                foreach ($import_errors as $errors_for_row) {
                    foreach ($errors_for_row as $error_message) {
                        // إزالة رقم الصف من الرسالة لعرض ملخص بدون تكرار
                        $flat_errors[] = preg_replace('/^الصف \d+:/', '', $error_message);
                    }
                }
                echo '<ul>';
                foreach (array_unique($flat_errors) as $error) {
                    echo '<li>' . htmlspecialchars(trim($error)) . '</li>';
                }
                echo '</ul>';
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container" style="max-height: 500px; overflow-y: auto;">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>السنة (A)</th>
                        <th>الشهر (B)</th>
                        <th>المندوب (C)</th>
                        <th>المبلغ (D)</th>
                        <th>ملاحظات (E)</th>
                        <th>حالة الصف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i_prev=0; foreach ($preview_data as $excel_row_key => $row_prev): $i_prev++; ?>
                    <tr class="<?php echo isset($import_errors[$excel_row_key]) ? 'row-error' : 'row-success'; ?>">
                        <td><?php echo $i_prev + 1; // +1 لأننا أزلنا صف العناوين ?></td>
                        <td><?php echo htmlspecialchars(trim($row_prev['A'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars(trim($row_prev['B'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars(trim($row_prev['C'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars(trim($row_prev['D'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars(trim($row_prev['E'] ?? '')); ?></td>
                        <td class="status-cell" title="<?php echo isset($import_errors[$excel_row_key]) ? htmlspecialchars(implode(', ', $import_errors[$excel_row_key])) : 'صالح للاستيراد'; ?>">
                            <?php if (isset($import_errors[$excel_row_key])): ?>
                                <span class="error-text"><i class="fas fa-times-circle"></i> خطأ</span>
                            <?php else: echo '<span class="success-text"><i class="fas fa-check-circle"></i> صالح</span>'; endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <br>
        <div class="form-actions">
            <form action="actions/collection_import_confirm.php" method="POST" style="display:inline-block;">
                <?php csrf_input(); ?>
                <button type="submit" class="button-link add-btn" <?php if (count($preview_data) === count($import_errors)) echo 'disabled'; ?>>
                    <i class="fas fa-check"></i> تأكيد واستيراد البيانات الصالحة
                </button>
            </form>
            <a href="index.php?page=collections&action=import" class="button-link" style="background-color:#6c757d;">
                <i class="fas fa-times"></i> إلغاء والعودة
            </a>
        </div>
        <?php if (count($preview_data) === count($import_errors)): ?>
            <p class="message error-message" style="margin-top:15px;">
                <strong>تنبيه:</strong> كل الصفوف في الملف تحتوي على أخطاء. لا يمكن المتابعة. يرجى تصحيح الملف والمحاولة مرة أخرى.
            </p>
        <?php endif; ?>
    </div>
</div>

<style>
/* يمكنك نقل هذا إلى style.css */
.preview-table {
    width: 100%;
    border-collapse: collapse;
}
.preview-table th, .preview-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: right;
    font-size: 0.9em;
}
.preview-table thead th {
    background-color: #f2f2f2;
    position: sticky;
    top: 0;
}
.row-error {
    background-color: #f8d7da !important;
    color: #721c24;
}
.row-success {
    background-color: #d4edda;
}
.status-cell {
    text-align: center;
}
.status-cell .error-text { color: #721c24; font-weight: bold; }
.status-cell .success-text { color: #155724; font-weight: bold; }
.form-actions {
    padding-top: 15px;
    border-top: 1px solid #eee;
}
</style>