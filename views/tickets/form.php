<?php // views/tickets/form.php 

// تعريف مسار المرفقات لتسهيل استخدامه
define('TICKET_ATTACHMENT_DIR', 'uploads/ticket_attachments/');
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/ticket_save.php" method="POST" enctype="multipart/form-data" class="ticket-form">
    <?php csrf_input(); ?>
        <input type="hidden" name="action" value="<?php echo $action; ?>"> 

    <?php if ($action == 'edit' && !empty($ticket_id)): ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
        
    <?php endif; ?>

    <fieldset>
        <legend>معلومات التذكرة الأساسية</legend>
        <div class="form-group">
            <label for="subject">موضوع التذكرة:</label>
            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($ticket_data['subject'] ?? ''); ?>" placeholder="اكتب عنوانًا موجزًا وواضحًا للمشكلة أو الطلب" required>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="customer_id">العميل (اختياري):</label>
                <select name="customer_id" id="customer_id" class="select2-enable">
                    <option value="">-- لا يوجد عميل محدد --</option>
                    <?php foreach($customers as $customer): ?>
                        <option value="<?php echo $customer['customer_id']; ?>" <?php echo (isset($ticket_data['customer_id']) && $ticket_data['customer_id'] == $customer['customer_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['name'] . ' (' . $customer['customer_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="ticket_type">نوع التذكرة:</label>
                <select name="ticket_type" id="ticket_type" required>
                    <option value="">-- اختر النوع --</option>
                    <?php foreach($ticket_types as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($ticket_data['ticket_type']) && $ticket_data['ticket_type'] == $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($value); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="priority">الأولوية:</label>
                <select name="priority" id="priority" required>
                    <?php foreach($priorities as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($ticket_data['priority']) && $ticket_data['priority'] == $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($value); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="status">الحالة:</label>
                <select name="status" id="status" required>
                     <?php foreach($statuses as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($ticket_data['status']) && $ticket_data['status'] == $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($value); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">وصف المشكلة/الطلب:</label>
            <textarea name="description" id="description" rows="6" placeholder="يرجى تقديم وصف تفصيلي للمشكلة، بما في ذلك أي معلومات قد تكون مفيدة مثل أرقام الفواتير، تواريخ، أو أسماء المنتجات." required><?php echo htmlspecialchars($ticket_data['description'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>الإسناد والمتابعة</legend>
        <div class="form-row">
            <div class="form-group col-md-6">
                
            </div>
            <div class="form-group col-md-6">
                <label for="assign_user">إسناد إلى موظف (اختياري):</label>
                <select name="user_id" id="assign_user" class="select2-enable">
                    <option value="">-- لا يوجد موظف محدد --</option>
                     <?php foreach($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>" <?php echo (isset($ticket_data['assigned_to_user_id']) && $ticket_data['assigned_to_user_id'] == $user['user_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>المرفقات</legend>
        <div class="form-group">
            <label for="attachments">إرفاق ملفات (يمكن تحديد أكثر من ملف):</label>
            <input type="file" name="attachments[]" id="attachments" multiple>
        </div>
        <?php if (!empty($ticket_attachments)): ?>
            <p><strong>المرفقات الحالية:</strong></p>
            <div class="current-attachments">
                <?php foreach($ticket_attachments as $attachment): ?>
                    <div class="attachment-item" id="attachment-<?php echo $attachment['attachment_id']; ?>">
                        <a href="<?php echo TICKET_ATTACHMENT_DIR . htmlspecialchars($attachment['file_path']); ?>" target="_blank">
                            📎 <?php echo htmlspecialchars($attachment['file_name']); ?>
                        </a>
                        <button type="button" class="delete-attachment-btn" data-attachment-id="<?php echo $attachment['attachment_id']; ?>">حذف</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="button-link add-btn"><?php echo $action == 'add' ? 'إنشاء التذكرة' : 'حفظ التغييرات'; ?></button>
        <a href="index.php?page=tickets" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </div>
</form>

<script>
$(document).ready(function() {
    // تفعيل Select2
    $('.select2-enable').select2({
        placeholder: "-- اختر أو ابحث --",
        dir: "rtl",
        width: '100%'
    });

    // حذف مرفق عبر AJAX
    $('.delete-attachment-btn').on('click', function() {
        if (!confirm('هل أنت متأكد من حذف هذا المرفق؟')) return;

        const button = $(this);
        const attachmentId = button.data('attachment-id');
        const attachmentItem = $('#attachment-' + attachmentId);
        const csrfToken = $('input[name="csrf_token"]').val();

        $.ajax({
            url: 'actions/ticket_attachment_delete.php', // ملف جديد يجب إنشاؤه
            type: 'POST',
            data: { 
                attachment_id: attachmentId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    attachmentItem.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert('فشل حذف المرفق: ' + (response.message || 'خطأ غير معروف.'));
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال بالخادم.');
            }
        });
    });
});
</script>

<style>
/* CSS مخصص للنموذج */
.ticket-form fieldset {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    background-color: #fdfdfd;
}
.ticket-form legend {
    font-weight: bold;
    font-size: 1.1em;
    color: var(--primary-color);
    padding: 0 10px;
}
.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.form-group.col-md-6 {
    flex: 1 1 calc(50% - 10px); /* 50% minus half the gap */
}
@media (max-width: 768px) {
    .form-group.col-md-6 {
        flex-basis: 100%;
    }
}
.current-attachments {
    margin-top: 10px;
}
.attachment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f1f1f1;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 5px;
}
.attachment-item a {
    text-decoration: none;
    color: #007bff;
}
.delete-attachment-btn {
    background: none;
    border: 1px solid #dc3545;
    color: #dc3545;
    cursor: pointer;
    border-radius: 4px;
    padding: 3px 8px;
    font-size: 0.8em;
}
.form-actions {
    text-align: left;
    margin-top: 20px;
}
</style>