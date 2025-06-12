<?php
// controllers/dashboard.php (FINAL CORRECTED VERSION for NULL values)

require_permission('view_dashboard_summaries');

$page_title = "لوحة التحكم";
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// --- إعدادات الفترة الزمنية الرئيسية (الشهر والسنة الحالية) ---
$current_year = date('Y');
$current_month = date('n');

// --- بناء شروط الصلاحيات الديناميكية ---
$scope_params = [];
$scope_sql_user = "";         // شرط يطبق على جدول users as u
$scope_sql_sales_rep = "";    // شرط يطبق على جدول users as rep (في استعلامات المبيعات)

if ($current_user_role == 'supervisor') {
    $scope_sql_user = " AND u.supervisor_id = :user_id";
    $scope_sql_sales_rep = " AND rep.supervisor_id = :user_id";
    $scope_params[':user_id'] = $current_user_id;
} elseif ($current_user_role == 'representative') {
    $scope_sql_user = " AND u.user_id = :user_id";
    $scope_sql_sales_rep = " AND rep.user_id = :user_id";
    $scope_params[':user_id'] = $current_user_id;
}


// =================================================================
// 1. بطاقات الملخص السريع (KPIs)
// =================================================================

// -- إجمالي المبيعات النقدية للشهر الحالي --
$total_cash_sales_sql = "
    SELECT COALESCE(SUM(ms.net_sales_amount), 0) 
    FROM monthly_sales ms
    JOIN users rep ON ms.representative_id = rep.user_id
    WHERE ms.year = :year AND ms.month = :month {$scope_sql_sales_rep}";
$stmt_cash_sales = $pdo->prepare($total_cash_sales_sql);
$stmt_cash_sales->execute(array_merge($scope_params, [':year' => $current_year, ':month' => $current_month]));
$total_cash_sales = $stmt_cash_sales->fetchColumn();

// -- نسبة تحقيق الهدف النقدي الإجمالي للشهر الحالي --
$total_target_sql = "
    SELECT COALESCE(SUM(st.target_amount), 0)
    FROM sales_targets st
    JOIN users rep ON st.representative_id = rep.user_id
    WHERE st.year = :year AND st.month = :month {$scope_sql_sales_rep}";
$stmt_target = $pdo->prepare($total_target_sql);
$stmt_target->execute(array_merge($scope_params, [':year' => $current_year, ':month' => $current_month]));
$total_cash_target = $stmt_target->fetchColumn();
$overall_achievement_percentage = ($total_cash_target > 0) ? round(($total_cash_sales / $total_cash_target) * 100) : 0;

// -- عدد العملاء الجدد هذا الشهر --
$new_customers_sql = "
    SELECT COUNT(c.customer_id) 
    FROM customers c
    LEFT JOIN users rep ON c.representative_id = rep.user_id
    WHERE YEAR(c.opening_date) = :year AND MONTH(c.opening_date) = :month {$scope_sql_sales_rep}";
$stmt_new_cust = $pdo->prepare($new_customers_sql);
$stmt_new_cust->execute(array_merge($scope_params, [':year' => $current_year, ':month' => $current_month]));
$new_customers_count = $stmt_new_cust->fetchColumn();

// -- عدد الأصناف المباعة المختلفة هذا الشهر --
$distinct_items_sql = "
    SELECT COUNT(DISTINCT mis.product_id)
    FROM monthly_item_sales mis
    JOIN users rep ON mis.representative_id = rep.user_id
    WHERE mis.year = :year AND mis.month = :month {$scope_sql_sales_rep}";
$stmt_distinct_items = $pdo->prepare($distinct_items_sql);
$stmt_distinct_items->execute(array_merge($scope_params, [':year' => $current_year, ':month' => $current_month]));
$distinct_items_count = $stmt_distinct_items->fetchColumn();


// =================================================================
// 2. بيانات الرسوم البيانية والقوائم
// =================================================================

// -- نسبة تحقيق كل مندوب لهدف الشهر الحالي (للرسم البياني) --
$reps_achievement_sql = "
    SELECT 
        u.full_name,
        COALESCE(SUM(ms.net_sales_amount), 0) as total_sales,
        COALESCE(SUM(st.target_amount), 0) as total_target,
        (CASE WHEN COALESCE(SUM(st.target_amount), 0) > 0 THEN (COALESCE(SUM(ms.net_sales_amount), 0) / SUM(st.target_amount)) * 100 ELSE 0 END) as achievement_percentage
    FROM users u
    LEFT JOIN monthly_sales ms ON u.user_id = ms.representative_id AND ms.year = :year_ms AND ms.month = :month_ms
    LEFT JOIN sales_targets st ON u.user_id = st.representative_id AND st.year = :year_st AND st.month = :month_st
    WHERE u.role = 'representative' AND u.is_active = TRUE
    {$scope_sql_user}
    GROUP BY u.user_id, u.full_name
    HAVING total_sales > 0 OR total_target > 0
    ORDER BY achievement_percentage DESC
";
$reps_achievement_params = array_merge($scope_params, [
    ':year_ms' => $current_year, ':month_ms' => $current_month,
    ':year_st' => $current_year, ':month_st' => $current_month,
]);
$stmt_reps_achievement = $pdo->prepare($reps_achievement_sql);
$stmt_reps_achievement->execute($reps_achievement_params);
$reps_achievement_data = $stmt_reps_achievement->fetchAll(PDO::FETCH_ASSOC);


// --- أفضل 5 أصناف مبيعًا (قيمةً) ---
$top_products_sql = "
    SELECT 
        p.name, 
        COALESCE(SUM(mis.total_value), SUM(mis.quantity_sold * COALESCE(mis.unit_price, 0)), 0) as total_sales_value
    FROM monthly_item_sales mis
    JOIN products p ON mis.product_id = p.product_id
    JOIN users rep ON mis.representative_id = rep.user_id
    WHERE mis.year = :year AND mis.month = :month
    {$scope_sql_sales_rep}
    GROUP BY p.product_id, p.name
    HAVING total_sales_value > 0 
    ORDER BY total_sales_value DESC 
    LIMIT 5
";
$top_products_params = array_merge($scope_params, [':year' => $current_year, ':month' => $current_month]);
$stmt_top_products = $pdo->prepare($top_products_sql);
$stmt_top_products->execute($top_products_params);
$top_products = $stmt_top_products->fetchAll(PDO::FETCH_ASSOC);


// --- أفضل 5 عملاء (قيمةً) ---
$top_customers_sql = "
    SELECT 
        c.name, 
        COALESCE(SUM(mis.total_value), SUM(mis.quantity_sold * COALESCE(mis.unit_price, 0)), 0) as total_sales_value
    FROM monthly_item_sales mis
    JOIN customers c ON mis.customer_id = c.customer_id
    JOIN users rep ON mis.representative_id = rep.user_id
    WHERE mis.year = :year AND mis.month = :month
    {$scope_sql_sales_rep}
    GROUP BY c.customer_id, c.name
    HAVING total_sales_value > 0 
    ORDER BY total_sales_value DESC 
    LIMIT 5
";
$top_customers_params = array_merge($scope_params, [':year' => $current_year, ':month' => $current_month]);
$stmt_top_customers = $pdo->prepare($top_customers_sql);
$stmt_top_customers->execute($top_customers_params);
$top_customers = $stmt_top_customers->fetchAll(PDO::FETCH_ASSOC);


// =================================================================
// تحميل الواجهة
// =================================================================
$view_file = 'views/dashboard.php';
include 'views/layout.php';