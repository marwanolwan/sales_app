<?php
// controllers/customer_categories.php

require_permission('manage_customer_categories');

$action = $_GET['action'] ?? 'list';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = "إدارة تصنيفات العملاء";
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة تصنيف جديد' : 'تعديل تصنيف العميل';
        
        $category_data = null;
        if ($action == 'edit' && $category_id) {
            $stmt = $pdo->prepare("SELECT * FROM customer_categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $category_data = $stmt->fetch();
            if (!$category_data) {
                $_SESSION['error_message'] = "تصنيف العميل غير موجود.";
                header("Location: index.php?page=customer_categories");
                exit();
            }
        }
        $view_file = 'views/customer_categories/form.php';
        break;

    case 'list':
    default:
        // استعلام محسن لجلب عدد العملاء المرتبطين بكل تصنيف
        $stmt_categories = $pdo->query("SELECT cc.*, COUNT(c.customer_id) as customer_count 
                                         FROM customer_categories cc
                                         LEFT JOIN customers c ON cc.category_id = c.category_id
                                         GROUP BY cc.category_id, cc.name, cc.description, cc.created_at, cc.updated_at
                                         ORDER BY cc.name ASC");
        $categories = $stmt_categories->fetchAll();
        $view_file = 'views/customer_categories/list.php';
        break;
}

include 'views/layout.php';