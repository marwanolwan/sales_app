<?php
// users.php (The Controller)

// 1. التحقق من الصلاحيات
require_permission('manage_users');

// 2. تحديد الإجراء المطلوب
$action = $_GET['action'] ?? 'list';
$user_id_to_edit = $_GET['id'] ?? null;

$page_title = "إدارة المستخدمين";
$view_file = '';

// 3. جلب البيانات اللازمة للـ Views
$roles_translation = [
    'admin' => 'مدير النظام',
    'supervisor' => 'مشرف مبيعات',
    'representative' => 'مندوب مبيعات',
    'promoter' => 'مروج مبيعات'
];

// 4. توجيه الطلب إلى الـ View المناسب
switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة مستخدم جديد' : 'تعديل بيانات المستخدم';
        
        // جلب البيانات اللازمة للنموذج
        $regions = $pdo->query("SELECT region_id, name FROM regions ORDER BY name ASC")->fetchAll();
        $supervisors = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'supervisor' AND is_active = TRUE ORDER BY full_name ASC")->fetchAll();
        
        $user_data = null;
        if ($action == 'edit' && $user_id_to_edit) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id_to_edit]);
            $user_data = $stmt->fetch();
            if (!$user_data) {
                $_SESSION['error_message'] = "المستخدم غير موجود.";
                header("Location: index.php?page=users");
                exit();
            }
        }
        $view_file = 'views/users/form.php';
        break;

    case 'list':
    default:
        $page_title = "إدارة المستخدمين";
        
        // **تحسين الأداء (إصلاح N+1 Query)**
        // جلب كل البيانات المطلوبة في استعلام واحد باستخدام JOIN
        $stmt_users = $pdo->query("SELECT u.*, r.name as region_name, sup.full_name as supervisor_name 
                                   FROM users u 
                                   LEFT JOIN regions r ON u.region_id = r.region_id
                                   LEFT JOIN users sup ON u.supervisor_id = sup.user_id
                                   ORDER BY u.user_id ASC");
        $users = $stmt_users->fetchAll();
        
        $view_file = 'views/users/list.php';
        break;
}

// 5. عرض القالب الرئيسي وتمرير الـ View إليه
include 'views/layout.php';