<?php
// controllers/posm.php

require_permission('manage_assets'); // يمكن إعادة استخدام هذه الصلاحية أو إنشاء صلاحية جديدة 'manage_posm'

$action = $_GET['action'] ?? 'dashboard';
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = "إدارة المواد الترويجية (POSM)";
$view_file = '';

switch ($action) {
    case 'add_item':
    case 'edit_item':
        $page_title = ($action == 'add_item') ? 'إضافة مادة ترويجية جديدة' : 'تعديل بيانات المادة';
        
        $item_data = null;
        if ($action == 'edit_item' && $item_id) {
            $stmt = $pdo->prepare("SELECT * FROM posm_items WHERE item_id = ?");
            $stmt->execute([$item_id]);
            $item_data = $stmt->fetch();
            if (!$item_data) {
                $_SESSION['error_message'] = "المادة الترويجية غير موجودة.";
                header("Location: index.php?page=posm&action=items_list");
                exit();
            }
        }
        $view_file = 'views/posm/items_form.php';
        break;

    case 'items_list':
        $page_title = "قائمة المواد الترويجية المعرفة";
        
        // جلب قائمة المواد مع رصيدها الحالي
        $sql = "SELECT 
                    pi.item_id, 
                    pi.item_name, 
                    pi.item_code, 
                    (SELECT SUM(CASE 
                                WHEN psm.movement_type = 'Stock In' THEN psm.quantity
                                ELSE -psm.quantity 
                               END) 
                     FROM posm_stock_movements psm 
                     WHERE psm.item_id = pi.item_id) as current_stock
                FROM posm_items pi
                ORDER BY pi.item_name ASC";
        
        $items_list = $pdo->query($sql)->fetchAll();
        $view_file = 'views/posm/items_list.php';
        break;

    case 'stock_entry':
        $page_title = "إدارة مخزون المواد الترويجية";
        
        // جلب البيانات اللازمة للنموذج
        $posm_items = $pdo->query("SELECT item_id, item_name, item_code FROM posm_items ORDER BY item_name ASC")->fetchAll();
        $promoters = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'promoter' AND is_active = TRUE ORDER BY full_name ASC")->fetchAll();
        $customers = $pdo->query("SELECT customer_id, name, customer_code FROM customers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        $view_file = 'views/posm/stock_form.php';
        break;
        
    case 'history':
        $page_title = "سجل حركات المخزون";
        if (!$item_id) {
            $_SESSION['error_message'] = "يرجى تحديد مادة ترويجية لعرض سجلها.";
            header("Location: index.php?page=posm&action=items_list");
            exit();
        }

        $item_stmt = $pdo->prepare("SELECT item_name, item_code FROM posm_items WHERE item_id = ?");
        $item_stmt->execute([$item_id]);
        $item_info = $item_stmt->fetch();
        if (!$item_info) {
             $_SESSION['error_message'] = "المادة الترويجية غير موجودة.";
             header("Location: index.php?page=posm&action=items_list");
             exit();
        }
        $page_title .= " لـ: " . htmlspecialchars($item_info['item_name']);
        
        // جلب سجل الحركات لهذه المادة
        $sql_history = "SELECT 
                            psm.*,
                            u.full_name as rep_name,
                            c.name as customer_name
                        FROM posm_stock_movements psm
                        LEFT JOIN users u ON psm.user_id = u.user_id
                        LEFT JOIN customers c ON psm.customer_id = c.customer_id
                        WHERE psm.item_id = ?
                        ORDER BY psm.movement_date DESC";
        $history_stmt = $pdo->prepare($sql_history);
        $history_stmt->execute([$item_id]);
        $stock_history = $history_stmt->fetchAll();

        $view_file = 'views/posm/history.php';
        break;

    case 'dashboard':
    default:
        $page_title = "لوحة تحكم المواد الترويجية";

        // 1. الرصيد الحالي لكل مادة ترويجية
        $sql_stock = "SELECT 
                        pi.item_id, 
                        pi.item_name, 
                        (SELECT SUM(CASE 
                                    WHEN psm.movement_type = 'Stock In' THEN psm.quantity
                                    ELSE -psm.quantity 
                                   END) 
                         FROM posm_stock_movements psm 
                         WHERE psm.item_id = pi.item_id) as current_stock
                    FROM posm_items pi
                    ORDER BY pi.item_name ASC";
        $stock_summary = $pdo->query($sql_stock)->fetchAll();

        // 2. رصيد كل مندوب من المواد
         $sql_promoter_stock = "SELECT 
                                    u.full_name as promoter_name,
                                    pi.item_name,
                                    SUM(CASE 
                                        WHEN psm.movement_type = 'Dispatch to Rep' THEN psm.quantity
                                        ELSE -psm.quantity 
                                       END) as promoter_balance
                                  FROM posm_stock_movements psm
                                  JOIN posm_items pi ON psm.item_id = pi.item_id
                                  JOIN users u ON psm.user_id = u.user_id
                                  WHERE psm.movement_type IN ('Dispatch to Rep', 'Deliver to Customer')
                                  AND u.role = 'promoter' -- التأكد من أن المستخدم هو مروج
                                  GROUP BY u.user_id, pi.item_id
                                  HAVING promoter_balance > 0
                                  ORDER BY u.full_name, pi.item_name";
        $promoter_stock_summary = $pdo->query($sql_promoter_stock)->fetchAll();
        
        // تجميع النتائج حسب المروج
        $promoter_stock_grouped = [];
        foreach ($promoter_stock_summary as $row) {
            $promoter_stock_grouped[$row['promoter_name']][] = $row;
        }

        $view_file = 'views/posm/dashboard.php';
        break;
}

include 'views/layout.php';