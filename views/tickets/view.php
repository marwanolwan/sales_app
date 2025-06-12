<?php 
// views/tickets/view.php 

// تعريف المسارات لتسهيل استخدامها وتجنب الأخطاء
define('TICKET_ATTACHMENT_DIR', 'uploads/ticket_attachments/');
define('COMMENT_ATTACHMENT_DIR', 'uploads/task_comments/'); // افترض أن مرفقات التعليقات هنا
?>

<div class="ticket-view-header">
    <div class="ticket-title-group">
        <h2><?php echo htmlspecialchars($ticket_data['subject']); ?></h2>
        <span class="ticket-id-header">#<?php echo $ticket_data['ticket_id']; ?></span>
    </div>
    <div class="actions-bar">
        <a href="index.php?page=tickets" class="button-link" style="background-color: #6c757d;">العودة للقائمة</a>
        <?php if ($ticket_data['created_by_user_id'] == $_SESSION['user_id'] || $_SESSION['user_role'] === 'admin'): ?>
            <a href="index.php?page=tickets&action=edit&id=<?php echo $ticket_data['ticket_id']; ?>" class="button-link edit-btn">تعديل</a>
        <?php endif; ?>
    </div>
</div>

<div class="ticket-view-container">
    <!-- =====| العمود الأيمن: المحتوى الرئيسي (الوصف والتعليقات) |===== -->
    <div class="ticket-main-content">
        
        <div class="ticket-card">
            <h3>وصف المشكلة/الطلب</h3>
            <div class="description-box">
                <p><?php echo nl2br(htmlspecialchars($ticket_data['description'])); ?></p>
            </div>
            
            <?php if (!empty($ticket_attachments)): ?>
                <h4>المرفقات</h4>
                <div class="attachments-list">
                    <?php foreach ($ticket_attachments as $attachment): ?>
                        <a href="<?php echo TICKET_ATTACHMENT_DIR . htmlspecialchars($attachment['file_path']); ?>" target="_blank" class="attachment-link">
                            📎 <?php echo htmlspecialchars($attachment['file_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="ticket-card">
            <h3>النقاشات والمتابعة</h3>
            <div class="comments-list" id="comments-list">
                <?php if (empty($ticket_comments)): ?>
                    <p class="no-comments">لا توجد تعليقات. كن أول من يبدأ النقاش!</p>
                <?php else: ?>
                    <?php foreach($ticket_comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <strong class="comment-author"><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <div class="comment-body">
                                <p><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="comment-form-container">
                <form id="comment-form" action="actions/ticket_comment_add.php" method="POST" enctype="multipart/form-data">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                    <textarea name="comment_text" placeholder="اكتب تعليقًا أو تحديثًا هنا..." rows="3" required></textarea>
                    <div class="comment-actions">
                        <!-- يمكنك إضافة مرفقات للتعليقات هنا في المستقبل -->
                        <button type="submit" class="button-link add-btn">إضافة تعليق</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- =====| العمود الأيسر: الشريط الجانبي (المعلومات والإجراءات) |===== -->
    <div class="ticket-sidebar">
        
        <div class="ticket-card">
            <h3>معلومات التذكرة</h3>
            <div class="details-list">
                <p><strong>الحالة:</strong> 
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket_data['status'])); ?>">
                        <?php echo htmlspecialchars($statuses[$ticket_data['status']] ?? $ticket_data['status']); ?>
                    </span>
                </p>
                <p><strong>الأولوية:</strong> 
                    <span class="priority-badge priority-<?php echo strtolower($ticket_data['priority']); ?>">
                        <?php echo htmlspecialchars($priorities[$ticket_data['priority']] ?? $ticket_data['priority']); ?>
                    </span>
                </p>
                <p><strong>النوع:</strong> <?php echo htmlspecialchars($ticket_types[$ticket_data['ticket_type']] ?? $ticket_data['ticket_type']); ?></p>
                <p><strong>العميل:</strong> <?php echo htmlspecialchars($ticket_data['customer_name'] ?? 'لا يوجد'); ?></p>
                <hr>
                <p><strong>أنشئت بواسطة:</strong> <?php echo htmlspecialchars($ticket_data['creator_name']); ?></p>
                <p><strong>تاريخ الإنشاء:</strong> <?php echo date('Y-m-d', strtotime($ticket_data['created_at'])); ?></p>
            </div>
        </div>
        
        <div class="ticket-card">
            <h3>الإجراءات والإسناد</h3>
            
            <form id="assignment-form" action="actions/ticket_update_details.php" method="POST">
                <?php csrf_input(); ?>
                <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">

                <div class="form-group">
                    <label for="status-select">تغيير الحالة:</label>
                    <select name="status" id="status-select">
                        <?php foreach($statuses as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($ticket_data['status'] == $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($value); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (in_array($_SESSION['user_role'], ['admin', 'supervisor'])): ?>
                    
                    <div class="form-group">
                        <label for="assign_user">إسناد إلى موظف:</label>
                        <select name="user_id" id="assign_user" class="select2-enable">
                            <option value="">-- لا يوجد --</option>
                             <?php foreach($users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>" <?php echo ($ticket_data['assigned_to_user_id'] == $user['user_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <p><strong>مسندة إلى الموظف:</strong> <?php echo htmlspecialchars($ticket_data['assignee_name'] ?? 'غير مسندة'); ?></p>
                <?php endif; ?>
                
                <button type="submit" class="button-link" style="width: 100%;">حفظ التغييرات</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // تفعيل Select2
    $('.select2-enable').select2({
        placeholder: "-- اختر موظف --",
        dir: "rtl",
        width: '100%'
    });

    //スクロール لأسفل في قائمة التعليقات عند تحميل الصفحة
    const commentsList = document.getElementById('comments-list');
    if(commentsList) {
        commentsList.scrollTop = commentsList.scrollHeight;
    }
});
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.ticket-view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
    margin-bottom: 20px;
}
.ticket-title-group { display: flex; align-items: center; gap: 15px; }
.ticket-id-header { font-size: 1.2em; color: #999; font-weight: bold; }
h2.task-main-title, .ticket-view-header h2 { margin: 0; } /* لتوحيد العناوين */

.ticket-view-container {
    display: grid;
    grid-template-columns: 2.5fr 1fr; /* إعطاء مساحة أكبر للمحتوى */
    gap: 30px;
}
@media (max-width: 1024px) {
    .ticket-view-container { grid-template-columns: 1fr; }
}

.ticket-card {
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.ticket-card h3 {
    margin-top: 0;
    border-bottom: 1px solid #f1f1f1;
    padding-bottom: 10px;
    margin-bottom: 15px;
}
.description-box p {
    margin: 0;
    white-space: pre-wrap; /* للحفاظ على فواصل الأسطر */
    line-height: 1.6;
}
.attachments-list { margin-top: 15px; display: flex; flex-direction: column; gap: 8px; }
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
    background: #f8f9fa;
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
.comment-author { color: var(--primary-color); font-weight: bold; }
.comment-date { font-size: 0.8em; color: #888; }
.comment-body p { margin: 0; line-height: 1.5; }
.no-comments { text-align: center; color: #888; padding: 20px; }

/* Sidebar */
.details-list p { margin: 0 0 12px 0; display: flex; justify-content: space-between; }
.details-list p strong { color: #555; }
#assignment-form .form-group { margin-bottom: 15px; }

/* Badge Styles */
.priority-badge, .status-badge { display: inline-block; padding: 5px 12px; border-radius: 15px; color: white; font-size: 0.8em; font-weight: 500; text-align: center; min-width: 80px; }
.priority-low { background-color: #17a2b8; }
.priority-medium { background-color: #ffc107; color: #212529; }
.priority-high { background-color: #fd7e14; }
.priority-critical { background-color: #dc3545; }
.status-new { background-color: #6c757d; }
.status-open { background-color: #007bff; }
.status-in-progress { background-color: #ffc107; color: #212529; }
.status-resolved { background-color: #28a745; }
.status-closed { background-color: #343a40; }
</style>