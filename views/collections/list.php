<?php // views/collections/list.php ?>
<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; // سننشئ ملف فلاتر مشترك ?>

<div class="actions-bar">
    <a href="index.php?page=collections&action=add&year=<?php echo $filter_year; ?>&month=<?php echo $filter_month; ?>" class="button-link add-btn">إضافة سجل تحصيل</a>
    <a href="index.php?page=collections&action=import" class="button-link" style="background-color:#fd7e14;">استيراد من Excel</a>

</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>المندوب</th>
                <th>المبلغ المحصل</th>
                <th>ملاحظات</th>
                <th>سجل بواسطة</th>
                <th>تاريخ التسجيل</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($collections as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['representative_name']); ?></td>
                <td><?php echo number_format($item['collection_amount'], 2); ?></td>
                <td><?php echo nl2br(htmlspecialchars($item['notes'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($item['recorder_name'] ?? 'N/A'); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($item['recorded_at'])); ?></td>
                <td>
                    <a href="index.php?page=collections&action=edit&id=<?php echo $item['collection_id']; ?>" class="button-link edit-btn">تعديل</a>
                    <form action="actions/collection_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                        <input type="hidden" name="collection_id" value="<?php echo $item['collection_id']; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link delete-btn">حذف</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($collections)): ?>
            <tr><td colspan="6">لا توجد بيانات تحصيل مسجلة لهذه الفترة.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =====| بداية قسم الحذف الجماعي |===== -->
<div class="card danger-zone" style="margin-top: 40px;">
    <div class="card-header">
        <h3><i class="fas fa-exclamation-triangle"></i> حذف تحصيلات شهر كامل</h3>
    </div>
    <div class="card-body">
        <p><strong>تحذير:</strong> هذا الإجراء سيقوم بحذف جميع سجلات التحصيل للشهر والسنة المحددين في الفلاتر أعلاه. لا يمكن التراجع عن هذا الإجراء.</p>
        <form action="actions/collection_delete_monthly.php" method="POST" onsubmit="return confirm('هل أنت متأكد تمامًا من رغبتك في حذف جميع سجلات التحصيل لهذه الفترة؟ هذا الإجراء نهائي ولا يمكن التراجع عنه.');">
            <?php csrf_input(); ?>
            <input type="hidden" name="year" value="<?php echo $filter_year; ?>">
            <input type="hidden" name="month" value="<?php echo $filter_month; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="delete_rep_id">اختر المندوب (أو اترك "الكل" لحذف تحصيلات جميع المندوبين):</label>
                    <select name="representative_id" id="delete_rep_id">
                        <option value="all">كل المندوبين (ضمن صلاحياتك)</option>
                        <?php foreach($representatives as $rep): ?>
                            <option value="<?php echo $rep['user_id']; ?>">
                                <?php echo htmlspecialchars($rep['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="button-link delete-btn">حذف تحصيلات الفترة المحددة</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- =====| نهاية قسم الحذف الجماعي |===== -->
<style>
/* يمكنك نقل هذا إلى style.css */
.danger-zone {
    border-color: var(--danger-color);
    background-color: #f8d7da;
}
.danger-zone .card-header {
    background-color: #f5c6cb;
    color: #721c24;
}
.danger-zone p {
    color: #721c24;
}
</style>