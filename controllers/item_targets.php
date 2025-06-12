<?php
// controllers/item_targets.php

require_permission('manage_item_targets');

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

$action = $_GET['action'] ?? 'list';
$item_target_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// فلاتر العرض
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$selected_rep_filter = $_GET['representative_id_filter'] ?? 'all';

$page_title = "أهداف مبيعات الأصناف (كميات)";
$view_file = '';

$months_map = [
    1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
    7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
];

$sql_reps = "SELECT user_id, full_name, username FROM users WHERE role = 'representative' AND is_active = TRUE";
$params_reps = [];
if ($current_user_role == 'supervisor') {
    $sql_reps .= " AND supervisor_id = ?";
    $params_reps[] = $current_user_id;
}
$sql_reps .= " ORDER BY full_name ASC";
$stmt_reps = $pdo->prepare($sql_reps);
$stmt_reps->execute($params_reps);
$representatives = $stmt_reps->fetchAll(PDO::FETCH_ASSOC);

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة هدف صنف جديد' : 'تعديل هدف الصنف';
        
        $products_list = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = TRUE ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $item_target_data = null;
        if ($action == 'edit' && $item_target_id) {
            $sql_get_target = "SELECT it.* FROM item_sales_targets it JOIN users u ON it.representative_id = u.user_id WHERE it.item_target_id = ?";
            $params_get_target = [$item_target_id];

            if ($current_user_role == 'supervisor') {
                $sql_get_target .= " AND u.supervisor_id = ?";
                $params_get_target[] = $current_user_id;
            }

            $stmt = $pdo->prepare($sql_get_target);
            $stmt->execute($params_get_target);
            $item_target_data = $stmt->fetch();
            
            if (!$item_target_data) {
                $_SESSION['error_message'] = "الهدف المطلوب غير موجود أو لا تملك صلاحية تعديله.";
                header("Location: index.php?page=item_targets");
                exit();
            }
        }
        $view_file = 'views/item_targets/form.php';
        break;

    case 'list':
    default:
        $sql_targets_list = "SELECT it.*, u.full_name as representative_name, p.name as product_name, p.product_code 
                             FROM item_sales_targets it
                             JOIN users u ON it.representative_id = u.user_id
                             JOIN products p ON it.product_id = p.product_id
                             WHERE it.year = ? AND it.month = ?";
        $params_list = [$selected_year, $selected_month];

        if ($selected_rep_filter !== 'all' && is_numeric($selected_rep_filter)) {
            $sql_targets_list .= " AND it.representative_id = ?";
            $params_list[] = (int)$selected_rep_filter;
        } elseif ($current_user_role == 'supervisor') {
            $sql_targets_list .= " AND u.supervisor_id = ?";
            $params_list[] = $current_user_id;
        } elseif ($current_user_role == 'representative') {
            $sql_targets_list .= " AND it.representative_id = ?";
            $params_list[] = $current_user_id;
        }

        $sql_targets_list .= " ORDER BY u.full_name, p.name";
        $stmt_targets = $pdo->prepare($sql_targets_list);
        $stmt_targets->execute($params_list);
        $item_targets_raw = $stmt_targets->fetchAll(PDO::FETCH_ASSOC);

        // **التحسين المطلوب: تجميع البيانات حسب المندوب**
        $item_targets_grouped = [];
        foreach ($item_targets_raw as $target) {
            $item_targets_grouped[$target['representative_name']][] = $target;
        }
        
        $view_file = 'views/item_targets/list.php';
        break;
}

include 'views/layout.php';