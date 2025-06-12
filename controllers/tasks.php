<?php
// controllers/tasks.php

require_login(); // جميع أقسام المهام تتطلب تسجيل الدخول

// سنفترض أن أي مستخدم مسجل دخوله يمكنه عرض لوحة مهامه
// الصلاحيات المحددة سيتم التحقق منها داخل كل قسم عند الحاجة

$action = $_GET['action'] ?? 'dashboard'; // الافتراضي هو لوحة التحكم
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

$page_title = "إدارة المهام";
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إنشاء مهمة جديدة' : 'تعديل المهمة';
        
        $task_data = null;
        $task_steps = [];
        $task_attachments = [];

        if ($action == 'edit') {
            if (!$task_id) {
                $_SESSION['error_message'] = "معرف المهمة غير موجود.";
                header("Location: index.php?page=tasks");
                exit();
            }

            // جلب بيانات المهمة الأساسية
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE task_id = ?");
            $stmt->execute([$task_id]);
            $task_data = $stmt->fetch();

            if (!$task_data) {
                $_SESSION['error_message'] = "المهمة غير موجودة.";
                header("Location: index.php?page=tasks");
                exit();
            }

            // التحقق من صلاحية التعديل (إما أن تكون المنشئ أو المكلف)
            if ($task_data['created_by_user_id'] != $current_user_id && $task_data['assigned_to_user_id'] != $current_user_id && $current_user_role != 'admin') {
                 $_SESSION['error_message'] = "ليس لديك صلاحية لتعديل هذه المهمة.";
                 header("Location: index.php?page=tasks&action=view&id={$task_id}");
                 exit();
            }

            // جلب الخطوات والمرفقات
            $stmt_steps = $pdo->prepare("SELECT * FROM task_steps WHERE task_id = ? ORDER BY order_index ASC");
            $stmt_steps->execute([$task_id]);
            $task_steps = $stmt_steps->fetchAll();
            
            $stmt_attachments = $pdo->prepare("SELECT * FROM task_attachments WHERE task_id = ?");
            $stmt_attachments->execute([$task_id]);
            $task_attachments = $stmt_attachments->fetchAll();
        }

        // جلب قائمة المستخدمين الذين يمكن تكليفهم
        $assignable_users_sql = "SELECT user_id, full_name, role FROM users WHERE is_active = TRUE ";
        if ($current_user_role == 'supervisor') {
            // المشرف يمكنه تكليف نفسه أو فريقه
            $assignable_users_sql .= " AND (supervisor_id = {$current_user_id} OR user_id = {$current_user_id})";
        } elseif ($current_user_role == 'representative' || $current_user_role == 'promoter') {
            // الموظف العادي يمكنه تكليف نفسه فقط (مهمة شخصية)
            $assignable_users_sql .= " AND user_id = {$current_user_id}";
        }
        // المدير (admin) يرى الجميع (لا يوجد شرط إضافي)
        $assignable_users_sql .= " ORDER BY full_name ASC";
        $assignable_users = $pdo->query($assignable_users_sql)->fetchAll();

        $view_file = 'views/tasks/form.php';
        break;

    case 'view':
        if (!$task_id) {
            header("Location: index.php?page=tasks"); exit();
        }
        $page_title = "تفاصيل المهمة";
        
        // جلب بيانات المهمة مع أسماء المنشئ والمكلف
        $sql = "SELECT t.*, creator.full_name as creator_name, assign.full_name as assignee_name
                FROM tasks t
                JOIN users creator ON t.created_by_user_id = creator.user_id
                JOIN users assign ON t.assigned_to_user_id = assign.user_id
                WHERE t.task_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$task_id]);
        $task_data = $stmt->fetch();

        if (!$task_data) {
            $_SESSION['error_message'] = "المهمة غير موجودة.";
            header("Location: index.php?page=tasks"); exit();
        }

        // التحقق من صلاحية العرض
        if ($task_data['created_by_user_id'] != $current_user_id && $task_data['assigned_to_user_id'] != $current_user_id && $current_user_role != 'admin') {
             $_SESSION['error_message'] = "ليس لديك صلاحية لعرض هذه المهمة.";
             header("Location: index.php?page=tasks");
             exit();
        }

        // جلب الخطوات، المرفقات، والتعليقات
        $stmt_steps = $pdo->prepare("SELECT * FROM task_steps WHERE task_id = ? ORDER BY order_index ASC");
        $stmt_steps->execute([$task_id]);
        $task_steps = $stmt_steps->fetchAll();

        $stmt_attachments = $pdo->prepare("SELECT * FROM task_attachments WHERE task_id = ? ORDER BY uploaded_at DESC");
        $stmt_attachments->execute([$task_id]);
        $task_attachments = $stmt_attachments->fetchAll();
        
        $stmt_comments = $pdo->prepare("SELECT tc.*, u.full_name as user_name FROM task_comments tc JOIN users u ON tc.user_id = u.user_id WHERE tc.task_id = ? ORDER BY tc.created_at ASC");
        $stmt_comments->execute([$task_id]);
        $task_comments = $stmt_comments->fetchAll();

        $view_file = 'views/tasks/view.php';
        break;

    case 'list':
        $page_title = "قائمة كل المهام";
        
        // الفلاتر
        $filter_status = $_GET['status'] ?? 'all';
        $filter_assignee = $_GET['assignee'] ?? 'all';
        $filter_search = trim($_GET['search'] ?? '');

        $sql = "SELECT t.*, creator.full_name as creator_name, assign.full_name as assignee_name
                FROM tasks t
                JOIN users creator ON t.created_by_user_id = creator.user_id
                JOIN users assign ON t.assigned_to_user_id = assign.user_id
                WHERE 1=1"; // للبدء
        $params = [];
        
        // تطبيق فلاتر الصلاحيات
        if ($current_user_role == 'supervisor') {
            $sql .= " AND (assign.supervisor_id = :user_id OR t.assigned_to_user_id = :user_id OR t.created_by_user_id = :user_id)";
            $params[':user_id'] = $current_user_id;
        } elseif ($current_user_role != 'admin') {
            $sql .= " AND (t.assigned_to_user_id = :user_id OR t.created_by_user_id = :user_id)";
            $params[':user_id'] = $current_user_id;
        }

        // تطبيق الفلاتر من النموذج
        if ($filter_status !== 'all') {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filter_status;
        }
        if ($filter_assignee !== 'all' && is_numeric($filter_assignee)) {
            $sql .= " AND t.assigned_to_user_id = :assignee";
            $params[':assignee'] = $filter_assignee;
        }
        if (!empty($filter_search)) {
            $sql .= " AND t.title LIKE :search";
            $params[':search'] = "%{$filter_search}%";
        }
        
        $sql .= " ORDER BY t.due_date IS NULL, t.due_date ASC, t.created_at DESC";
        
        $stmt_tasks = $pdo->prepare($sql);
        $stmt_tasks->execute($params);
        $tasks_list = $stmt_tasks->fetchAll();
        
        // جلب المستخدمين لفلتر التكليف
        $all_users = $pdo->query("SELECT user_id, full_name FROM users WHERE is_active = TRUE ORDER BY full_name ASC")->fetchAll();
        
        $view_file = 'views/tasks/list.php';
        break;

    case 'dashboard':
    default:
        $page_title = "لوحة تحكم المهام";
        
        // جلب المهام المكلفة للمستخدم الحالي (غير المؤرشفة)
        $stmt_my_tasks = $pdo->prepare("SELECT * FROM tasks WHERE assigned_to_user_id = ? AND status != 'Archived' ORDER BY due_date IS NULL, due_date ASC");
        $stmt_my_tasks->execute([$current_user_id]);
        $my_tasks = $stmt_my_tasks->fetchAll();
        
        // جلب المهام التي أنشأها المستخدم للآخرين (غير المؤرشفة)
        $stmt_created_tasks = $pdo->prepare(
            "SELECT t.*, u.full_name as assigned_to_name 
             FROM tasks t 
             JOIN users u ON t.assigned_to_user_id = u.user_id 
             WHERE t.created_by_user_id = ? 
             AND t.created_by_user_id != t.assigned_to_user_id 
             AND t.status != 'Archived' 
             ORDER BY t.created_at DESC"
        );
        $stmt_created_tasks->execute([$current_user_id]);
        $created_tasks = $stmt_created_tasks->fetchAll();
        
        $view_file = 'views/tasks/dashboard.php';
        break;
}

// أخيرًا، قم بتضمين القالب الرئيسي الذي سيعرض الواجهة المحددة
include 'views/layout.php';