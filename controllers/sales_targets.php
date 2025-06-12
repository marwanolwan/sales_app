<?php
// controllers/sales_targets.php

require_permission('manage_sales_targets');

$page_title = "أهداف المبيعات الشهرية";
$action = $_GET['action'] ?? 'list';
$target_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selected_year = $_GET['year'] ?? date('Y');
$selected_month = $_GET['month'] ?? date('n');

$months_array = [
    1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
    7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
];

// جلب المندوبين المتاحين للمستخدم الحالي
$sql_reps = "SELECT user_id, full_name, username FROM users WHERE role = 'representative' AND is_active = TRUE";
$params_reps = [];
if ($_SESSION['user_role'] == 'supervisor') {
    $sql_reps .= " AND supervisor_id = ?";
    $params_reps[] = $_SESSION['user_id'];
}
$sql_reps .= " ORDER BY full_name ASC";
$stmt_reps = $pdo->prepare($sql_reps);
$stmt_reps->execute($params_reps);
$representatives = $stmt_reps->fetchAll();
$representatives_map_by_id = array_column($representatives, 'full_name', 'user_id');

$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? "إضافة هدف جديد" : "تعديل الهدف";
        $target_data = null;
        if ($action == 'edit' && $target_id) {
            $stmt = $pdo->prepare("SELECT * FROM sales_targets WHERE target_id = ?");
            $stmt->execute([$target_id]);
            $target_data = $stmt->fetch();
            // Security check: Supervisor can only edit their team's targets
            if ($_SESSION['user_role'] == 'supervisor' && !isset($representatives_map_by_id[$target_data['representative_id']])) {
                 $_SESSION['error_message'] = "ليس لديك صلاحية لتعديل هذا الهدف.";
                 header("Location: index.php?page=sales_targets");
                 exit();
            }
        }
        $view_file = 'views/sales_targets/form.php';
        break;

    case 'import_targets':
        $page_title = "استيراد الأهداف من Excel";
        $view_file = 'views/sales_targets/import.php';
        break;

    case 'list':
    default:
        $sql_targets = "SELECT st.*, u.full_name as representative_name, creator.full_name as creator_name
                        FROM sales_targets st
                        JOIN users u ON st.representative_id = u.user_id
                        LEFT JOIN users creator ON st.created_by_user_id = creator.user_id
                        WHERE st.year = ? AND st.month = ?";
        $params_targets = [$selected_year, $selected_month];

        if ($_SESSION['user_role'] == 'supervisor') {
            $sql_targets .= " AND u.supervisor_id = ?";
            $params_targets[] = $_SESSION['user_id'];
        }
        $sql_targets .= " ORDER BY u.full_name ASC";
        $stmt_targets = $pdo->prepare($sql_targets);
        $stmt_targets->execute($params_targets);
        $targets_list = $stmt_targets->fetchAll();
        $view_file = 'views/sales_targets/list.php';
        break;
}

include 'views/layout.php';