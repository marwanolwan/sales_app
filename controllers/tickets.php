<?php
// controllers/tickets.php

require_login(); // الكل يجب أن يكون مسجلاً دخوله

// افترض وجود صلاحية manage_tickets (يجب إضافتها لقاعدة البيانات)
// سنقوم بالتحقق من الصلاحية داخل كل قسم حسب الحاجة
// require_permission('manage_tickets'); 

$action = $_GET['action'] ?? 'list';
$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

$page_title = "نظام التذاكر والشكاوى";
$view_file = '';
$view_mode = $_GET['view'] ?? 'active';
// تعريف ثوابت لتسهيل القراءة وتوحيد المصطلحات
// يمكن نقلها إلى ملف إعدادات عام في المستقبل
$ticket_types = [
    'Quality Issue' => 'مشكلة في الجودة',
    'Delivery Delay' => 'تأخير في التوصيل',
    'POSM Request' => 'طلب مواد ترويجية',
    'New Customer' => 'عميل جديد بحاجة للتواصل',
    'Rep Issue' => 'مشكلة مع المندوب',
    'Other' => 'أخرى'
];
$priorities = ['Low' => 'عادي', 'Medium' => 'متوسط', 'High' => 'عاجل', 'Critical' => 'حرج'];
$statuses = ['New' => 'جديدة', 'Open' => 'مفتوحة', 'In Progress' => 'قيد المعالجة', 'Resolved' => 'تم الحل', 'Closed' => 'مغلقة'];

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إنشاء تذكرة جديدة' : 'تعديل تذكرة';
        
        // جلب البيانات اللازمة للنموذج
        $customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $departments = $pdo->query("SELECT department_id, name FROM departments ORDER BY name ASC")->fetchAll();
        $users = $pdo->query("SELECT user_id, full_name FROM users WHERE is_active=1 ORDER BY full_name ASC")->fetchAll();

        $ticket_data = null;
        $ticket_attachments = [];
        if ($action == 'edit' && $ticket_id) {
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
            $stmt->execute([$ticket_id]);
            $ticket_data = $stmt->fetch();

            if (!$ticket_data) {
                $_SESSION['error_message'] = "التذكرة المطلوبة غير موجودة.";
                header("Location: index.php?page=tickets");
                exit();
            }
            
            // تحقق من صلاحية التعديل (المنشئ أو المدير فقط)
            if ($ticket_data['created_by_user_id'] != $current_user_id && $current_user_role != 'admin') {
                $_SESSION['error_message'] = "ليس لديك صلاحية لتعديل هذه التذكرة.";
                header("Location: index.php?page=tickets&action=view&id={$ticket_id}");
                exit();
            }

            // جلب المرفقات الحالية
            $stmt_attachments = $pdo->prepare("SELECT * FROM ticket_attachments WHERE ticket_id = ?");
            $stmt_attachments->execute([$ticket_id]);
            $ticket_attachments = $stmt_attachments->fetchAll();
        }
        $view_file = 'views/tickets/form.php';
        break;

    case 'view':
        if (!$ticket_id) { 
            header("Location: index.php?page=tickets"); 
            exit(); 
        }
        $page_title = "تفاصيل التذكرة #" . $ticket_id;
        
        $sql = "SELECT t.*, c.name as customer_name, creator.full_name as creator_name, 
                       assign_user.full_name as assignee_name, assign_dept.name as department_name
                FROM tickets t
                LEFT JOIN customers c ON t.customer_id = c.customer_id
                JOIN users creator ON t.created_by_user_id = creator.user_id
                LEFT JOIN users assign_user ON t.assigned_to_user_id = assign_user.user_id
                LEFT JOIN departments assign_dept ON t.assigned_to_department_id = assign_dept.department_id
                WHERE t.ticket_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ticket_id]);
        $ticket_data = $stmt->fetch();

        if (!$ticket_data) {
            $_SESSION['error_message'] = "التذكرة المطلوبة غير موجودة.";
            header("Location: index.php?page=tickets"); 
            exit();
        }

        // جلب المرفقات والتعليقات
        $attachments_stmt = $pdo->prepare("SELECT * FROM ticket_attachments WHERE ticket_id = ? ORDER BY uploaded_at DESC");
        $attachments_stmt->execute([$ticket_id]);
        $ticket_attachments = $attachments_stmt->fetchAll();
        
        $comments_stmt = $pdo->prepare("SELECT tc.*, u.full_name as user_name FROM ticket_comments tc JOIN users u ON tc.user_id = u.user_id WHERE tc.ticket_id = ? ORDER BY tc.created_at ASC");
        $comments_stmt->execute([$ticket_id]);
        $ticket_comments = $comments_stmt->fetchAll();

        // جلب المستخدمين والأقسام لنموذج الإسناد
        $users = $pdo->query("SELECT user_id, full_name FROM users WHERE is_active=1 ORDER BY full_name ASC")->fetchAll();
        $departments = $pdo->query("SELECT department_id, name FROM departments ORDER BY name ASC")->fetchAll();

        $view_file = 'views/tickets/view.php';
        break;

    case 'list':
    default:
        $page_title = "نظام المتابعة والشكاوي";
        
        // بناء استعلام القائمة مع الفلاتر والصلاحيات
        $sql = "SELECT t.ticket_id, t.subject, t.status, t.priority, t.created_at, c.name as customer_name, creator.full_name as creator_name
                FROM tickets t
                LEFT JOIN customers c ON t.customer_id = c.customer_id
                JOIN users creator ON t.created_by_user_id = creator.user_id
                LEFT JOIN users assignee ON t.assigned_to_user_id = assignee.user_id
                WHERE 1=1";
        $params = [];
        
        // فلتر الصلاحيات: المدير يرى كل شيء، المشرف يرى تذاكر فريقه وتذاكره، الموظف يرى تذاكره فقط
        if ($current_user_role == 'supervisor') {
            $sql .= " AND (t.created_by_user_id = :user_id OR assignee.supervisor_id = :user_id OR t.assigned_to_user_id = :user_id)";
            $params[':user_id'] = $current_user_id;
        } elseif ($current_user_role != 'admin') {
            $sql .= " AND (t.created_by_user_id = :user_id OR t.assigned_to_user_id = :user_id)";
            $params[':user_id'] = $current_user_id;
        }
        if ($view_mode === 'archived') {
            $sql .= " AND t.status = 'Closed'";
            $page_title = "أرشيف التذاكر المغلقة";
        } else {
            $sql .= " AND t.status != 'Closed'";
        }
        
        $sql .= " ORDER BY t.priority = 'Critical' DESC, t.priority = 'High' DESC, t.status = 'New' DESC, t.updated_at DESC";
        $stmt_tickets = $pdo->prepare($sql);
        $stmt_tickets->execute($params);
        $tickets_list = $stmt_tickets->fetchAll();
        
        $view_file = 'views/tickets/list.php';
        break;
}

include 'views/layout.php';