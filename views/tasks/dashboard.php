<?php // views/tasks/dashboard.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=tasks&action=add" class="button-link add-btn">إنشاء مهمة جديدة</a>
    <a href="index.php?page=tasks&action=list" class="button-link">عرض كل المهام</a>
</div>

<div class="tasks-container">
    
    <!-- العمود الأول: المهام المكلفة للمستخدم -->
    <div class="tasks-column">
        <h3>📋 مهامي (مطلوبة مني)</h3>
        <div class="task-list">
            <?php if (empty($my_tasks)): ?>
                <div class="empty-state">
                    <p>🎉 لا توجد مهام مطلوبة منك حاليًا. عمل رائع!</p>
                </div>
            <?php else: ?>
                <?php foreach($my_tasks as $task): ?>
                    <?php
                        // تحديد لون بطاقة المهمة بناءً على تاريخ الاستحقاق
                        $due_date_class = '';
                        if ($task['due_date'] && $task['status'] !== 'Completed') {
                            $due_timestamp = strtotime($task['due_date']);
                            $now_timestamp = time();
                            if ($due_timestamp < $now_timestamp) {
                                $due_date_class = 'overdue'; // متأخرة
                            } elseif ($due_timestamp < $now_timestamp + (24 * 3600)) {
                                $due_date_class = 'due-soon'; // قريبة التسليم
                            }
                        }
                    ?>
                    <div class="task-card status-<?php echo str_replace(' ', '-', strtolower($task['status'])); ?> <?php echo $due_date_class; ?>">
                        <a href="index.php?page=tasks&action=view&id=<?php echo $task['task_id']; ?>" class="task-card-link">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="task-meta">
                                <span class="meta-item">
                                    <span class="icon">🗓️</span>
                                    <?php echo htmlspecialchars($task['due_date'] ? date('Y-m-d H:i', strtotime($task['due_date'])) : 'غير محدد'); ?>
                                </span>
                                <span class="badge"><?php echo htmlspecialchars($task['status']); ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- العمود الثاني: المهام التي أنشأها المستخدم للآخرين -->
    <div class="tasks-column">
        <h3>📤 مهام أتابعها (قمت بإنشائها)</h3>
        <div class="task-list">
             <?php if (empty($created_tasks)): ?>
                <div class="empty-state">
                    <p>لم تقم بإنشاء مهام للآخرين بعد. يمكنك البدء من زر "إنشاء مهمة جديدة".</p>
                </div>
            <?php else: ?>
                <?php foreach($created_tasks as $task): ?>
                     <div class="task-card status-<?php echo str_replace(' ', '-', strtolower($task['status'])); ?>">
                        <a href="index.php?page=tasks&action=view&id=<?php echo $task['task_id']; ?>" class="task-card-link">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="task-meta">
                                <span class="meta-item">
                                    <span class="icon">👤</span>
                                    مكلفة لـ: <strong><?php echo htmlspecialchars($task['assigned_to_name']); ?></strong>
                                </span>
                                <span class="badge"><?php echo htmlspecialchars($task['status']); ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =====| بداية كود CSS المدمج |===== -->
<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.actions-bar {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}
.tasks-container { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); 
    gap: 30px; 
    margin-top: 20px; 
}
.tasks-column {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e9ecef;
}
.tasks-column h3 { 
    border-bottom: 2px solid #dee2e6; 
    padding-bottom: 10px; 
    margin-top: 0;
    color: #343a40;
}
.task-list { 
    display: flex; 
    flex-direction: column; 
    gap: 12px;
    max-height: 60vh;
    overflow-y: auto;
    padding-right: 5px; /* لإظهار شريط التمرير بشكل أفضل */
}
.task-card { 
    border: 1px solid #ddd; 
    border-right: 5px solid #ccc; /* الشريط الجانبي للحالة */
    border-radius: 5px; 
    background-color: #fff; 
    transition: box-shadow 0.2s, transform 0.2s;
}
.task-card:hover { 
    box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
    transform: translateY(-2px);
}
.task-card-link { 
    display: block; 
    padding: 15px; 
    text-decoration: none; 
    color: #333; 
}
.task-title { 
    font-weight: bold; 
    margin-bottom: 10px;
    color: #212529;
}
.task-meta { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    font-size: 0.85em; 
    color: #6c757d; 
}
.task-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
}
.task-meta .icon {
    font-size: 1.1em;
}

/* Status Colors */
.task-meta .badge { 
    font-size: 0.9em; 
    padding: 4px 10px; 
    border-radius: 12px; 
    color: white; 
    font-weight: 500;
}
.status-not-started { border-right-color: #6c757d; }
.status-not-started .badge { background-color: #6c757d; }

.status-in-progress { border-right-color: #007bff; }
.status-in-progress .badge { background-color: #007bff; }

.status-completed { border-right-color: #28a745; text-decoration: line-through; opacity: 0.8; }
.status-completed .badge { background-color: #28a745; }

/* Due Date Colors */
.task-card.overdue {
    border-right-color: #dc3545;
    background-color: #fff7f7;
}
.task-card.due-soon {
    border-right-color: #ffc107;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #888;
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
}
</style>