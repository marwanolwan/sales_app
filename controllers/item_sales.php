<?php
// controllers/item_sales.php

require_permission('manage_item_sales'); 

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

$action = $_GET['action'] ?? 'list';

$page_title = "مبيعات الأصناف الشهرية";
$view_file = '';

// --- فلاتر العرض ---
$filter_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$filter_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$filter_rep = $_GET['representative_id'] ?? 'all';
$filter_product = $_GET['product_id'] ?? 'all';
$filter_customer = $_GET['customer_id'] ?? 'all';

$months_map = [
    1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
    7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
];

// --- جلب بيانات الفلاتر ---
$sql_reps = "SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE";
$params_reps = [];
if ($current_user_role == 'supervisor') {
    $sql_reps .= " AND supervisor_id = ?";
    $params_reps[] = $current_user_id;
}
$stmt_reps = $pdo->prepare($sql_reps . " ORDER BY full_name ASC");
$stmt_reps->execute($params_reps);
$representatives = $stmt_reps->fetchAll(PDO::FETCH_ASSOC);

$products = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = TRUE ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// **التصحيح: تعريف المتغيرات بقيم ابتدائية**
$item_sales = [];
$total_records = 0;
$total_pages = 0;
$current_page_num = 1;


switch ($action) {
    case 'import_preview':
        $page_title = "معاينة استيراد مبيعات الأصناف";
        $preview_data = $_SESSION['import_preview_data']['data'] ?? [];
        $import_errors = $_SESSION['import_preview_data']['errors'] ?? [];
        $has_errors = !empty($import_errors);
        
        if (empty($preview_data)) {
             $_SESSION['error_message'] = "لا توجد بيانات للمعاينة أو انتهت صلاحية الجلسة.";
             header("Location: index.php?page=item_sales");
             exit();
        }
        $view_file = 'views/item_sales/import_preview.php';
        break;

    case 'list':
    default:
        $base_sql = "FROM monthly_item_sales mis
                     JOIN products p ON mis.product_id = p.product_id
                     JOIN customers c ON mis.customer_id = c.customer_id
                     JOIN users u ON mis.representative_id = u.user_id
                     WHERE mis.year = ? AND mis.month = ?";
        
        $params = [$filter_year, $filter_month];
        
        $where_clauses = [];
        if ($filter_rep !== 'all' && is_numeric($filter_rep)) {
            $where_clauses[] = "mis.representative_id = ?";
            $params[] = (int)$filter_rep;
        }
        if ($filter_product !== 'all' && is_numeric($filter_product)) {
            $where_clauses[] = "mis.product_id = ?";
            $params[] = (int)$filter_product;
        }
        if ($filter_customer !== 'all' && is_numeric($filter_customer)) {
            $where_clauses[] = "mis.customer_id = ?";
            $params[] = (int)$filter_customer;
        }
        if ($current_user_role == 'supervisor') {
            $where_clauses[] = "u.supervisor_id = ?";
            $params[] = $current_user_id;
        } elseif ($current_user_role == 'representative') {
            $where_clauses[] = "mis.representative_id = ?";
            $params[] = $current_user_id;
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = ' AND ' . implode(' AND ', $where_clauses);
        }

        $count_sql = "SELECT COUNT(mis.item_sale_id) " . $base_sql . $where_sql;
        $stmt_count = $pdo->prepare($count_sql);
        $stmt_count->execute($params);
        $total_records = $stmt_count->fetchColumn();

        $records_per_page = 25;
        $total_pages = ceil($total_records / $records_per_page);
        $current_page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($current_page_num < 1) $current_page_num = 1;
        if ($current_page_num > $total_pages && $total_pages > 0) $current_page_num = $total_pages;
        $offset = ($current_page_num - 1) * $records_per_page;

        $select_sql = "SELECT mis.*, p.name as product_name, p.product_code, c.name as customer_name, u.full_name as representative_name ";
        $order_limit_sql = " ORDER BY u.full_name, c.name, p.name LIMIT ? OFFSET ?";
        
        $final_sql = $select_sql . $base_sql . $where_sql . $order_limit_sql;
        $final_params = array_merge($params, [$records_per_page, $offset]);

        $stmt = $pdo->prepare($final_sql);
        foreach ($final_params as $key => $val) {
            $stmt->bindValue($key + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $item_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $view_file = 'views/item_sales/list.php';
        break;
}

include 'views/layout.php';