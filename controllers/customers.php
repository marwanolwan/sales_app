<?php
// controllers/customers.php

require_permission('manage_customers');

$page_title = "إدارة العملاء";
$action = $_GET['action'] ?? 'list';
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$view_file = '';
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// جلب البيانات اللازمة للنماذج والواجهات
$customer_categories = $pdo->query("SELECT category_id, name FROM customer_categories ORDER BY name ASC")->fetchAll();
$representatives = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE ORDER BY full_name ASC")->fetchAll();
$promoters = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'promoter' AND is_active = TRUE ORDER BY full_name ASC")->fetchAll();
$main_accounts = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE is_main_account = TRUE ORDER BY name ASC")->fetchAll();

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة عميل جديد' : 'تعديل بيانات العميل';
        $customer_data = null;
        if ($action == 'edit' && $customer_id) {
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
            $stmt->execute([$customer_id]);
            $customer_data = $stmt->fetch();
            if (!$customer_data) {
                $_SESSION['error_message'] = "العميل غير موجود.";
                header("Location: index.php?page=customers");
                exit();
            }
        }
        $view_file = 'views/customers/form.php';
        break;

    case 'import':
        $page_title = "استيراد العملاء من Excel";
        $view_file = 'views/customers/import.php';
        break;
        
    case 'import_preview':
        $page_title = "معاينة استيراد العملاء";
        $preview_data = $_SESSION['import_preview_data'] ?? [];
        $import_errors = $_SESSION['import_errors'] ?? [];
        $import_file_name = $_SESSION['import_file_name'] ?? '';
        if (empty($preview_data)) {
            $_SESSION['error_message'] = "لا توجد بيانات للمعاينة. يرجى رفع الملف أولاً.";
            header("Location: index.php?page=customers&action=import");
            exit();
        }
        unset($_SESSION['import_preview_data'], $_SESSION['import_errors'], $_SESSION['import_file_name']);
        $view_file = 'views/customers/import_preview.php';
        break;

    case 'view_branches':
        if (!$customer_id) {
            header("Location: index.php?page=customers"); exit();
        }
        $stmt = $pdo->prepare("SELECT name, customer_code FROM customers WHERE customer_id = ? AND is_main_account = TRUE");
        $stmt->execute([$customer_id]);
        $main_account_info = $stmt->fetch();
        if (!$main_account_info) {
             $_SESSION['error_message'] = "هذا الحساب ليس حسابًا رئيسيًا أو غير موجود.";
             header("Location: index.php?page=customers"); exit();
        }
        $page_title = "فروع العميل الرئيسي: " . htmlspecialchars($main_account_info['name']);
        $stmt_branches = $pdo->prepare("SELECT c.*, cat.name as category_name, rep.full_name as representative_name FROM customers c LEFT JOIN customer_categories cat ON c.category_id = cat.category_id LEFT JOIN users rep ON c.representative_id = rep.user_id WHERE c.main_account_id = ? ORDER BY c.name ASC");
        $stmt_branches->execute([$customer_id]);
        $branches = $stmt_branches->fetchAll();
        $view_file = 'views/customers/branches.php';
        break;

    case 'list':
    default:
        // =====| بداية التعديلات للبحث والترقيم |=====

        // 1. تحديد متغيرات البحث والترقيم
        $search_term = trim($_GET['search'] ?? '');
        $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page_num < 1) $page_num = 1;
        $items_per_page = 20; // عدد العناصر في كل صفحة
        $offset = ($page_num - 1) * $items_per_page;

        // 2. بناء الاستعلام الأساسي مع الفلاتر الأمنية حسب دور المستخدم
        $base_sql = "FROM customers c
                     LEFT JOIN customer_categories cat ON c.category_id = cat.category_id
                     LEFT JOIN users rep ON c.representative_id = rep.user_id
                     LEFT JOIN users promo ON c.promoter_id = promo.user_id
                     LEFT JOIN customers main_acc ON c.main_account_id = main_acc.customer_id";
        
        $where_clauses = [];
        $params = [];

        // فلترة الصلاحيات
        if ($current_user_role == 'supervisor') {
            $stmt_team = $pdo->prepare("SELECT user_id FROM users WHERE supervisor_id = ? AND role IN ('representative', 'promoter')");
            $stmt_team->execute([$current_user_id]);
            $team_ids = $stmt_team->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($team_ids)) {
                $placeholders = implode(',', array_fill(0, count($team_ids), '?'));
                $where_clauses[] = "(c.representative_id IN ({$placeholders}) OR c.promoter_id IN ({$placeholders}))";
                $params = array_merge($params, $team_ids, $team_ids);
            } else {
                $where_clauses[] = "1=0"; // لا يوجد فريق = لا يوجد عملاء
            }
        } elseif (in_array($current_user_role, ['representative', 'promoter'])) {
            $field_to_check = ($current_user_role == 'representative') ? 'c.representative_id' : 'c.promoter_id';
            $where_clauses[] = "{$field_to_check} = ?";
            $params[] = $current_user_id;
        }

        // 3. إضافة شرط البحث
        if (!empty($search_term)) {
            $where_clauses[] = "(c.name LIKE ? OR c.customer_code LIKE ? OR rep.full_name LIKE ?)";
            $search_param = "%{$search_term}%";
            array_push($params, $search_param, $search_param, $search_param);
        }

        $sql_where = "";
        if (!empty($where_clauses)) {
            $sql_where = " WHERE " . implode(' AND ', $where_clauses);
        }

        // 4. استعلام لحساب العدد الإجمالي للنتائج (للترقيم)
        $total_items_sql = "SELECT COUNT(c.customer_id) " . $base_sql . $sql_where;
        $stmt_total = $pdo->prepare($total_items_sql);
        $stmt_total->execute($params);
        $total_items = $stmt_total->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);
        if ($page_num > $total_pages && $total_pages > 0) { // تصحيح إذا كان رقم الصفحة أكبر من المتاح
            $page_num = $total_pages;
            $offset = ($page_num - 1) * $items_per_page;
        }


        // 5. استعلام لجلب البيانات للصفحة الحالية فقط
        $data_sql = "SELECT c.*, cat.name as category_name, rep.full_name as representative_name, promo.full_name as promoter_name,
                     (SELECT COUNT(*) FROM customers br WHERE br.main_account_id = c.customer_id) as branch_count,
                     main_acc.name as main_account_name_display, main_acc.customer_code as main_account_code_display "
                     . $base_sql . $sql_where . " ORDER BY c.is_main_account DESC, c.name ASC LIMIT ? OFFSET ?";
        
        $stmt_customers = $pdo->prepare($data_sql);
        
        // ربط البارامترات مع إضافة LIMIT و OFFSET
        $param_index = 1;
        foreach ($params as $param) {
            $stmt_customers->bindValue($param_index++, $param);
        }
        $stmt_customers->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
        $stmt_customers->bindValue($param_index++, $offset, PDO::PARAM_INT);

        $stmt_customers->execute();
        $customers = $stmt_customers->fetchAll();
        
        $view_file = 'views/customers/list.php';
        // =====| نهاية التعديلات للبحث والترقيم |=====
        break;
}


include 'views/layout.php';