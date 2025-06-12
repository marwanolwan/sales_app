<?php
// controllers/regions.php

require_permission('manage_regions');

$action = $_GET['action'] ?? 'list';
$region_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = "إدارة المناطق";
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة منطقة جديدة' : 'تعديل بيانات المنطقة';
        
        $region_data = null;
        if ($action == 'edit' && $region_id) {
            $stmt = $pdo->prepare("SELECT * FROM regions WHERE region_id = ?");
            $stmt->execute([$region_id]);
            $region_data = $stmt->fetch();
            if (!$region_data) {
                $_SESSION['error_message'] = "المنطقة غير موجودة.";
                header("Location: index.php?page=regions");
                exit();
            }
        }
        $view_file = 'views/regions/form.php';
        break;

    case 'list':
    default:
        $stmt_regions = $pdo->query("SELECT r.*, COUNT(u.user_id) as supervisor_count 
                                     FROM regions r
                                     LEFT JOIN users u ON r.region_id = u.region_id AND u.role = 'supervisor'
                                     GROUP BY r.region_id
                                     ORDER BY r.name ASC");
        $regions = $stmt_regions->fetchAll();
        $view_file = 'views/regions/list.php';
        break;
}

include 'views/layout.php';