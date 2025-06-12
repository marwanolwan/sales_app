<?php // views/tasks/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=tasks&action=dashboard" class="button-link" style="background-color: #6c757d;">العودة للوحة التحكم</a>
    <a href="index.php?page=tasks&action=add" class="button-link add-btn">إنشاء مهمة جديدة</a>
</div>

<!-- =====| بداية قسم الفلاتر |===== -->
<div class="filter-container">
    <form action="index.php" method="GET">
        <input type="hidden" name="page" value="tasks">
        <input type="hidden" name="action" value="list">
        
        <div class="filter-group">
            <label for="filter_status">الحالة:</label>
            <select name="status" id="filter_status">
                <option value="all">كل الحالات</option>
                <option value="Not Started" <?php echo ($filter_status == 'Not Started') ? 'selected' : ''; ?>>لم تبدأ</option>
                <option value="In Progress" <?php echo ($filter_status == 'In Progress') ? 'selected' : ''; ?>>قيد التنفيذ</option>
                <option value="Completed" <?php echo ($filter_status == 'Completed') ? 'selected' : ''; ?>>منتهية</option>
                <option value="Archived" <?php echo ($filter_status == 'Archived') ? 'selected' : ''; ?>>مؤرشفة</option>
            </select>
        </div>

        <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'supervisor'): ?>
        <div class="filter-group">
            <label for="filter_assignee">مكلفة لـ:</label>
            <select name="assignee" id="filter_assignee" class="select2-enable">
                <option value="all">كل الموظفين</option>
                <?php foreach ($all_users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>" <?php echo ($filter_assignee == $user['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="filter-group">
            <label for="filter_search">بحث بالعنوان:</label>
            <input type="text" name="search" id="filter_search" placeholder="كلمة مفتاحية..." value="<?php echo htmlspecialchars($filter_search); ?>">
        </div>

        <div class="filter-group">
            <button type="submit" class="button-link">تطبيق الفلتر</button>
            <a href="index.php?page=tasks&action=list" class="button-link" style="background-color: #777;">مسح الفلتر</a>
        </div>
    </form>
</div>

<!-- =====| بداية جدول المهام |===== -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>العنوان</th>
                <th>أنشئت بواسطة</th>
                <th>مكلفة لـ</th>
                <th>تاريخ الاستحقاق</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tasks_list)): ?>
                <tr><td colspan="6">لا توجد مهام تطابق معايير البحث الحالية.</td></tr>
            <?php else: ?>
                <?php foreach ($tasks_list as $task): ?>
                    <?php
                        // تحديد لون الصف بناء على الحالة والتأخير
                        $row_class = '';
                        if ($task['status'] === 'Completed' || $task['status'] === 'Archived') {
                            $row_class = 'task-done';
                        } elseif ($task['due_date'] && strtotime($task['due_date']) < time()) {
                            $row_class = 'task-overdue';
                        }
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td>
                            <a href="index.php?page=tasks&action=view&id=<?php echo $task['task_id']; ?>" class="task-title-link">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($task['creator_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['assignee_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date'] ? date('Y-m-d H:i', strtotime($task['due_date'])) : 'غير محدد'); ?></td>
                        <td>
                            <span class="badge status-<?php echo str_replace(' ', '-', strtolower($task['status'])); ?>">
                                <?php echo htmlspecialchars($task['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=tasks&action=view&id=<?php echo $task['task_id']; ?>" class="button-link">عرض</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // تفعيل Select2 على الفلتر المطلوب
    $('.select2-enable').select2({
        placeholder: "-- اختر موظف --",
        dir: "rtl",
        width: '200px' // تحديد عرض مناسب
    });
});
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.filter-container {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}
.filter-container form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    margin-bottom: 5px;
    font-size: 0.9em;
    color: #555;
}
.filter-group select, .filter-group input[type="text"] {
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
    min-width: 180px;
}

/* Table Styles */
.table-container {
    overflow-x: auto;
}
.task-title-link {
    font-weight: bold;
    color: var(--primary-color);
    text-decoration: none;
}
.task-title-link:hover {
    text-decoration: underline;
}

/* Row Status Styles */
tr.task-done {
    opacity: 0.7;
    background-color: #f1f1f1;
}
tr.task-done .task-title-link {
    text-decoration: line-through;
}
tr.task-overdue {
    background-color: #fff2f2;
}
tr.task-overdue td:first-child {
    border-left: 4px solid #dc3545; /* استخدام border-left مع RTL */
}

/* Badge Styles */
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    color: white;
    font-weight: 500;
    font-size: 0.85em;
    white-space: nowrap;
}
.status-not-started { background-color: #6c757d; }
.status-in-progress { background-color: #007bff; }
.status-completed { background-color: #28a745; }
.status-archived { background-color: #343a40; }
</style>