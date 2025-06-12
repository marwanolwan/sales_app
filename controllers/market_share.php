<?php
// controllers/market_share.php

require_permission('manage_market_share');

$action = $_GET['action'] ?? 'report';
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

$page_title = "تحليل الحصة السوقية";
$view_file = '';

switch ($action) {
    case 'data_entry':
        $page_title = "إدخال بيانات الحصة السوقية";

        // تحديد العميل والفترة للعمل عليها
        $customer_id_entry = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $report_period_entry = $_GET['report_period'] ?? date('Y-m');

        // جلب قائمة العملاء (مع مراعاة صلاحيات المستخدم)
        $customer_sql = "SELECT customer_id, name, customer_code FROM customers WHERE status = 'active'";
        $customer_params = [];
        if ($current_user_role == 'supervisor') {
            $customer_sql .= " AND representative_id IN (SELECT user_id FROM users WHERE supervisor_id = ?)";
            $customer_params[] = $current_user_id;
        } elseif ($current_user_role == 'representative') {
            $customer_sql .= " AND representative_id = ?";
            $customer_params[] = $current_user_id;
        }
        $customer_sql .= " ORDER BY name ASC";
        $stmt_cust = $pdo->prepare($customer_sql);
        $stmt_cust->execute($customer_params);
        $customers_list = $stmt_cust->fetchAll();

        // جلب قائمة منتجاتنا
        $our_products = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = TRUE ORDER BY name ASC")->fetchAll();
        
        $existing_entries = [];
        if ($customer_id_entry && $report_period_entry) {
            $sql = "SELECT * FROM market_share_entries WHERE customer_id = ? AND report_period = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id_entry, $report_period_entry]);
            $existing_entries = $stmt->fetchAll();
        }

        $view_file = 'views/market_share/data_entry_form.php';
        break;

case 'report':
    default:
        $page_title = "تقرير الحصة السوقية";
        
        $filter_period = $_GET['period'] ?? '';
        $filter_rep = $_GET['rep_id'] ?? 'all';
        $filter_customer_search = trim($_GET['customer_search'] ?? '');

        $available_periods = $pdo->query("SELECT DISTINCT report_period FROM market_share_entries ORDER BY report_period DESC")->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($filter_period) && !empty($available_periods)) {
            $filter_period = $available_periods[0];
        }
        
        $reps_for_filter = $pdo->query("SELECT user_id, full_name FROM users WHERE role='representative' AND is_active=TRUE ORDER BY full_name ASC")->fetchAll();

        // =====| بداية التعديل الجذري للاستعلام |=====
        
        $report_data_grouped = []; // المصفوفة الجديدة لتخزين البيانات المجمعة
        $total_market_quantity = 0;

        if (!empty($filter_period)) {
            $sql = "SELECT 
                        c.customer_id,
                        c.name as customer_name,
                        c.customer_code,
                        ms.product_name,
                        ms.is_our_product,
                        SUM(ms.quantity_sold) as total_quantity
                    FROM market_share_entries ms
                    JOIN customers c ON ms.customer_id = c.customer_id
                    WHERE ms.report_period = :period";
            $params = [':period' => $filter_period];
            
            if ($filter_rep !== 'all' && is_numeric($filter_rep)) {
                $sql .= " AND c.representative_id = :rep_id";
                $params[':rep_id'] = $filter_rep;
            }
            if (!empty($filter_customer_search)) {
                $sql .= " AND (c.name LIKE :search OR c.customer_code LIKE :search)";
                $params[':search'] = "%{$filter_customer_search}%";
            }

            $sql .= " GROUP BY c.customer_id, c.name, c.customer_code, ms.product_name, ms.is_our_product ORDER BY c.name, total_quantity DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $flat_report_data = $stmt->fetchAll();

            // تجميع البيانات في هيكل هرمي
            foreach ($flat_report_data as $row) {
                $customer_id = $row['customer_id'];
                
                if (!isset($report_data_grouped[$customer_id])) {
                    $report_data_grouped[$customer_id] = [
                        'customer_info' => [
                            'name' => $row['customer_name'],
                            'code' => $row['customer_code']
                        ],
                        'products' => [],
                        'total_customer_quantity' => 0
                    ];
                }
                
                $report_data_grouped[$customer_id]['products'][] = [
                    'name' => $row['product_name'],
                    'is_our' => $row['is_our_product'],
                    'quantity' => $row['total_quantity']
                ];
                $report_data_grouped[$customer_id]['total_customer_quantity'] += $row['total_quantity'];
            }
        }
        // =====| نهاية التعديل الجذري للاستعلام |=====
        
        $view_file = 'views/market_share/report.php';
        break;
}

include 'views/layout.php';