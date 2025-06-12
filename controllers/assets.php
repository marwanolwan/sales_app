<?php
// controllers/assets.php

require_permission('manage_assets'); // افترض وجود هذه الصلاحية

$action = $_GET['action'] ?? 'list';
$asset_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$type_id = isset($_GET['type_id']) ? (int)$_GET['type_id'] : null;

$page_title = "إدارة الأصول الثابتة";
$view_file = '';

// تعريف مصفوفة الحالات لتمريرها للواجهات
$statuses = [
    'In Warehouse' => 'في المخزن', 
    'With Customer' => 'لدى العميل', 
    'Under Maintenance' => 'تحت الصيانة', 
    'Retired' => 'خارج الخدمة'
];

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة أصل جديد' : 'تعديل بيانات الأصل';
        
        $asset_types = $pdo->query("SELECT * FROM asset_types ORDER BY type_name ASC")->fetchAll();
        $customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status='active' ORDER BY name ASC")->fetchAll();
        
        $asset_data = null;
        if ($action == 'edit' && $asset_id) {
            $stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ?");
            $stmt->execute([$asset_id]);
            $asset_data = $stmt->fetch();
            if (!$asset_data) {
                $_SESSION['error_message'] = "الأصل المطلوب غير موجود.";
                header("Location: index.php?page=assets");
                exit();
            }
        }
        $view_file = 'views/assets/form.php';
        break;

    case 'types':
        $page_title = "إدارة أنواع الأصول";
        $stmt = $pdo->query("
            SELECT at.*, COUNT(a.asset_id) as asset_count
            FROM asset_types at
            LEFT JOIN assets a ON at.type_id = a.type_id
            GROUP BY at.type_id
            ORDER BY at.type_name ASC
        ");
        $asset_types = $stmt->fetchAll();
        $view_file = 'views/assets/types_list.php';
        break;
    
    case 'add_type':
    case 'edit_type':
         $page_title = ($action == 'add_type') ? 'إضافة نوع أصل جديد' : 'تعديل نوع الأصل';
         $type_data = null;
         if ($action == 'edit_type') {
             if (!$type_id) {
                $_SESSION['error_message'] = "معرف النوع غير محدد.";
                header("Location: index.php?page=assets&action=types");
                exit();
             }
             $stmt = $pdo->prepare("SELECT * FROM asset_types WHERE type_id = ?");
             $stmt->execute([$type_id]);
             $type_data = $stmt->fetch();
              if (!$type_data) {
                $_SESSION['error_message'] = "نوع الأصل غير موجود.";
                header("Location: index.php?page=assets&action=types");
                exit();
            }
         }
         $view_file = 'views/assets/types_form.php';
         break;

    case 'list':
    default:
        $page_title = "قائمة الأصول الثابتة";

        // ======================| بداية التصحيح |======================
        // تعريف متغيرات الفلترة وتمريرها للواجهة
        $filter_status = $_GET['status'] ?? 'all';
        $filter_type = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 'all';
        $filter_search = trim($_GET['search'] ?? '');

        // جلب أنواع الأصول اللازمة لقائمة الفلتر في الواجهة
        $asset_types_for_filter = $pdo->query("SELECT type_id, type_name FROM asset_types ORDER BY type_name ASC")->fetchAll();

        // بناء الاستعلام مع الفلاتر
        $sql = "SELECT a.*, at.type_name, c.name as customer_name, c.customer_code
                FROM assets a
                JOIN asset_types at ON a.type_id = at.type_id
                LEFT JOIN customers c ON a.customer_id = c.customer_id
                WHERE 1=1";
        $params = [];

        if ($filter_status !== 'all') {
            $sql .= " AND a.status = :status";
            $params[':status'] = $filter_status;
        }
        if ($filter_type !== 'all') {
            $sql .= " AND a.type_id = :type_id";
            $params[':type_id'] = $filter_type;
        }
        if (!empty($filter_search)) {
            $sql .= " AND (a.serial_number LIKE :search OR c.name LIKE :search OR c.customer_code LIKE :search)";
            $params[':search'] = "%{$filter_search}%";
        }

        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $assets_list = $stmt->fetchAll();
        // ======================| نهاية التصحيح |======================
        
        $view_file = 'views/assets/list.php';
        break;

}

// أخيرًا، قم بتضمين القالب الرئيسي الذي سيعرض الواجهة المحددة
include 'views/layout.php';