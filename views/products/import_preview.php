<?php // views/products/import_preview.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <p>الرجاء مراجعة البيانات. الصفوف التي تحتوي على أخطاء لن يتم استيرادها.</p>
    
    <?php if ($has_errors): ?>
        <div class="message error-message">
            <strong>تم العثور على أخطاء في الملف. لن يتم استيراد الصفوف التي بها أخطاء:</strong><br>
            <?php 
            foreach ($import_errors as $row_key_err => $errors_for_row):
                foreach ($errors_for_row as $err_msg):
                     echo '- ' . htmlspecialchars($err_msg) . '<br>';
                endforeach;
            endforeach; 
            ?>
        </div>
    <?php endif; ?>

    <table class="preview-table">
        <thead>
            <tr>
                <th>#</th>
                <th>رمز المنتج (A)</th>
                <th>اسم المنتج (B)</th>
                <th>عائلة المنتج (C)</th>
                <th>الوحدة (D)</th>
                <th>التعبئة (E)</th>
                <th>الحالة (F)</th>
                <?php if ($has_errors): ?><th>ملاحظات</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($preview_data as $row_key_prev => $row_prev): ?>
            <tr <?php echo isset($import_errors[$row_key_prev]) ? 'style="background-color: #f8d7da;"' : 'style="background-color: #dff0d8;"'; ?>>
                <td><?php echo htmlspecialchars($row_prev['row_num']); ?></td>
                <td><?php echo htmlspecialchars($row_prev['data']['product_code']); ?></td>
                <td><?php echo htmlspecialchars($row_prev['data']['name']); ?></td>
                <td><?php echo htmlspecialchars($row_prev['data']['family_name']); ?></td>
                <td><?php echo htmlspecialchars($row_prev['data']['unit']); ?></td>
                <td><?php echo htmlspecialchars($row_prev['data']['packaging_details']); ?></td>
                <td><?php echo htmlspecialchars($row_prev['data']['is_active_text']); ?></td>
                <?php if ($has_errors): ?>
                    <td>
                        <?php if (isset($import_errors[$row_key_prev])): ?>
                            <ul class="error-list">
                            <?php foreach ($import_errors[$row_key_prev] as $err_text_item): ?>
                                <li><?php echo htmlspecialchars(str_replace("الصف {$row_prev['row_num']}: ", "", $err_text_item)); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <div class="form-actions">
        <form action="actions/product_import_confirm.php" method="POST" style="display:inline-block;">
            <?php csrf_input(); ?>
            <button type="submit" class="button-link" <?php echo empty($preview_data) ? 'disabled' : ''; ?>>
                تأكيد واستيراد البيانات الصالحة
            </button>
        </form>
        <a href="index.php?page=products&action=import" class="button-link" style="background-color:#6c757d;">إلغاء والعودة</a>
    </div>
</div>
<style>
.preview-table .error-list { margin:0; padding-right:15px; color:red; list-style-type:square; }
</style>