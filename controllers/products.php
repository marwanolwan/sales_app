<?php
// controllers/products.php

require_permission('manage_products');

$action = $_GET['action'] ?? 'list';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = "إدارة المنتجات (الأصناف)";
$view_file = '';

// تعريف المسارات لتسهيل استخدامها
define('PRODUCTS_IMAGE_DIR', 'uploads/products_images/');

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة منتج جديد' : 'تعديل بيانات المنتج';
        
        $product_families_list = $pdo->query("SELECT family_id, name FROM product_families WHERE is_active = TRUE ORDER BY name ASC")->fetchAll();
        
        $product_data = null;
        if ($action == 'edit' && $product_id) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $product_data = $stmt->fetch();
            if (!$product_data) {
                $_SESSION['error_message'] = "المنتج غير موجود.";
                header("Location: index.php?page=products");
                exit();
            }
        }
        $view_file = 'views/products/form.php';
        break;

    case 'import':
        $page_title = "استيراد المنتجات من ملف Excel";
        $view_file = 'views/products/import.php';
        break;
        
    case 'import_preview':
        // هذا الإجراء يتم معالجته بالكامل في ملف الـ action الخاص به
        // لكننا نجهز الواجهة هنا
        $page_title = "معاينة استيراد المنتجات";
        // البيانات ستكون مخزنة في الجلسة بواسطة `product_import_preview.php`
        $preview_data = $_SESSION['import_preview_data']['data'] ?? [];
        $import_errors = $_SESSION['import_preview_data']['errors'] ?? [];
        $has_errors = !empty($import_errors);
        
        if (empty($preview_data)) {
             $_SESSION['error_message'] = "لا توجد بيانات للمعاينة أو انتهت صلاحية الجلسة.";
             header("Location: index.php?page=products&action=import");
             exit();
        }
        $view_file = 'views/products/import_preview.php';
        break;
        
      case 'list':
    default:
        // =====| بداية التعديلات للبحث والترقيم |=====
         $filter_new = $_GET['filter_new'] ?? 'all';
        // 1. تحديد متغيرات البحث والترقيم
        $search_term = trim($_GET['search'] ?? '');
        $page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page_num < 1) $page_num = 1;
        $items_per_page = 20; // عدد المنتجات في كل صفحة
        $offset = ($page_num - 1) * $items_per_page;

        // 2. بناء الاستعلام الأساسي
        $base_sql = "FROM products p
                     LEFT JOIN product_families pf ON p.family_id = pf.family_id";
        
        $where_clauses = [];
        $params = [];

        if ($filter_new === 'new_only') {
        $where_clauses[] = "(p.is_new_product = 1 AND (p.new_product_end_date IS NULL OR p.new_product_end_date >= CURDATE()))";
    }

        // 3. إضافة شرط البحث (إذا كان موجوداً)
        if (!empty($search_term)) {
            $where_clauses[] = "(p.name LIKE ? OR p.product_code LIKE ? OR pf.name LIKE ?)";
            $search_param = "%{$search_term}%";
            array_push($params, $search_param, $search_param, $search_param);
        }

        $sql_where = "";
        if (!empty($where_clauses)) {
            $sql_where = " WHERE " . implode(' AND ', $where_clauses);
        }

        // 4. استعلام لحساب العدد الإجمالي للمنتجات
        $total_items_sql = "SELECT COUNT(p.product_id) " . $base_sql . $sql_where;
        $stmt_total = $pdo->prepare($total_items_sql);
        $stmt_total->execute($params);
        $total_items = $stmt_total->fetchColumn();
        $total_pages = ceil($total_items / $items_per_page);
        
        if ($page_num > $total_pages && $total_pages > 0) {
            $page_num = $total_pages;
            $offset = ($page_num - 1) * $items_per_page;
        }

        // 5. استعلام لجلب بيانات الصفحة الحالية
        $data_sql = "SELECT p.*, pf.name as family_name " . $base_sql . $sql_where . " ORDER BY p.name ASC LIMIT ? OFFSET ?";
        $stmt_products = $pdo->prepare($data_sql);
        
        // ربط البارامترات
        $param_index = 1;
        foreach ($params as $param) {
            $stmt_products->bindValue($param_index++, $param);
        }
        $stmt_products->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
        $stmt_products->bindValue($param_index++, $offset, PDO::PARAM_INT);
        
        $stmt_products->execute();
        $products_list_display = $stmt_products->fetchAll();

        $view_file = 'views/products/list.php';
        // =====| نهاية التعديلات للبحث والترقيم |=====
        break;
}

include 'views/layout.php';