<?php
// controllers/monthly_sales.php

require_permission('manage_monthly_sales');

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

$action = $_GET['action'] ?? 'list';
$sale_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// فلاتر العرض
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

$page_title = "المبيعات الشهرية للمندوبين";
$view_file = '';

$months_array = [
    1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
    7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
];

// جلب قائمة المندوبين المتاحين للمستخدم الحالي (للفلاتر والنماذج)
$sql_reps = "SELECT user_id, full_name, username FROM users WHERE role = 'representative' AND is_active = TRUE";
$params_reps = [];
if ($current_user_role == 'supervisor') {
    $sql_reps .= " AND supervisor_id = ?";
    $params_reps[] = $current_user_id;
}
$sql_reps .= " ORDER BY full_name ASC";
$stmt_reps = $pdo->prepare($sql_reps);
$stmt_reps->execute($params_reps);
$representatives = $stmt_reps->fetchAll();

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? "تسجيل مبيعات جديدة" : 'تعديل سجل المبيعات';
        
        $sale_data = null;
        if ($action == 'edit' && $sale_id) {
            $sql_get_sale = "SELECT ms.*, u.full_name as representative_name 
                             FROM monthly_sales ms 
                             JOIN users u ON ms.representative_id = u.user_id 
                             WHERE ms.sale_id = ?";
            $params_get_sale = [$sale_id];

            // التأكد أن المشرف لا يعدل سجل لا يخص فريقه
            if ($current_user_role == 'supervisor') {
                $sql_get_sale .= " AND u.supervisor_id = ?";
                $params_get_sale[] = $current_user_id;
            }

            $stmt = $pdo->prepare($sql_get_sale);
            $stmt->execute($params_get_sale);
            $sale_data = $stmt->fetch();
            
            if (!$sale_data) {
                $_SESSION['error_message'] = "سجل المبيعات غير موجود أو لا تملك صلاحية تعديله.";
                header("Location: index.php?page=monthly_sales");
                exit();
            }
        }
        $view_file = 'views/monthly_sales/form.php';
        break;

    case 'import':
        $page_title = "استيراد المبيعات الشهرية من ملف Excel";
        $view_file = 'views/monthly_sales/import.php';
        break;

    case 'import_preview':
        $page_title = "معاينة استيراد المبيعات الشهرية";
        $preview_data = $_SESSION['import_preview_data']['data'] ?? [];
        $import_errors = $_SESSION['import_preview_data']['errors'] ?? [];
        $has_errors = !empty($import_errors);
        
        if (empty($preview_data)) {
             $_SESSION['error_message'] = "لا توجد بيانات للمعاينة أو انتهت صلاحية الجلسة.";
             header("Location: index.php?page=monthly_sales&action=import");
             exit();
        }
        $view_file = 'views/monthly_sales/import_preview.php';
        break;

    case 'list':
    default:
        $sql_sales_list = "SELECT ms.*, u.full_name as representative_name, rec.full_name as recorder_name
                           FROM monthly_sales ms
                           JOIN users u ON ms.representative_id = u.user_id
                           LEFT JOIN users rec ON ms.recorded_by_user_id = rec.user_id
                           WHERE ms.year = ? AND ms.month = ?";
        $params_list = [$selected_year, $selected_month];

        if ($current_user_role == 'supervisor') {
            $sql_sales_list .= " AND u.supervisor_id = ?";
            $params_list[] = $current_user_id;
        }
        $sql_sales_list .= " ORDER BY u.full_name ASC";
        $stmt_sales = $pdo->prepare($sql_sales_list);
        $stmt_sales->execute($params_list);
        $monthly_sales_list = $stmt_sales->fetchAll();
        $view_file = 'views/monthly_sales/list.php';
        break;
}

include 'views/layout.php';