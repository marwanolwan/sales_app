<?php // views/tasks/form.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<form action="actions/task_save.php" method="POST" enctype="multipart/form-data" class="task-form">
    <?php csrf_input(); ?>
    <?php if ($action == 'edit' && !empty($task_id)): ?>
        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
    <?php endif; ?>

    <fieldset>
        <legend>المعلومات الأساسية للمهمة</legend>
        <div class="form-row">
            <div class="form-group col-md-8">
                <label for="title">عنوان المهمة:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task_data['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label for="assigned_to_user_id">تكليف لـ:</label>
                <select name="assigned_to_user_id" id="assigned_to_user_id" class="select2-enable" required>
                    <?php if (count($assignable_users) > 1 || $_SESSION['user_role'] === 'admin'): // إذا كان لديه صلاحية تكليف الآخرين ?>
                        <option value="">-- اختر موظف --</option>
                    <?php endif; ?>

                    <option value="<?php echo $_SESSION['user_id']; ?>" 
                        <?php echo (!isset($task_data) || $task_data['assigned_to_user_id'] == $_SESSION['user_id']) ? 'selected' : ''; ?>>
                        مهمة شخصية (لي)
                    </option>
                    
                    <?php if (count($assignable_users) > 1): ?>
                        <optgroup label="تكليف لموظف آخر">
                            <?php foreach ($assignable_users as $user): 
                                if ($user['user_id'] == $_SESSION['user_id']) continue; ?>
                                <option value="<?php echo $user['user_id']; ?>" <?php echo (isset($task_data['assigned_to_user_id']) && $task_data['assigned_to_user_id'] == $user['user_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['role'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="due_date">تاريخ ووقت التسليم (اختياري):</label>
                <input type="datetime-local" id="due_date" name="due_date" value="<?php echo htmlspecialchars(isset($task_data['due_date']) ? date('Y-m-d\TH:i', strtotime($task_data['due_date'])) : ''); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="status">حالة المهمة:</label>
                <select name="status" id="status">
                    <option value="Not Started" <?php if(isset($task_data) && $task_data['status'] == 'Not Started') echo 'selected';?>>لم تبدأ</option>
                    <option value="In Progress" <?php if(isset($task_data) && $task_data['status'] == 'In Progress') echo 'selected';?>>قيد التنفيذ</option>
                    <option value="Completed" <?php if(isset($task_data) && $task_data['status'] == 'Completed') echo 'selected';?>>منتهية</option>
                    <?php if ($action == 'edit'): // لا يمكن أرشفة مهمة جديدة ?>
                    <option value="Archived" <?php if(isset($task_data) && $task_data['status'] == 'Archived') echo 'selected';?>>مؤرشفة</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">تفاصيل المهمة:</label>
            <textarea name="description" id="description" rows="5" placeholder="أضف وصفًا تفصيليًا للمهمة هنا..."><?php echo htmlspecialchars($task_data['description'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>خطوات التنفيذ (قائمة تحقق)</legend>
        <div id="steps-container">
            <?php if (!empty($task_steps)): ?>
                <?php foreach ($task_steps as $index => $step): ?>
                    <div class="step-row" id="step-row-<?php echo $step['step_id']; ?>">
                        <input type="hidden" name="steps[<?php echo $step['step_id']; ?>][id]" value="<?php echo $step['step_id']; ?>">
                        <input type="text" name="steps[<?php echo $step['step_id']; ?>][title]" value="<?php echo htmlspecialchars($step['step_title']); ?>" placeholder="عنوان الخطوة..." required>
                        <select name="steps[<?php echo $step['step_id']; ?>][is_completed]">
                            <option value="0" <?php echo !$step['is_completed'] ? 'selected' : ''; ?>>قيد الانتظار</option>
                            <option value="1" <?php echo $step['is_completed'] ? 'selected' : ''; ?>>مكتملة</option>
                        </select>
                        <button type="button" class="remove-btn" onclick="this.closest('.step-row').remove()">X</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-step-btn" class="button-link" style="background-color: #6c757d; margin-top: 10px;">+ إضافة خطوة</button>
    </fieldset>

    <fieldset>
        <legend>المرفقات</legend>
        <div class="form-group">
            <label for="attachments">إرفاق ملفات جديدة (يمكن تحديد أكثر من ملف):</label>
            <input type="file" name="attachments[]" id="attachments" multiple>
        </div>
        <?php if (!empty($task_attachments)): ?>
            <p><strong>المرفقات الحالية:</strong></p>
            <div class="current-attachments">
                <?php foreach($task_attachments as $attachment): ?>
                    <div class="attachment-item" id="attachment-<?php echo $attachment['attachment_id']; ?>">
                        <a href="uploads/task_attachments/<?php echo htmlspecialchars($attachment['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($attachment['file_name']); ?></a>
                        <button type="button" class="delete-attachment-btn" data-attachment-id="<?php echo $attachment['attachment_id']; ?>">حذف</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="button-link add-btn"><?php echo $action == 'add' ? 'إنشاء المهمة' : 'حفظ التغييرات'; ?></button>
        <a href="index.php?page=tasks" class="button-link" style="background-color:#6c757d;">إلغاء</a>
    </div>
</form>

<script>
$(document).ready(function() {
    // تفعيل Select2
    $('.select2-enable').select2({
        placeholder: "-- اختر موظف --",
        dir: "rtl",
        width: '100%'
    });

    // إضافة خطوة جديدة
    let stepIndex = <?php echo count($task_steps ?? []); ?>;
    $('#add-step-btn').on('click', function() {
        stepIndex++;
        const newStepHtml = `
            <div class="step-row" id="step-row-new-${stepIndex}">
                <input type="hidden" name="steps[new_${stepIndex}][id]" value="new">
                <input type="text" name="steps[new_${stepIndex}][title]" placeholder="عنوان الخطوة الجديدة..." required>
                <select name="steps[new_${stepIndex}][is_completed]">
                    <option value="0" selected>قيد الانتظار</option>
                    <option value="1">مكتملة</option>
                </select>
                <button type="button" class="remove-btn" onclick="this.closest('.step-row').remove()">X</button>
            </div>
        `;
        $('#steps-container').append(newStepHtml);
    });
    
    // حذف مرفق عبر AJAX
    $('.delete-attachment-btn').on('click', function() {
        if (!confirm('هل أنت متأكد من حذف هذا المرفق؟')) return;

        const button = $(this);
        const attachmentId = button.data('attachment-id');
        const attachmentItem = $('#attachment-' + attachmentId);
        const csrfToken = $('input[name="csrf_token"]').val();

        $.ajax({
            url: 'actions/task_attachment_delete.php', // ملف جديد يجب إنشاؤه
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
.task-form fieldset {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    background-color: #fdfdfd;
}
.task-form legend {
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
.form-group.col-md-4 { flex: 1 1 30%; }
.form-group.col-md-6 { flex: 1 1 45%; }
.form-group.col-md-8 { flex: 1 1 60%; }

#steps-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.step-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.step-row input[type="text"] {
    flex-grow: 1;
}
.remove-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 4px;
    font-weight: bold;
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