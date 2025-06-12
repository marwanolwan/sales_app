<?php
// controllers/reports.php

require_permission('view_reports');

// تحديد التقرير المطلوب، مع التأكد من أنه في القائمة البيضاء
$report_type = $_GET['type'] ?? 'dashboard';

$available_reports = [
    'dashboard' => 'لوحة تحكم التقارير',
    'top_selling_items' => 'الأصناف الأكثر مبيعًا (حسب الكمية)',
    'stagnant_items' => 'الأصناف الراكدة',
    'customer_purchase_analysis' => 'تحليل مشتريات نقاط البيع',
    'product_distribution' => 'تقرير توزيع الأصناف',
    'item_target_performance' => 'مقارنة أهداف ومبيعات الأصناف', // <-- التقرير الجديد
    'value_target_performance' =>  'مقارنة أهداف وقيمة مبيعات الأصناف', // هذا التقرير قيد الإضافة
    'customer_item_yoy_comparison' => 'مقارنة مبيعات الأصناف للعملاء (سنوي)', // <-- التقرير الجديد
    'rep_effectiveness' => 'تقرير فعالية المندوب', // <-- التقرير الجديد
    'contractual_targets' => 'تقرير الأهداف التعاقدية للعملاء', // <-- التقرير الجديد
    'trend_analysis' => 'تقرير تحليل الاتجاهات', // <-- التقرير الجديد
    'sales_vs_collection' => 'تقرير المبيعات مقابل التحصيل', // <-- التقرير الجديد
    'sales_mix' => 'تقرير مزيج المبيعات', // <-- التقرير الجديد
    'lost_customers' => 'تقرير العملاء المفقودين' // <-- التقرير الجديد

    // باقي التقارير ستضاف هنا
];

if (!array_key_exists($report_type, $available_reports)) {
    header("Location: index.php?page=reports");
    exit();
}

$page_title = $available_reports[$report_type];
$view_file = '';
$report_data = [];

// --- جلب بيانات الفلاتر العامة ---
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// استقبال قيم الفلاتر من الرابط مع قيم افتراضية
$filter_current_start = $_GET['current_start'] ?? date('Y-m-01');
$filter_current_end = $_GET['current_end'] ?? date('Y-m-t');
$filter_previous_start = $_GET['previous_start'] ?? date('Y-m-01', strtotime('-1 year'));
$filter_previous_end = $_GET['previous_end'] ?? date('Y-m-t', strtotime('-1 year'));
$filter_year = $_GET['year'] ?? date('Y');
$filter_period_type = $_GET['period_type'] ?? 'annually';
$filter_month = $_GET['month'] ?? date('n');
$filter_quarter = $_GET['quarter'] ?? ceil(date('n') / 3);
$filter_region_id = $_GET['region_id'] ?? 'all';
$filter_rep_id = $_GET['representative_id'] ?? 'all';
$filter_family_id = $_GET['family_id'] ?? 'all';
$filter_limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search_term = trim($_GET['search'] ?? '');

$filter_year_primary = $_GET['year_primary'] ?? date('Y');
$filter_year_secondary = $_GET['year_secondary'] ?? (date('Y') - 1);
$filter_customer_id = $_GET['customer_id'] ?? 'all';
$filter_product_id = $_GET['product_id'] ?? 'all';
// --- جلب خيارات الفلاتر بشكل ديناميكي ---
$regions = $pdo->query("SELECT region_id, name FROM regions ORDER BY name ASC")->fetchAll();
$product_families = $pdo->query("SELECT family_id, name FROM product_families WHERE is_active = TRUE ORDER BY name ASC")->fetchAll();
$all_customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$all_products = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

$reps_sql = "SELECT u.user_id, u.full_name FROM users u LEFT JOIN users sup ON u.supervisor_id = sup.user_id WHERE u.role = 'representative' AND u.is_active = TRUE";
$reps_params = [];
if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) {
    $reps_sql .= " AND sup.region_id = ?";
    $reps_params[] = $filter_region_id;
}

$reps_sql .= " ORDER BY u.full_name ASC";
$stmt_reps = $pdo->prepare($reps_sql);
$stmt_reps->execute($reps_params);
$representatives = $stmt_reps->fetchAll();

// --- بناء الاستعلام بناءً على الفلاتر (مرة واحدة) ---
list($where_clause, $params) = build_sales_query_filters(
    $pdo, $filter_year, $filter_period_type, $filter_month,
    $filter_quarter, $filter_region_id, $filter_rep_id
);

// متغيرات لتخزين الإجماليات
$grand_total_quantity = 0;


// --- التوجيه إلى منطق التقرير المحدد ---
switch ($report_type) {
    case 'top_selling_items':
        $sql = "SELECT p.product_code, p.name, SUM(mis.quantity_sold) as total_quantity, SUM(mis.total_value) as total_value
                FROM monthly_item_sales mis
                JOIN products p ON mis.product_id = p.product_id
                JOIN users rep ON mis.representative_id = rep.user_id
                LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                {$where_clause}
                GROUP BY p.product_id, p.name, p.product_code
                ORDER BY total_quantity DESC 
                LIMIT ?";
        
        $final_params = $params;
        $final_params[] = $filter_limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($final_params);
        $report_data = $stmt->fetchAll();

        if (!empty($report_data)) {
            $total_sql = "SELECT SUM(mis.quantity_sold) 
                          FROM monthly_item_sales mis
                          JOIN users rep ON mis.representative_id = rep.user_id
                          LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                          {$where_clause}";
            $stmt_total = $pdo->prepare($total_sql);
            $stmt_total->execute($params);
            $grand_total_quantity = $stmt_total->fetchColumn();
        }
        
        $view_file = 'views/reports/top_selling_items.php';
        break;

    case 'stagnant_items':
        $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page_num < 1) $page_num = 1;
        $items_per_page = 25;
        $offset = ($page_num - 1) * $items_per_page;
        
        $products_base_sql = "FROM products p LEFT JOIN product_families pf ON p.family_id = pf.family_id";
        
        $products_where_clauses = ["p.is_active = 1"];
        $products_params = [];
        
        if ($filter_family_id !== 'all' && is_numeric($filter_family_id)) {
            $products_where_clauses[] = "p.family_id = ?";
            $products_params[] = $filter_family_id;
        }

        $sales_subquery = "SELECT DISTINCT mis.product_id FROM monthly_item_sales mis
                           JOIN users rep ON mis.representative_id = rep.user_id
                           LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                           {$where_clause}";
        
        $products_where_clauses[] = "p.product_id NOT IN ({$sales_subquery})";
        $products_where_sql = " WHERE " . implode(" AND ", $products_where_clauses);
        
        $final_params = array_merge($products_params, $params);

        $total_items_sql = "SELECT COUNT(p.product_id) " . $products_base_sql . $products_where_sql;
        $stmt_total = $pdo->prepare($total_items_sql);
        $stmt_total->execute($final_params);
        $total_items = $stmt_total->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);
        
        if ($page_num > $total_pages && $total_pages > 0) {
            $page_num = $total_pages;
            $offset = ($page_num - 1) * $items_per_page;
        }

        $data_sql = "SELECT p.product_code, p.name, pf.name as family_name " 
                    . $products_base_sql . $products_where_sql 
                    . " ORDER BY pf.name, p.name LIMIT ? OFFSET ?";
        
        $stmt_data = $pdo->prepare($data_sql);
        $param_index = 1;
        foreach ($final_params as $param) { $stmt_data->bindValue($param_index++, $param); }
        $stmt_data->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
        $stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
        $stmt_data->execute();
        $report_data = $stmt_data->fetchAll();
        
        $view_file = 'views/reports/stagnant_items.php';
        break;
        
    case 'customer_purchase_analysis':
        $customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

        if ($customer_id) {
            // --- المرحلة الثانية: عرض التقرير المفصل للعميل ---
            $stmt_cust = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
            $stmt_cust->execute([$customer_id]);
            $customer = $stmt_cust->fetch();
            $page_title = "تفاصيل مشتريات الزبون: " . htmlspecialchars($customer['name']);

            $sql_bought = "SELECT p.product_code, p.name, p.product_id, SUM(mis.quantity_sold) as total_quantity, 
                                  GROUP_CONCAT(DISTINCT mis.month ORDER BY mis.month SEPARATOR ', ') as purchase_months
                           FROM monthly_item_sales mis
                           JOIN products p ON mis.product_id = p.product_id
                           JOIN users rep ON mis.representative_id = rep.user_id
                           LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                           {$where_clause} AND mis.customer_id = ?
                           GROUP BY p.product_id, p.name, p.product_code
                           ORDER BY total_quantity DESC";
            
            $bought_params = array_merge($params, [$customer_id]);
            $stmt_bought = $pdo->prepare($sql_bought);
            $stmt_bought->execute($bought_params);
            $bought_items = $stmt_bought->fetchAll();
            $bought_item_ids = array_column($bought_items, 'product_id');

            $all_active_products = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = 1")->fetchAll();
            $not_bought_items = array_filter($all_active_products, function($product) use ($bought_item_ids) {
                return !in_array($product['product_id'], $bought_item_ids);
            });

            $total_active_count = count($all_active_products);
            $bought_count = count($bought_items);
            $coverage_percentage = ($total_active_count > 0) ? ($bought_count / $total_active_count) * 100 : 0;
            
            $view_file = 'views/reports/customer_purchase_details.php';

        } else {
            // --- المرحلة الأولى: عرض قائمة العملاء ---
            $page_title = $available_reports[$report_type];
            $search_term = trim($_GET['search'] ?? '');
            $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            if ($page_num < 1) $page_num = 1;
            $items_per_page = 20;
            $offset = ($page_num - 1) * $items_per_page;

            $base_sql = "FROM customers c 
                         LEFT JOIN users rep ON c.representative_id = rep.user_id
                         LEFT JOIN users sup ON rep.supervisor_id = sup.user_id";
            
            $where_clauses = ["c.status = 'active'"];
            $list_params = [];
            
            if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) { $where_clauses[] = "sup.region_id = ?"; $list_params[] = $filter_region_id; }
            if ($filter_rep_id !== 'all' && is_numeric($filter_rep_id)) { $where_clauses[] = "c.representative_id = ?"; $list_params[] = $filter_rep_id; }
            if (!empty($search_term)) { 
                $where_clauses[] = "(c.name LIKE ? OR c.customer_code LIKE ?)";
                $search_param = "%{$search_term}%";
                array_push($list_params, $search_param, $search_param);
            }

            $sql_where = " WHERE " . implode(' AND ', $where_clauses);
            
            $total_sql = "SELECT COUNT(c.customer_id) " . $base_sql . $sql_where;
            $stmt_total = $pdo->prepare($total_sql);
            $stmt_total->execute($list_params);
            $total_items = $stmt_total->fetchColumn();
            $total_pages = ceil($total_items / $items_per_page);

            if ($page_num > $total_pages && $total_pages > 0) {
                $page_num = $total_pages;
                $offset = ($page_num - 1) * $items_per_page;
            }

            $data_sql = "SELECT c.customer_id, c.name, c.customer_code " . $base_sql . $sql_where . " ORDER BY c.name ASC LIMIT ? OFFSET ?";
            $stmt_list = $pdo->prepare($data_sql);
            
            $param_index = 1;
            foreach ($list_params as $param) { $stmt_list->bindValue($param_index++, $param); }
            $stmt_list->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
            $stmt_list->bindValue($param_index++, $offset, PDO::PARAM_INT);
            $stmt_list->execute();
            $report_data = $stmt_list->fetchAll();
            
            $view_file = 'views/reports/customer_purchase_list.php';
        }
        break;
        case 'product_distribution':
        $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

        if ($product_id) {
            // --- المرحلة الثانية: عرض تفاصيل المنتج المحدد ---
            $stmt_prod = $pdo->prepare("SELECT name, product_code FROM products WHERE product_id = ?");
            $stmt_prod->execute([$product_id]);
            $product = $stmt_prod->fetch();
            $page_title = "توزيع المنتج: " . htmlspecialchars($product['name']);

            // 1. جلب العملاء الذين اشتروا هذا المنتج خلال الفترة والفلاتر المحددة
                        $sql_bought = "SELECT c.customer_id, c.name, c.customer_code, rep.full_name as representative_name,
                                  SUM(mis.quantity_sold) as total_quantity_bought,
                                  GROUP_CONCAT(DISTINCT mis.month ORDER BY mis.month SEPARATOR ', ') as purchase_months
                           FROM monthly_item_sales mis
                           JOIN customers c ON mis.customer_id = c.customer_id
                           JOIN users rep ON c.representative_id = rep.user_id
                           LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                           {$where_clause} AND mis.product_id = ?
                           GROUP BY c.customer_id, c.name, c.customer_code, rep.full_name
                           ORDER BY c.name";
            
            $bought_params = array_merge($params, [$product_id]);
            $stmt_bought = $pdo->prepare($sql_bought);
            $stmt_bought->execute($bought_params);
            $customers_who_bought = $stmt_bought->fetchAll();
            $bought_customer_ids = array_column($customers_who_bought, 'customer_id');


            // 2. جلب كل العملاء النشطين الذين يطابقون الفلاتر الجغرافية (منطقة/مندوب)
           $customer_list_sql = "FROM customers c
                                  LEFT JOIN users rep ON c.representative_id = rep.user_id
                                  LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                                  WHERE c.status = 'active'";
            $customer_list_params = [];
            if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) { $customer_list_sql .= " AND sup.region_id = ?"; $customer_list_params[] = $filter_region_id; }
            if ($filter_rep_id !== 'all' && is_numeric($filter_rep_id)) { $customer_list_sql .= " AND c.representative_id = ?"; $customer_list_params[] = $filter_rep_id; }
            $all_relevant_customers_sql = "SELECT c.customer_id, c.name, c.customer_code, rep.full_name as representative_name " . $customer_list_sql;
            $stmt_all_relevant = $pdo->prepare($all_relevant_customers_sql);
            $stmt_all_relevant->execute($customer_list_params);
            $all_relevant_customers = $stmt_all_relevant->fetchAll();

            // 3. تحديد العملاء الذين لم يشتروا
             $customers_who_did_not_buy = array_filter($all_relevant_customers, function($customer) use ($bought_customer_ids) {
                return !in_array($customer['customer_id'], $bought_customer_ids);
            });
            
            $view_file = 'views/reports/product_distribution_details.php';

        } else {
            // --- المرحلة الأولى: عرض قائمة المنتجات ---
            $page_title = $available_reports[$report_type];
            $search_term = trim($_GET['search'] ?? '');
            $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $items_per_page = 20;
            $offset = ($page_num - 1) * $items_per_page;
            
            // جلب المنتجات مع كمية مبيعاتها (ضمن الفلاتر)
            $sql_products = "SELECT p.product_id, p.name, p.product_code, COALESCE(SUM(mis.quantity_sold), 0) as total_quantity
                             FROM products p
                             LEFT JOIN (
                                 monthly_item_sales mis
                                 JOIN users rep ON mis.representative_id = rep.user_id
                                 LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                             ) ON p.product_id = mis.product_id AND " . substr($where_clause, 6) . "
                             WHERE p.is_active = 1"; // substr لإزالة "WHERE" من $where_clause
            
            if (!empty($search_term)) {
                $sql_products .= " AND (p.name LIKE ? OR p.product_code LIKE ?)";
                $params[] = "%{$search_term}%";
                $params[] = "%{$search_term}%";
            }
            
            $sql_products .= " GROUP BY p.product_id, p.name, p.product_code ORDER BY total_quantity DESC";
            
            // تطبيق الترقيم
            $total_items_sql = "SELECT COUNT(*) FROM ({$sql_products}) as subquery";
            $stmt_total = $pdo->prepare($total_items_sql);
            $stmt_total->execute($params);
            $total_items = $stmt_total->fetchColumn();
            $total_pages = ceil($total_items / $items_per_page);

            $data_sql = $sql_products . " LIMIT ? OFFSET ?";
            $stmt_data = $pdo->prepare($data_sql);
            
            $param_index = 1;
            foreach ($params as $param) { $stmt_data->bindValue($param_index++, $param); }
            $stmt_data->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
            $stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
            $stmt_data->execute();
            $report_data = $stmt_data->fetchAll();
            
            $view_file = 'views/reports/product_distribution_list.php';
        }
        break;

     case 'item_target_performance':
        // هذا التقرير يعمل بشكل أفضل على مستوى شهري، لذلك سنجبر الفلتر على ذلك
        $filter_period_type = 'monthly';
        $page_title = "مقارنة أهداف ومبيعات الأصناف لشهر " . ($arabic_months[$filter_month] ?? $filter_month) . " " . $filter_year;

        // 1. جلب الأهداف والمبيعات معًا
        // نستخدم LEFT JOIN من الأهداف إلى المبيعات لضمان ظهور كل الأهداف حتى لو لم تكن هناك مبيعات
        $sql = "SELECT 
                    t.product_id,
                    p.name as product_name,
                    p.product_code,
                    rep.full_name as representative_name,
                    t.target_quantity,
                    COALESCE(SUM(s.quantity_sold), 0) as total_sold
                FROM item_sales_targets t
                JOIN products p ON t.product_id = p.product_id
                JOIN users rep ON t.representative_id = rep.user_id
                LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                LEFT JOIN monthly_item_sales s ON t.product_id = s.product_id 
                                               AND t.representative_id = s.representative_id 
                                               AND t.year = s.year 
                                               AND t.month = s.month
                WHERE t.year = ? AND t.month = ?";
        
        $report_params = [$filter_year, $filter_month];

        // تطبيق الفلاتر الجغرافية
        if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) {
            $sql .= " AND sup.region_id = ?";
            $report_params[] = $filter_region_id;
        }
        if ($filter_rep_id !== 'all' && is_numeric($filter_rep_id)) {
            $sql .= " AND t.representative_id = ?";
            $report_params[] = $filter_rep_id;
        }
        
        // تطبيق صلاحيات المستخدم
        if ($current_user_role === 'supervisor') {
            $sql .= " AND rep.supervisor_id = ?";
            $report_params[] = $current_user_id;
        } elseif ($current_user_role === 'representative') {
            $sql .= " AND t.representative_id = ?";
            $report_params[] = $current_user_id;
        }

        $sql .= " GROUP BY t.product_id, p.name, p.product_code, rep.full_name, t.target_quantity
                  ORDER BY rep.full_name, p.name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($report_params);
        $report_data = $stmt->fetchAll();

        $view_file = 'views/reports/item_target_performance.php';
        break;

        case 'value_target_performance':
        $page_title = "تحقيق الأهداف النقدية";

        // تحديد مستوى التجميع (لكل مندوب، لكل مشرف، أو لكل منطقة)
        $group_by_level = 'representative'; // الافتراضي
        if ($filter_rep_id === 'all' && $filter_region_id !== 'all') {
            $group_by_level = 'supervisor'; // إذا تم اختيار منطقة فقط، نجمع حسب المشرف
        } elseif ($filter_rep_id === 'all' && $filter_region_id === 'all') {
             $group_by_level = 'region'; // إذا لم يتم اختيار شيء، نجمع حسب المنطقة
        }

        // بناء حقول SELECT و GROUP BY بناءً على مستوى التجميع
        $select_fields = "rep.full_name as group_name, sup.full_name as supervisor_name, r.name as region_name";
        $group_by_fields = "rep.user_id, sup.full_name, r.name";
        
        if ($group_by_level === 'supervisor') {
            $select_fields = "sup.full_name as group_name, r.name as region_name";
            $group_by_fields = "sup.user_id, r.name";
        } elseif ($group_by_level === 'region') {
            $select_fields = "r.name as group_name";
            $group_by_fields = "r.region_id";
        }

        // بناء استعلام الأهداف والمبيعات معًا
        $sql = "SELECT 
                    {$select_fields},
                    COALESCE(SUM(t.target_amount), 0) as total_target,
                    COALESCE(SUM(s.net_sales_amount), 0) as total_sales
                FROM users rep
                LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                LEFT JOIN regions r ON sup.region_id = r.region_id
                LEFT JOIN sales_targets t ON rep.user_id = t.representative_id AND t.year = ?
                LEFT JOIN monthly_sales s ON rep.user_id = s.representative_id AND s.year = ?
                WHERE rep.role = 'representative' AND rep.is_active = 1";
        
        // استخدام نفس السنة للهدف والمبيعات
        $report_params = [$filter_year, $filter_year];

        // تطبيق فلاتر الفترة على كلا الجدولين (الأهداف والمبيعات)
        if ($filter_period_type === 'monthly') {
            $sql .= " AND t.month = ? AND s.month = ?";
            array_push($report_params, $filter_month, $filter_month);
        } elseif ($filter_period_type === 'quarterly') {
            $q_months = [];
            if ($filter_quarter == 1) $q_months = [1,2,3]; elseif ($filter_quarter == 2) $q_months = [4,5,6];
            elseif ($filter_quarter == 3) $q_months = [7,8,9]; elseif ($filter_quarter == 4) $q_months = [10,11,12];
            $placeholders = implode(',', array_fill(0, count($q_months), '?'));
            $sql .= " AND t.month IN ({$placeholders}) AND s.month IN ({$placeholders})";
            $report_params = array_merge($report_params, $q_months, $q_months);
        }

        // تطبيق الفلاتر الجغرافية
        if ($filter_region_id !== 'all') { $sql .= " AND r.region_id = ?"; $report_params[] = $filter_region_id; }
        if ($filter_rep_id !== 'all') { $sql .= " AND rep.user_id = ?"; $report_params[] = $filter_rep_id; }

        $sql .= " GROUP BY {$group_by_fields} ORDER BY group_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($report_params);
        $report_data = $stmt->fetchAll();

        $view_file = 'views/reports/value_target_performance.php';
        break;

        case 'customer_item_yoy_comparison':
        $page_title = "مقارنة مبيعات الأصناف للعملاء";

        // =====| بداية التعديل: التعامل مع الحسابات الرئيسية والفروع |=====
        
        $search_term_customer = trim($_GET['search_customer'] ?? '');
        $search_term_product = trim($_GET['search_product'] ?? '');
        $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page_num < 1) $page_num = 1;
        $items_per_page = 20;
        $offset = ($page_num - 1) * $items_per_page;

        // 1. بناء الاستعلام الأساسي مع منطق COALESCE لتجميع الفروع
        $base_sql = "FROM monthly_item_sales mis
                     JOIN customers c ON mis.customer_id = c.customer_id
                     -- الانضمام إلى جدول العملاء مرة أخرى لجلب اسم الحساب الرئيسي
                     JOIN customers main_c ON COALESCE(c.main_account_id, c.customer_id) = main_c.customer_id
                     JOIN products p ON mis.product_id = p.product_id
                     JOIN users rep ON mis.representative_id = rep.user_id
                     LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                     WHERE mis.year IN (?, ?)";
        
        $report_params = [$filter_year_primary, $filter_year_secondary];

        // تطبيق فلاتر الفترة الزمنية
        if ($filter_period_type === 'monthly') {
            $base_sql .= " AND mis.month = ?";
            $report_params[] = $filter_month;
        } elseif ($filter_period_type === 'quarterly') { /* ... */ }

        // تطبيق الفلاتر الأخرى
        if ($filter_region_id !== 'all') { $base_sql .= " AND sup.region_id = ?"; $report_params[] = $filter_region_id; }
        if ($filter_rep_id !== 'all') { $base_sql .= " AND mis.representative_id = ?"; $report_params[] = $filter_rep_id; }
        if ($filter_customer_id !== 'all') { 
            // إذا تم اختيار عميل، يجب أن نبحث عنه سواء كان رئيسيًا أو فرعيًا
            $base_sql .= " AND COALESCE(c.main_account_id, c.customer_id) = ?"; 
            $report_params[] = $filter_customer_id; 
        }
        if ($filter_product_id !== 'all') { $base_sql .= " AND mis.product_id = ?"; $report_params[] = $filter_product_id; }
        
        // بناء الاستعلام الفرعي الذي يجمع البيانات
        $subquery_sql = "SELECT 
                            main_c.customer_id, main_c.name as customer_name,
                            p.product_id, p.name as product_name,
                            SUM(CASE WHEN mis.year = ? THEN mis.quantity_sold ELSE 0 END) as sales_primary,
                            SUM(CASE WHEN mis.year = ? THEN mis.quantity_sold ELSE 0 END) as sales_secondary
                        {$base_sql}
                        -- التجميع الآن على مستوى الحساب الرئيسي
                        GROUP BY main_c.customer_id, main_c.name, p.product_id, p.name";

        $subquery_params = array_merge([$filter_year_primary, $filter_year_secondary], $report_params);

        // بناء الاستعلام النهائي مع فلاتر البحث والترقيم
        $final_base_sql = "FROM ({$subquery_sql}) as comparison_data";
        $final_where_clauses = ["(sales_primary != 0 OR sales_secondary != 0)"];
        $final_params = $subquery_params;

        if (!empty($search_term_customer)) {
            $final_where_clauses[] = "customer_name LIKE ?";
            $final_params[] = "%{$search_term_customer}%";
        }
        if (!empty($search_term_product)) {
            $final_where_clauses[] = "product_name LIKE ?";
            $final_params[] = "%{$search_term_product}%";
        }
        $final_where_sql = " WHERE " . implode(" AND ", $final_where_clauses);

        // حساب العدد الإجمالي
        $total_items_sql = "SELECT COUNT(*) " . $final_base_sql . $final_where_sql;
        $stmt_total = $pdo->prepare($total_items_sql);
        $stmt_total->execute($final_params);
        $total_items = $stmt_total->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);
        
        if ($page_num > $total_pages && $total_pages > 0) {
            $page_num = $total_pages;
            $offset = ($page_num - 1) * $items_per_page;
        }

        // جلب بيانات الصفحة الحالية
        $data_sql = "SELECT * " . $final_base_sql . $final_where_sql . " ORDER BY customer_name, product_name LIMIT ? OFFSET ?";
        $stmt_data = $pdo->prepare($data_sql);
        
        $param_index = 1;
        foreach ($final_params as $param) { $stmt_data->bindValue($param_index++, $param); }
        $stmt_data->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
        $stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
        $stmt_data->execute();
        $report_data = $stmt_data->fetchAll();

        $view_file = 'views/reports/customer_item_yoy_comparison.php';
        break;

        case 'rep_effectiveness':
        $page_title = "تقرير فعالية المندوب";
 list($where_clause_sales, $params_sales) = build_sales_query_filters(
            $pdo, $filter_year, $filter_period_type, $filter_month,
            $filter_quarter, $filter_region_id, $filter_rep_id
        );
        // بناء الاستعلامات الجزئية لكل مؤشر أداء
        $where_clause_targets = str_replace('mis.', 't.', $where_clause_sales);
        $params_targets = $params_sales;
         $where_clause_customers = "WHERE c.status = 'active'";
        $params_customers = [];
        if ($filter_rep_id !== 'all') {
            $where_clause_customers .= " AND c.representative_id = ?";
            $params_customers[] = $filter_rep_id;
        }

        // 1. المبيعات والأهداف
                // 1. المبيعات والأهداف
        $sales_targets_sql = "
            SELECT
                rep.user_id,
                rep.full_name,
                COALESCE(SUM(s.net_sales_amount), 0) as total_sales,
                COALESCE(SUM(t.target_amount), 0) as total_target
            FROM users rep
            LEFT JOIN sales_targets t ON rep.user_id = t.representative_id AND t.year = ?
            LEFT JOIN monthly_sales s ON rep.user_id = s.representative_id AND s.year = ? AND (t.month IS NULL OR s.month = t.month)
            WHERE rep.role = 'representative' AND rep.is_active = 1
        ";
        $report_params = [$filter_year, $filter_year];
        
        // تطبيق فلاتر الفترة الزمنية
        if ($filter_period_type === 'monthly') {
            $sales_targets_sql .= " AND (t.month = ? OR s.month = ?)";
            array_push($report_params, $filter_month, $filter_month);
        } elseif ($filter_period_type === 'quarterly') {
            $q_months = [];
            if ($filter_quarter == 1) $q_months = [1,2,3]; elseif ($filter_quarter == 2) $q_months = [4,5,6];
            elseif ($filter_quarter == 3) $q_months = [7,8,9]; elseif ($filter_quarter == 4) $q_months = [10,11,12];
            $placeholders = implode(',', array_fill(0, count($q_months), '?'));
            $sales_targets_sql .= " AND (t.month IN ({$placeholders}) OR s.month IN ({$placeholders}))";
            $report_params = array_merge($report_params, $q_months, $q_months);
        }
        
        $sales_targets_sql .= " GROUP BY rep.user_id, rep.full_name";
        
        // 2. عدد العملاء النشطين (الذين اشتروا)
        $active_customers_sql = "SELECT representative_id, COUNT(DISTINCT customer_id) as active_customer_count FROM monthly_item_sales WHERE year = ? GROUP BY representative_id";

        // 3. عدد العملاء الجدد
        $new_customers_sql = "SELECT representative_id, COUNT(customer_id) as new_customer_count FROM customers WHERE opening_date BETWEEN ? AND ? GROUP BY representative_id";
        list($start_date, $end_date) = get_date_range_for_period($filter_year, $filter_period_type, $filter_month, $filter_quarter);
        
        // 4. عدد الأصناف المباعة (الفريدة)
        $unique_skus_sql = "SELECT representative_id, COUNT(DISTINCT product_id) as unique_sku_count FROM monthly_item_sales WHERE year = ? GROUP BY representative_id";

        // تنفيذ الاستعلامات
        $stmt_main = $pdo->prepare($sales_targets_sql);
        $stmt_main->execute($report_params);
        $report_data_raw = $stmt_main->fetchAll(); // جلب قياسي

        $stmt_active = $pdo->prepare($active_customers_sql);
        $stmt_active->execute([$filter_year]);
        $active_customers_data = $stmt_active->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt_new = $pdo->prepare($new_customers_sql);
        $stmt_new->execute([$start_date, $end_date]);
        $new_customers_data = $stmt_new->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt_sku = $pdo->prepare($unique_skus_sql);
        $stmt_sku->execute([$filter_year]);
        $unique_skus_data = $stmt_sku->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // دمج كل البيانات في مصفوفة واحدة باستخدام user_id كمفتاح
        $report_data = [];
        foreach ($report_data_raw as $data) {
            $user_id = $data['user_id'];
            $report_data[$user_id] = $data;
            $report_data[$user_id]['active_customer_count'] = $active_customers_data[$user_id] ?? 0;
            $report_data[$user_id]['new_customer_count'] = $new_customers_data[$user_id] ?? 0;
            $report_data[$user_id]['unique_sku_count'] = $unique_skus_data[$user_id] ?? 0;
        }


        $view_file = 'views/reports/rep_effectiveness.php';
        break;

            case 'contractual_targets':
        $page_title = "متابعة تحقيق الأهداف التعاقدية";

        // الفلاتر الخاصة بهذا التقرير
        $filter_year = $_GET['year'] ?? date('Y');
        $filter_customer_id = $_GET['customer_id'] ?? 'all';

        // 1. جلب جميع العقود التي تطابق الفلاتر
        $sql_contracts = "SELECT 
                            ac.*, 
                            c.name as customer_name, 
                            c.customer_code
                          FROM annual_contracts ac
                          JOIN customers c ON ac.customer_id = c.customer_id
                          WHERE ac.year = ?";
        
        $report_params = [$filter_year];

        if ($filter_customer_id !== 'all' && is_numeric($filter_customer_id)) {
            $sql_contracts .= " AND ac.customer_id = ?";
            $report_params[] = $filter_customer_id;
        }
        $sql_contracts .= " ORDER BY c.name";

        $stmt_contracts = $pdo->prepare($sql_contracts);
        $stmt_contracts->execute($report_params);
        $contracts = $stmt_contracts->fetchAll();

        // 2. جلب إجمالي المبيعات السنوية للعملاء الذين لديهم عقود
        $report_data = [];
        if (!empty($contracts)) {
            $customer_ids_with_contracts = array_column($contracts, 'customer_id');
            $placeholders = implode(',', array_fill(0, count($customer_ids_with_contracts), '?'));

            // استعلام لجلب المبيعات المجمعة (بما في ذلك الفروع)
            $sql_sales = "SELECT 
                            COALESCE(c.main_account_id, c.customer_id) as main_customer_id,
                            SUM(mis.total_value) as total_annual_sales
                          FROM monthly_item_sales mis
                          JOIN customers c ON mis.customer_id = c.customer_id
                          WHERE mis.year = ? AND COALESCE(c.main_account_id, c.customer_id) IN ({$placeholders})
                          GROUP BY main_customer_id";

            $stmt_sales = $pdo->prepare($sql_sales);
            $stmt_sales->execute(array_merge([$filter_year], $customer_ids_with_contracts));
            $sales_data = $stmt_sales->fetchAll(PDO::FETCH_KEY_PAIR);

            // 3. دمج بيانات المبيعات مع بيانات العقود
            foreach ($contracts as $contract) {
                $customer_id = $contract['customer_id'];
                $contract['total_annual_sales'] = $sales_data[$customer_id] ?? 0;
                $report_data[] = $contract;
            }
        }
        
        $view_file = 'views/reports/contractual_targets.php';
        break;
  case 'trend_analysis':
        $page_title = "تحليل اتجاهات المبيعات الشهرية";

        $sql = "SELECT 
                    s.month,
                    COALESCE(SUM(s.net_sales_amount), 0) as total_sales,
                    COALESCE(SUM(t.target_amount), 0) as total_target
                FROM 
                    (SELECT 1 as m UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) as months
                LEFT JOIN monthly_sales s ON months.m = s.month AND s.year = ?
                LEFT JOIN sales_targets t ON months.m = t.month AND t.year = ? AND s.representative_id = t.representative_id
                LEFT JOIN users rep ON s.representative_id = rep.user_id
                LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                WHERE 1=1
        ";
        
        $report_params = [$filter_year, $filter_year];

        // تطبيق الفلاتر
        if ($filter_region_id !== 'all') { $sql .= " AND sup.region_id = ?"; $report_params[] = $filter_region_id; }
        if ($filter_rep_id !== 'all') { $sql .= " AND s.representative_id = ?"; $report_params[] = $filter_rep_id; }
        
        $sql .= " GROUP BY months.m ORDER BY months.m";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($report_params);
        $report_data = $stmt->fetchAll();
        
        $view_file = 'views/reports/trend_analysis.php';
        break;
        
         case 'sales_vs_collection':
        $page_title = "تقرير المبيعات مقابل التحصيل";
        
                // =====| بداية التعديل: إعادة كتابة الاستعلام بالكامل |=====

        // 1. تحديد مستوى التجميع (لا تغيير هنا)
        $group_by_level = 'representative';
        if ($filter_rep_id === 'all' && $filter_region_id !== 'all') { $group_by_level = 'supervisor'; } 
        elseif ($filter_rep_id === 'all' && $filter_region_id === 'all') { $group_by_level = 'region'; }

        // 2. بناء حقول SELECT و GROUP BY ديناميكيًا (لا تغيير هنا)
        $select_fields = "rep.user_id, rep.full_name as group_name, sup.full_name as supervisor_name, r.name as region_name";
        $group_by_fields = "rep.user_id, rep.full_name, sup.full_name, r.name";
        if ($group_by_level === 'supervisor') {
            $select_fields = "sup.user_id, sup.full_name as group_name, r.name as region_name";
            $group_by_fields = "sup.user_id, sup.full_name, r.name";
        } elseif ($group_by_level === 'region') {
            $select_fields = "r.region_id, r.name as group_name";
            $group_by_fields = "r.region_id, r.name";
        }

        // 3. بناء الاستعلامات الفرعية للمبيعات والتحصيلات بشكل منفصل
        $sales_subquery = "SELECT representative_id, SUM(net_sales_amount) as total_sales FROM monthly_sales WHERE year = ?";
        $collection_subquery = "SELECT representative_id, SUM(collection_amount) as total_collections FROM monthly_collections WHERE year = ?";
        
        $report_params_sales = [$filter_year];
        $report_params_collections = [$filter_year];

        // تطبيق فلاتر الفترة الزمنية على كل استعلام فرعي
        if ($filter_period_type === 'monthly') {
            $sales_subquery .= " AND month = ?";
            $collection_subquery .= " AND month = ?";
            $report_params_sales[] = $filter_month;
            $report_params_collections[] = $filter_month;
        } elseif ($filter_period_type === 'quarterly') {
            $q_months = [];
            if ($filter_quarter == 1) $q_months = [1,2,3]; elseif ($filter_quarter == 2) $q_months = [4,5,6];
            elseif ($filter_quarter == 3) $q_months = [7,8,9]; elseif ($filter_quarter == 4) $q_months = [10,11,12];
            $placeholders = implode(',', array_fill(0, count($q_months), '?'));
            $sales_subquery .= " AND month IN ({$placeholders})";
            $collection_subquery .= " AND month IN ({$placeholders})";
            $report_params_sales = array_merge($report_params_sales, $q_months);
            $report_params_collections = array_merge($report_params_collections, $q_months);
        }
        
        $sales_subquery .= " GROUP BY representative_id";
        $collection_subquery .= " GROUP BY representative_id";

        // 4. بناء الاستعلام النهائي الذي يدمج كل شيء
        $sql = "SELECT 
                    {$select_fields},
                    COALESCE(SUM(sales_data.total_sales), 0) as total_sales,
                    COALESCE(SUM(collection_data.total_collections), 0) as total_collections
                FROM users rep
                LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                LEFT JOIN regions r ON sup.region_id = r.region_id
                LEFT JOIN ({$sales_subquery}) as sales_data ON rep.user_id = sales_data.representative_id
                LEFT JOIN ({$collection_subquery}) as collection_data ON rep.user_id = collection_data.representative_id
                WHERE rep.role = 'representative' AND rep.is_active = 1
        ";
        
        $final_params = array_merge($report_params_sales, $report_params_collections);

        // تطبيق الفلاتر الجغرافية على الاستعلام الخارجي
        if ($filter_region_id !== 'all') { $sql .= " AND r.region_id = ?"; $final_params[] = $filter_region_id; }
        if ($filter_rep_id !== 'all') { $sql .= " AND rep.user_id = ?"; $final_params[] = $filter_rep_id; }
        
        $sql .= " GROUP BY {$group_by_fields}
                  HAVING total_sales > 0 OR total_collections > 0
                  ORDER BY total_sales DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($final_params);
        $report_data = $stmt->fetchAll();

        
        $view_file = 'views/reports/sales_vs_collection.php';
        break;

          case 'sales_mix':
        $page_title = "تحليل مزيج المبيعات";
        
        // جلب فلتر عائلة المنتج
        $filter_family_id = $_GET['family_id'] ?? 'all';
        $selected_family_name = 'كل العائلات'; // قيمة افتراضية
// 1. بناء الجزء الأساسي من الاستعلام
        $base_sql = "
            FROM monthly_item_sales mis
            JOIN products p ON mis.product_id = p.product_id
            JOIN product_families pf ON p.family_id = pf.family_id
            JOIN users rep ON mis.representative_id = rep.user_id
            LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
        ";

        // 2. بناء الجزء الشرطي (WHERE)
        list($where_clause, $params) = build_sales_query_filters(
            $pdo, $filter_year, $filter_period_type, $filter_month,
            $filter_quarter, $filter_region_id, $filter_rep_id
        );


        if ($filter_family_id === 'all') {
            // --- المستوى الأول: عرض مساهمة كل عائلة منتجات ---
            $page_title .= " (حسب عائلة المنتج)";
            $sql = "SELECT 
                        pf.family_id,
                        pf.name as group_name,
                        SUM(mis.quantity_sold) as total_quantity,
                        SUM(mis.total_value) as total_value
                    {$base_sql}
                    {$where_clause}
                    GROUP BY pf.family_id, pf.name
                    ORDER BY total_value DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $report_data = $stmt->fetchAll();

        } else {
            // --- المستوى الثاني: عرض مساهمة المنتجات داخل عائلة محددة ---
            $stmt_fam_name = $pdo->prepare("SELECT name FROM product_families WHERE family_id = ?");
            $stmt_fam_name->execute([$filter_family_id]);
            $selected_family_name = $stmt_fam_name->fetchColumn();
            $page_title .= " (منتجات عائلة: " . htmlspecialchars($selected_family_name) . ")";

            // إضافة فلتر العائلة إلى جملة WHERE
            $where_clause .= " AND p.family_id = ?";
            $params[] = $filter_family_id;

            $sql = "SELECT 
                        p.product_id,
                        p.name as group_name,
                        SUM(mis.quantity_sold) as total_quantity,
                        SUM(mis.total_value) as total_value
                    {$base_sql}
                    {$where_clause}
                    GROUP BY p.product_id, p.name
                    ORDER BY total_value DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $report_data = $stmt->fetchAll();
        }
        // =====| نهاية التعديل |=====

        $view_file = 'views/reports/sales_mix.php';
        break;
        
            case 'lost_customers':
        $page_title = "تقرير العملاء المفقودين";
   // 1. جلب ID العملاء الذين اشتروا في الفترة الحالية
        $sql_current = "SELECT DISTINCT c.customer_id 
                        FROM monthly_item_sales mis
                        JOIN customers c ON mis.customer_id = c.customer_id
                        WHERE STR_TO_DATE(CONCAT(mis.year, '-', mis.month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?";
        $stmt_current = $pdo->prepare($sql_current);
        $stmt_current->execute([$filter_current_start, $filter_current_end]);
        $current_customers_ids = $stmt_current->fetchAll(PDO::FETCH_COLUMN);
        
        // إضافة عنصر نائب (placeholder) لتجنب خطأ SQL إذا كانت القائمة فارغة
        if (empty($current_customers_ids)) {
            $current_customers_ids[] = 0; 
        }
        $current_placeholders = implode(',', array_fill(0, count($current_customers_ids), '?'));

        // 2. جلب العملاء الذين اشتروا في الفترة السابقة ولكنهم "ليسوا ضمن" قائمة عملاء الفترة الحالية
        $sql_lost = "SELECT 
                        c.customer_id, 
                        c.name as customer_name, 
                        c.customer_code, 
                        rep.full_name as representative_name,
                        MAX(STR_TO_DATE(CONCAT(mis.year, '-', mis.month, '-01'), '%Y-%m-%d')) as last_purchase_date
                     FROM monthly_item_sales mis
                     JOIN customers c ON mis.customer_id = c.customer_id
                     JOIN users rep ON c.representative_id = rep.user_id
                     LEFT JOIN users sup ON rep.supervisor_id = sup.user_id
                     WHERE STR_TO_DATE(CONCAT(mis.year, '-', mis.month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?
                     AND c.customer_id NOT IN ({$current_placeholders})";
        
        $report_params = [$filter_previous_start, $filter_previous_end];
        $report_params = array_merge($report_params, $current_customers_ids);

        // تطبيق الفلاتر الجغرافية
        if ($filter_region_id !== 'all' && is_numeric($filter_region_id)) { 
            $sql_lost .= " AND sup.region_id = ?"; 
            $report_params[] = $filter_region_id; 
        }
        if ($filter_rep_id !== 'all' && is_numeric($filter_rep_id)) { 
            $sql_lost .= " AND c.representative_id = ?"; 
            $report_params[] = $filter_rep_id; 
        }
        
        $sql_lost .= " GROUP BY c.customer_id, c.name, c.customer_code, rep.full_name ORDER BY c.name";

        $stmt_lost = $pdo->prepare($sql_lost);
        $stmt_lost->execute($report_params);
        $report_data = $stmt_lost->fetchAll();
        
        // =====| نهاية التعديل |=====
        
        $view_file = 'views/reports/lost_customers.php';
        break;
        

    case 'dashboard':
    default:
        $view_file = 'views/reports/dashboard.php';
        break;
}
function get_date_range_for_period($year, $period_type, $month, $quarter) {
    if ($period_type === 'monthly') {
        $start_date = date('Y-m-d', strtotime("{$year}-{$month}-01"));
        $end_date = date('Y-m-t', strtotime($start_date));
    } elseif ($period_type === 'quarterly') {
        $start_month = ($quarter - 1) * 3 + 1;
        $end_month = $start_month + 2;
        $start_date = date('Y-m-d', strtotime("{$year}-{$start_month}-01"));
        $end_date = date('Y-m-t', strtotime("{$year}-{$end_month}-01"));
    } else { // annually
        $start_date = "{$year}-01-01";
        $end_date = "{$year}-12-31";
    }
    return [$start_date, $end_date];
}
/**
 * دالة مساعدة لبناء الجزء الشرطي (WHERE) ومصفوفة البارامترات للاستعلامات.
 */
function build_sales_query_filters($pdo, $year, $period_type, $month, $quarter, $region_id, $rep_id) {
    $where_clauses = ["mis.year = ?"];
    $params = [$year];

    if ($period_type === 'monthly') {
        $where_clauses[] = "mis.month = ?";
        $params[] = $month;
    } elseif ($period_type === 'quarterly') {
        $q_months = [];
        if ($quarter == 1) $q_months = [1, 2, 3];
        elseif ($quarter == 2) $q_months = [4, 5, 6];
        elseif ($quarter == 3) $q_months = [7, 8, 9];
        elseif ($quarter == 4) $q_months = [10, 11, 12];
        
        if (!empty($q_months)) {
            $placeholders = implode(',', array_fill(0, count($q_months), '?'));
            $where_clauses[] = "mis.month IN ({$placeholders})";
            $params = array_merge($params, $q_months);
        } else {
             $where_clauses[] = "1=0";
        }
    }
    
    if ($region_id !== 'all' && is_numeric($region_id)) {
        $where_clauses[] = "sup.region_id = ?";
        $params[] = $region_id;
    }

    if ($rep_id !== 'all' && is_numeric($rep_id)) {
        $where_clauses[] = "mis.representative_id = ?";
        $params[] = $rep_id;
    }

    // تطبيق صلاحيات المستخدم على البيانات
    if ($_SESSION['user_role'] === 'supervisor') {
         $where_clauses[] = "rep.supervisor_id = ?";
         $params[] = $_SESSION['user_id'];
    } elseif ($_SESSION['user_role'] === 'representative') {
        $where_clauses[] = "mis.representative_id = ?";
        $params[] = $_SESSION['user_id'];
    }

    $where_clause_string = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
    
    return [$where_clause_string, $params];
}

include 'views/layout.php';