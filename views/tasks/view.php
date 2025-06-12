<?php // views/tasks/view.php 

define('TASK_ATTACHMENT_DIR', 'uploads/task_attachments/');
define('COMMENT_ATTACHMENT_DIR', 'uploads/task_comments/'); // افترض وجود هذا المجلد

?>

<div class="task-view-header">
    <div class="task-title-section">
        <h2 class="task-main-title"><?php echo htmlspecialchars($task_data['title']); ?></h2>
        <span class="badge status-<?php echo str_replace(' ', '-', strtolower($task_data['status'])); ?>"><?php echo htmlspecialchars($task_data['status']); ?></span>
    </div>
    <div class="task-header-actions">
        <a href="index.php?page=tasks&action=edit&id=<?php echo $task_data['task_id']; ?>" class="button-link edit-btn">تعديل المهمة</a>
        <a href="index.php?page=tasks" class="button-link" style="background-color: #6c757d;">العودة للقائمة</a>
    </div>
</div>

<div class="task-view-container">
    <!-- =====| العمود الأيمن: التفاصيل والخطوات والمرفقات |===== -->
    <div class="task-main-content">
        
        <div class="task-details-card">
            <h3>تفاصيل المهمة</h3>
            <div class="details-grid">
                <div><strong>أنشئت بواسطة:</strong> <?php echo htmlspecialchars($task_data['creator_name']); ?></div>
                <div><strong>مكلفة لـ:</strong> <?php echo htmlspecialchars($task_data['assignee_name']); ?></div>
                <div><strong>تاريخ الإنشاء:</strong> <?php echo date('Y-m-d H:i', strtotime($task_data['created_at'])); ?></div>
                <div><strong>تاريخ الاستحقاق:</strong> 
                    <span class="<?php echo ($task_data['due_date'] && strtotime($task_data['due_date']) < time() && $task_data['status'] !== 'Completed') ? 'text-danger' : ''; ?>">
                        <?php echo $task_data['due_date'] ? date('Y-m-d H:i', strtotime($task_data['due_date'])) : 'غير محدد'; ?>
                    </span>
                </div>
            </div>
            <?php if (!empty($task_data['description'])): ?>
                <div class="description-box">
                    <strong>الوصف:</strong>
                    <p><?php echo nl2br(htmlspecialchars($task_data['description'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($task_steps)): ?>
        <div class="task-details-card">
            <h3>قائمة الخطوات (Checklist)</h3>
            <div class="steps-list">
                <?php foreach ($task_steps as $step): ?>
                    <div class="step-item">
                        <input type="checkbox" id="step-<?php echo $step['step_id']; ?>" data-step-id="<?php echo $step['step_id']; ?>" <?php echo $step['is_completed'] ? 'checked' : ''; ?>>
                        <label for="step-<?php echo $step['step_id']; ?>" class="<?php echo $step['is_completed'] ? 'completed' : ''; ?>"><?php echo htmlspecialchars($step['step_title']); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($task_attachments)): ?>
        <div class="task-details-card">
            <h3>المرفقات الأساسية</h3>
            <div class="attachments-list">
                <?php foreach ($task_attachments as $attachment): ?>
                    <a href="<?php echo TASK_ATTACHMENT_DIR . htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="attachment-link">
                        <span class="icon">📎</span> <?php echo htmlspecialchars($attachment['file_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- =====| العمود الأيسر: التعليقات والنشاط |===== -->
    <div class="task-sidebar-content">
        <div class="task-details-card">
            <h3>تغيير الحالة</h3>
            <form id="status-update-form" action="actions/task_status_update.php" method="POST">
                <input type="hidden" name="task_id" value="<?php echo $task_data['task_id']; ?>">
                <?php csrf_input(); ?>
                <select name="status" id="status-select" onchange="this.form.submit()">
                    <option value="Not Started" <?php if($task_data['status'] == 'Not Started') echo 'selected';?>>لم تبدأ</option>
                    <option value="In Progress" <?php if($task_data['status'] == 'In Progress') echo 'selected';?>>قيد التنفيذ</option>
                    <option value="Completed" <?php if($task_data['status'] == 'Completed') echo 'selected';?>>منتهية</option>
                    <option value="Archived" <?php if($task_data['status'] == 'Archived') echo 'selected';?>>مؤرشفة</option>
                </select>
            </form>
        </div>

        <div class="task-details-card">
            <h3>النقاشات والتعليقات</h3>
            <div class="comments-list" id="comments-list">
                <?php if (empty($task_comments)): ?>
                    <p class="no-comments">لا توجد تعليقات. كن أول من يبدأ النقاش!</p>
                <?php else: ?>
                    <?php foreach($task_comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <strong class="comment-author"><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <div class="comment-body">
                                <?php if (!empty($comment['comment_text'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($comment['attachment_path'])): ?>
                                    <a href="<?php echo COMMENT_ATTACHMENT_DIR . htmlspecialchars($comment['attachment_path']); ?>" target="_blank" class="attachment-link">
                                        <span class="icon">📎</span> عرض المرفق
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="comment-form-container">
                <form id="comment-form" action="actions/task_comment_add.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="task_id" value="<?php echo $task_data['task_id']; ?>">
                    <?php csrf_input(); ?>
                    <textarea name="comment_text" placeholder="اكتب تعليقك هنا..." rows="3"></textarea>
                    <div class="comment-actions">
                        <label for="comment_attachment" class="attachment-label">إرفاق ملف</label>
                        <input type="file" name="comment_attachment" id="comment_attachment">
                        <button type="submit" class="button-link add-btn">إضافة تعليق</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // تحديث حالة الخطوة عبر AJAX
    $('.step-item input[type="checkbox"]').on('change', function() {
        const stepId = $(this).data('step-id');
        const isCompleted = $(this).is(':checked') ? 1 : 0;
        const label = $(this).next('label');
        const csrfToken = $('#status-update-form input[name="csrf_token"]').val();

        $.ajax({
            url: 'actions/task_step_update.php', // ملف جديد يجب إنشاؤه
            type: 'POST',
            data: {
                step_id: stepId,
                is_completed: isCompleted,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    label.toggleClass('completed', isCompleted);
                } else {
                    alert('فشل تحديث الخطوة: ' + (response.message || 'خطأ غير معروف'));
                    // إعادة حالة الـ checkbox إلى حالتها السابقة عند الفشل
                    $(this).prop('checked', !isCompleted);
                }
            }.bind(this),
            error: function() {
                alert('حدث خطأ في الاتصال بالخادم.');
                $(this).prop('checked', !isCompleted);
            }.bind(this)
        });
    });

    //スクロールを一番下に
    const commentsList = document.getElementById('comments-list');
    commentsList.scrollTop = commentsList.scrollHeight;
});
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.task-view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
    margin-bottom: 20px;
}
.task-title-section { display: flex; align-items: center; gap: 15px; }
.task-main-title { margin: 0; color: #333; }
.task-view-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}
@media (max-width: 1024px) {
    .task-view-container { grid-template-columns: 1fr; }
}
.task-details-card {
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}
.description-box {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f1f1f1;
}
.description-box p { margin-top: 5px; white-space: pre-wrap; }
.text-danger { color: #dc3545; font-weight: bold; }
/* Steps List */
.steps-list .step-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dotted #eee;
}
.steps-list .step-item:last-child { border-bottom: none; }
.step-item label.completed { text-decoration: line-through; color: #888; }
/* Attachments List */
.attachments-list { display: flex; flex-direction: column; gap: 8px; }
.attachment-link { 
    text-decoration: none; color: #007bff; background-color: #f1f1f1; 
    padding: 8px 12px; border-radius: 4px; transition: background-color 0.2s;
}
.attachment-link:hover { background-color: #e2e6ea; }
/* Comments Section */
.comments-list {
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 15px;
}
.comment-item {
    margin-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 10px;
}
.comment-item:last-child { border-bottom: none; margin-bottom: 0; }
.comment-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
.comment-author { color: var(--primary-color); }
.comment-date { font-size: 0.8em; color: #888; }
.comment-body p { margin: 0; }
.no-comments { text-align: center; color: #888; padding: 20px; }
/* Comment Form */
.comment-form-container textarea { width: 100%; margin-bottom: 10px; }
.comment-actions { display: flex; justify-content: space-between; align-items: center; }
.attachment-label {
    cursor: pointer;
    color: #007bff;
    font-size: 0.9em;
}
#comment_attachment { display: none; }

/* Badge Styles */
.badge {
    padding: 4px 10px; border-radius: 12px; color: white;
    font-weight: 500; font-size: 1em;
}
.status-not-started { background-color: #6c757d; }
.status-in-progress { background-color: #007bff; }
.status-completed { background-color: #28a745; }
.status-archived { background-color: #343a40; }
</style>