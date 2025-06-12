<?php
// controllers/product_families.php

require_permission('manage_product_families');

$action = $_GET['action'] ?? 'list';
$family_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = "إدارة عائلات المنتجات (الشركات)";
$view_file = '';

// تعريف المسار لتسهيل استخدامه في الواجهات
define('PRODUCT_FAMILIES_LOGO_DIR', 'uploads/product_families_logos/');

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة عائلة منتج جديدة' : 'تعديل عائلة المنتج';
        
        $family_data = null;
        if ($action == 'edit' && $family_id) {
            $stmt = $pdo->prepare("SELECT * FROM product_families WHERE family_id = ?");
            $stmt->execute([$family_id]);
            $family_data = $stmt->fetch();
            if (!$family_data) {
                $_SESSION['error_message'] = "عائلة المنتج غير موجودة.";
                header("Location: index.php?page=product_families");
                exit();
            }
        }
        $view_file = 'views/product_families/form.php';
        break;

    case 'list':
    default:
        $stmt_families = $pdo->query("SELECT pf.*, COUNT(p.product_id) as product_count 
                                       FROM product_families pf
                                       LEFT JOIN products p ON pf.family_id = p.family_id
                                       GROUP BY pf.family_id
                                       ORDER BY pf.name ASC");
        $product_families = $stmt_families->fetchAll();
        $view_file = 'views/product_families/list.php';
        break;
}

include 'views/layout.php';