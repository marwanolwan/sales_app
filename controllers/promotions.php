<?php
// controllers/promotions.php

require_permission('manage_promotions');

$action = $_GET['action'] ?? 'list_customers_with_promos';
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

// إذا تم تحديد عميل، انتقل مباشرة إلى لوحة التحكم الخاصة به (لا تغيير هنا)
if ($customer_id) {
    // ... (نفس الكود السابق لعرض لوحة تحكم العميل) ...
    $stmt_cust = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
    $stmt_cust->execute([$customer_id]);
    $customer = $stmt_cust->fetch();
    if (!$customer) {
        $_SESSION['error_message'] = "العميل المحدد غير موجود.";
        header("Location: index.php?page=promotions");
        exit();
    }
    $page_title = "خدمات الدعاية للزبون: " . htmlspecialchars($customer['name']);
    $view_file = 'views/promotions/customer_dashboard.php';
    include 'views/layout.php';
    exit();
}


$page_title = "خدمات الدعاية والعقود";
$view_file = '';

// =====| بداية تعديلات الفلاتر المتتالية |=====

// 1. تحديد الفلاتر النشطة من الرابط
$filter_region_id = $_GET['region_id'] ?? 'all';
$filter_supervisor_id = $_GET['supervisor_id'] ?? 'all';
$filter_rep_id = $_GET['representative_id'] ?? 'all';
$filter_promoter_id = $_GET['promoter_id'] ?? 'all';

// 2. جلب خيارات الفلاتر بشكل ديناميكي

// المناطق (دائما كل المناطق)
$regions = $pdo->query("SELECT region_id, name FROM regions ORDER BY name ASC")->fetchAll();

// المشرفون (يعتمد على المنطقة المختارة)
$supervisors_sql = "SELECT user_id, full_name FROM users WHERE role = 'supervisor' AND is_active = TRUE";
$supervisor_params = [];
if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) {
    $supervisors_sql .= " AND region_id = ?";
    $supervisor_params[] = $filter_region_id;
}
$supervisors_sql .= " ORDER BY full_name ASC";
$stmt_supervisors = $pdo->prepare($supervisors_sql);
$stmt_supervisors->execute($supervisor_params);
$supervisors = $stmt_supervisors->fetchAll();

// المندوبون (يعتمد على المنطقة أو المشرف المختار)
$reps_sql = "SELECT u.user_id, u.full_name FROM users u LEFT JOIN users sup ON u.supervisor_id = sup.user_id WHERE u.role = 'representative' AND u.is_active = TRUE";
$reps_params = [];
if ($filter_supervisor_id !== 'all' && is_numeric($filter_supervisor_id)) {
    $reps_sql .= " AND u.supervisor_id = ?";
    $reps_params[] = $filter_supervisor_id;
} elseif ($filter_region_id !== 'all' && is_numeric($filter_region_id)) {
    $reps_sql .= " AND sup.region_id = ?";
    $reps_params[] = $filter_region_id;
}
$reps_sql .= " ORDER BY u.full_name ASC";
$stmt_reps = $pdo->prepare($reps_sql);
$stmt_reps->execute($reps_params);
$representatives = $stmt_reps->fetchAll();

// المروجون (يعتمد على المنطقة أو المشرف المختار)
$promoters_sql = "SELECT u.user_id, u.full_name FROM users u LEFT JOIN users sup ON u.supervisor_id = sup.user_id WHERE u.role = 'promoter' AND u.is_active = TRUE";
$promoters_params = [];
if ($filter_supervisor_id !== 'all' && is_numeric($filter_supervisor_id)) {
    $promoters_sql .= " AND u.supervisor_id = ?";
    $promoters_params[] = $filter_supervisor_id;
} elseif ($filter_region_id !== 'all' && is_numeric($filter_region_id)) {
    $promoters_sql .= " AND sup.region_id = ?";
    $promoters_params[] = $filter_region_id;
}
$promoters_sql .= " ORDER BY u.full_name ASC";
$stmt_promoters = $pdo->prepare($promoters_sql);
$stmt_promoters->execute($promoters_params);
$promoters = $stmt_promoters->fetchAll();

// =====| نهاية تعديلات الفلاتر المتتالية |=====
switch ($action) {
    case 'add_customer':
        $page_title = "إضافة زبون جديد للاتفاقيات";

        // =====| بداية التعديل |=====
        // تعريف جميع المتغيرات اللازمة للواجهة والترقيم
        $search_term = trim($_GET['search'] ?? '');
        $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page_num < 1) $page_num = 1;
        $items_per_page = 20;
        $offset = ($page_num - 1) * $items_per_page;
        // =====| نهاية التعديل |=====

        // استعلام لجلب العملاء الذين ليس لديهم أي اتفاقيات بعد
        $subquery = "NOT EXISTS (SELECT 1 FROM annual_campaigns WHERE customer_id = c.customer_id)
                     AND NOT EXISTS (SELECT 1 FROM temp_campaigns WHERE customer_id = c.customer_id)
                     AND NOT EXISTS (SELECT 1 FROM annual_contracts WHERE customer_id = c.customer_id)";

        $base_sql = "FROM customers c WHERE c.status = 'active' AND ({$subquery})";
        $params = [];

        if (!empty($search_term)) {
            $base_sql .= " AND (c.name LIKE ? OR c.customer_code LIKE ?)";
            $search_param = "%{$search_term}%";
            array_push($params, $search_param, $search_param);
        }

        // حساب العدد الإجمالي
        $total_items_sql = "SELECT COUNT(*) " . $base_sql;
        $stmt_total = $pdo->prepare($total_items_sql);
        $stmt_total->execute($params);
        $total_items = $stmt_total->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);

        // جلب البيانات للصفحة الحالية
        $data_sql = "SELECT c.customer_id, c.name, c.customer_code " . $base_sql . " ORDER BY c.name ASC LIMIT ? OFFSET ?";
        $stmt_data = $pdo->prepare($data_sql);
        $param_index = 1;
        foreach ($params as $param) { $stmt_data->bindValue($param_index++, $param); }
        $stmt_data->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
        $stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
        $stmt_data->execute();
        $customers_to_add = $stmt_data->fetchAll();

        $view_file = 'views/promotions/add_customer_to_promo.php';
        break;
    case 'list_customers_with_promos':
    default:
        $page_title = "زبائن لديهم اتفاقيات حالية";

        // متغيرات البحث والترقيم
 $search_term = trim($_GET['search'] ?? '');
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) $page_num = 1;
$items_per_page = 20;
$offset = ($page_num - 1) * $items_per_page;
        
        // بناء الاستعلام الأساسي مع JOIN إضافي للمناطق
        $base_sql = "FROM customers c
                     LEFT JOIN users rep ON c.representative_id = rep.user_id
                     LEFT JOIN users sup ON rep.supervisor_id = sup.user_id";

        $where_clauses = [];
        $params = [];
        
        // شرط أساسي: يجب أن يكون للعميل اتفاقية
        $subquery_exists = "EXISTS (SELECT 1 FROM annual_campaigns WHERE customer_id = c.customer_id)
                         OR EXISTS (SELECT 1 FROM temp_campaigns WHERE customer_id = c.customer_id)
                         OR EXISTS (SELECT 1 FROM annual_contracts WHERE customer_id = c.customer_id)";
        $where_clauses[] = "c.status = 'active' AND ({$subquery_exists})";

        // =====| بداية تعديل الفلاتر |=====

        // فلتر المنطقة (يعتمد على مشرف المندوب)
        if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) {
            $where_clauses[] = "sup.region_id = ?";
            $params[] = $filter_region_id;
        }

        // فلتر المندوب
        if ($filter_rep_id !== 'all' && is_numeric($filter_rep_id)) {
            $where_clauses[] = "c.representative_id = ?";
            $params[] = $filter_rep_id;
        }

        // فلتر المروج
        if ($filter_promoter_id !== 'all' && is_numeric($filter_promoter_id)) {
            $where_clauses[] = "c.promoter_id = ?";
            $params[] = $filter_promoter_id;
        }

        // فلتر البحث
        if (!empty($search_term)) {
            $where_clauses[] = "(c.name LIKE ? OR c.customer_code LIKE ?)";
            $search_param = "%{$search_term}%";
            array_push($params, $search_param, $search_param);
        }
        
        // =====| نهاية تعديل الفلاتر |=====

        $sql_where = "";
        if (!empty($where_clauses)) {
            $sql_where = " WHERE " . implode(' AND ', $where_clauses);
        }

        // حساب العدد الإجمالي
        $total_items_sql = "SELECT COUNT(c.customer_id) " . $base_sql . $sql_where;
        $stmt_total = $pdo->prepare($total_items_sql);
        $stmt_total->execute($params);
        $total_items = $stmt_total->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);

        // جلب البيانات للصفحة الحالية
        $data_sql = "SELECT c.customer_id, c.name, c.customer_code " . $base_sql . $sql_where . " ORDER BY c.name ASC LIMIT ? OFFSET ?";
        $stmt_data = $pdo->prepare($data_sql);
        
        $param_index = 1;
        foreach ($params as $param) { $stmt_data->bindValue($param_index++, $param); }
        $stmt_data->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
        $stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
        $stmt_data->execute();
        $customers_with_promos = $stmt_data->fetchAll();
        
        $view_file = 'views/promotions/customer_selection_list.php';
        break;
}

include 'views/layout.php';