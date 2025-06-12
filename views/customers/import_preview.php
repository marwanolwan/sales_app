<?php // views/customers/import_preview.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="content-block">
    <p>الرجاء مراجعة البيانات. الصفوف التي تحتوي على أخطاء لن يتم استيرادها.</p>
    
    <?php if (!empty($import_errors)): ?>
        <div class="message error-message" style="max-height: 200px; overflow-y: auto;">
            <strong>تم العثور على الأخطاء التالية في الملف:</strong><br>
            <?php foreach ($import_errors as $row_key => $errors_for_row): ?>
                - <?php echo "<strong>" . htmlspecialchars($errors_for_row['summary']) . "</strong>: " . htmlspecialchars(implode(', ', $errors_for_row['details'])); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="max-height: 400px; overflow-y: auto;">
        <table class="preview-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رمز العميل (A)</th>
                    <th>اسم العميل (B)</th>
                    <th>النوع (C)</th>
                    <th>المندوب (E)</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; foreach ($preview_data as $row_key => $row): $i++; ?>
                <tr <?php echo isset($import_errors[$row_key]) ? 'style="background-color: #ffe0e0;"' : 'style="background-color: #e6ffe6;"'; ?>>
                    <td><?php echo $i; ?></td>
                    <td><?php echo htmlspecialchars($row['A'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['B'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['C'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['E'] ?? ''); ?></td>
                    <td>
                        <?php if (isset($import_errors[$row_key])): ?>
                            <span style="color:red; font-weight:bold;">خطأ</span>
                        <?php else: echo '<span style="color:green;">صالح</span>'; endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
    <form action="actions/customer_import_confirm.php" method="POST" style="display:inline-block;">
        <?php csrf_input(); ?>
        <input type="hidden" name="confirmed_file_name" value="<?php echo htmlspecialchars($import_file_name); ?>">
        <button type="submit" class="button-link add-btn">تأكيد واستيراد البيانات الصالحة</button>
    </form>
    <a href="index.php?page=customers&action=import" class="button-link" style="background-color:#6c757d;">إلغاء</a>
</div>