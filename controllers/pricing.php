<?php
// controllers/pricing.php

// افترض أن لديك صلاحية جديدة مثل 'manage_pricing'
// require_permission('manage_pricing'); 

$action = $_GET['action'] ?? 'list';
$offer_id = isset($_GET['offer_id']) ? (int)$_GET['offer_id'] : null;

$page_title = "إدارة تسعير المنتجات والعروض";
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title = ($action == 'add') ? 'إضافة تسعير جديد' : 'تعديل التسعير';
        
        // جلب المنتجات لاستخدامها في القوائم المنسدلة
        $products = $pdo->query("SELECT product_id, name, product_code FROM products WHERE is_active = TRUE ORDER BY name ASC")->fetchAll();
        
        $offer_data = null;
        if ($action == 'edit' && $offer_id) {
            // جلب بيانات العرض الرئيسي
            $stmt = $pdo->prepare("SELECT * FROM price_offers WHERE offer_id = ? AND status = 'active'");
            $stmt->execute([$offer_id]);
            $offer_data = $stmt->fetch();
            if (!$offer_data) {
                $_SESSION['error_message'] = "التسعير المطلوب غير موجود أو مؤرشف.";
                header("Location: index.php?page=pricing");
                exit();
            }
            // جلب مستويات السعر التابعة له
            $stmt_levels = $pdo->prepare("SELECT * FROM price_levels WHERE offer_id = ? ORDER BY condition_quantity ASC");
            $stmt_levels->execute([$offer_id]);
            $offer_data['levels'] = $stmt_levels->fetchAll();

            // جلب أصناف البونص لكل مستوى
            foreach ($offer_data['levels'] as $key => $level) {
                $stmt_bonus = $pdo->prepare("SELECT * FROM bonus_items WHERE level_id = ?");
                $stmt_bonus->execute([$level['level_id']]);
                $offer_data['levels'][$key]['bonus_items'] = $stmt_bonus->fetchAll();
            }
        }
        $view_file = 'views/pricing/form.php';
        break;

    // *** بداية الجزء الذي تم إصلاحه ***
    case 'archived':
        $page_title = "أرشيف التسعيرات";
       
        // 1. جلب العروض المؤرشفة
        $stmt_offers = $pdo->prepare("
            SELECT po.*, p.name as product_name, p.product_code, u.full_name as creator_name
            FROM price_offers po
            JOIN products p ON po.product_id = p.product_id
            LEFT JOIN users u ON po.created_by_user_id = u.user_id
            WHERE po.status = 'archived'
            ORDER BY p.name, po.updated_at DESC
        ");
        $stmt_offers->execute();
        $all_offers = $stmt_offers->fetchAll(PDO::FETCH_ASSOC);

        // 2. إذا لم تكن هناك عروض مؤرشفة، لا داعي للمتابعة
        if (!empty($all_offers)) {
            // 3. جلب كل المستويات والبونص للعروض المؤرشفة
            $offer_ids = array_column($all_offers, 'offer_id');
            $placeholders = implode(',', array_fill(0, count($offer_ids), '?'));

            $sql_levels_bonuses = "
                SELECT 
                    pl.level_id, pl.offer_id, pl.condition_quantity, pl.price_per_unit, pl.pieces_per_unit, pl.bonus_same_item_quantity, pl.notes,
                    bi.bonus_id, bi.bonus_product_id, bi.bonus_quantity, bi.bonus_price, 
                    p_bonus.name as bonus_product_name
                FROM price_levels pl
                LEFT JOIN bonus_items bi ON pl.level_id = bi.level_id
                LEFT JOIN products p_bonus ON bi.bonus_product_id = p_bonus.product_id
                WHERE pl.offer_id IN ($placeholders)
                ORDER BY pl.condition_quantity ASC
            ";
            $stmt_details = $pdo->prepare($sql_levels_bonuses);
            $stmt_details->execute($offer_ids);
            $all_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

            // 4. تنظيم البيانات في هيكل متداخل
            $levels_by_offer_id = [];
            foreach($all_details as $detail) {
                $offer_id_from_detail = $detail['offer_id'];
                $level_id_from_detail = $detail['level_id'];

                if (!isset($levels_by_offer_id[$offer_id_from_detail][$level_id_from_detail])) {
                    $levels_by_offer_id[$offer_id_from_detail][$level_id_from_detail] = [
                        'level_id' => $level_id_from_detail,
                        'condition_quantity' => $detail['condition_quantity'],
                        'price_per_unit' => $detail['price_per_unit'],
                        'pieces_per_unit' => $detail['pieces_per_unit'],
                        'bonus_same_item_quantity' => $detail['bonus_same_item_quantity'],
                        'notes' => $detail['notes'],
                        'bonus_items' => []
                    ];
                }
                if ($detail['bonus_id']) {
                    $levels_by_offer_id[$offer_id_from_detail][$level_id_from_detail]['bonus_items'][] = $detail;
                }
            }
            
            // 5. دمج المستويات مع العروض الرئيسية وتجميعها حسب المنتج
            $offers_by_product = [];
            foreach ($all_offers as $offer) {
                $product_id = $offer['product_id'];
                if (!isset($offers_by_product[$product_id])) {
                    $offers_by_product[$product_id] = [
                        'details' => [
                            'product_name' => $offer['product_name'],
                            'product_code' => $offer['product_code']
                        ],
                        'offers' => []
                    ];
                }
                $offer['levels'] = $levels_by_offer_id[$offer['offer_id']] ?? [];
                $offers_by_product[$product_id]['offers'][] = $offer;
            }
        } else {
            $offers_by_product = [];
        }

        $view_file = 'views/pricing/archived_list.php';
        break; // <-- الـ break الخاصة بـ case 'archived'

    case 'list':
    default:
        // 1. جلب العروض النشطة الأساسية
        $stmt_offers = $pdo->query("
            SELECT po.*, p.name as product_name, p.product_code, u.full_name as creator_name
            FROM price_offers po
            JOIN products p ON po.product_id = p.product_id
            LEFT JOIN users u ON po.created_by_user_id = u.user_id
            WHERE po.status = 'active'
            ORDER BY p.name, po.created_at
        ");
        $all_offers = $stmt_offers->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($all_offers)) {
            // (نفس منطق جلب التفاصيل من الأعلى ولكن للعروض النشطة)
            $offer_ids = array_column($all_offers, 'offer_id');
            $placeholders = implode(',', array_fill(0, count($offer_ids), '?'));

            $sql_levels_bonuses = "
                SELECT 
                    pl.level_id, pl.offer_id, pl.condition_quantity, pl.price_per_unit, pl.pieces_per_unit, pl.bonus_same_item_quantity, pl.notes,
                    bi.bonus_id, bi.bonus_product_id, bi.bonus_quantity, bi.bonus_price, 
                    p_bonus.name as bonus_product_name, p_bonus.product_code as bonus_product_code
                FROM price_levels pl
                LEFT JOIN bonus_items bi ON pl.level_id = bi.level_id
                LEFT JOIN products p_bonus ON bi.bonus_product_id = p_bonus.product_id
                WHERE pl.offer_id IN ($placeholders)
                ORDER BY pl.condition_quantity ASC
            ";
            $stmt_details = $pdo->prepare($sql_levels_bonuses);
            $stmt_details->execute($offer_ids);
            $all_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

            $levels_by_offer_id = [];
            foreach($all_details as $detail) {
                $offer_id_from_detail = $detail['offer_id'];
                $level_id_from_detail = $detail['level_id'];
                if (!isset($levels_by_offer_id[$offer_id_from_detail][$level_id_from_detail])) {
                    $levels_by_offer_id[$offer_id_from_detail][$level_id_from_detail] = [
                        'level_id' => $level_id_from_detail,
                        'condition_quantity' => $detail['condition_quantity'],
                        'price_per_unit' => $detail['price_per_unit'],
                        'pieces_per_unit' => $detail['pieces_per_unit'],
                        'bonus_same_item_quantity' => $detail['bonus_same_item_quantity'],
                        'notes' => $detail['notes'],
                        'bonus_items' => []
                    ];
                }
                if ($detail['bonus_id']) {
                    $levels_by_offer_id[$offer_id_from_detail][$level_id_from_detail]['bonus_items'][] = $detail;
                }
            }
            
            $offers_by_product = [];
            foreach ($all_offers as $offer) {
                $product_id = $offer['product_id'];
                if (!isset($offers_by_product[$product_id])) {
                    $offers_by_product[$product_id] = [
                        'details' => [
                            'product_name' => $offer['product_name'],
                            'product_code' => $offer['product_code']
                        ],
                        'offers' => []
                    ];
                }
                $offer['levels'] = $levels_by_offer_id[$offer['offer_id']] ?? [];
                $offers_by_product[$product_id]['offers'][] = $offer;
            }
        } else {
            $offers_by_product = [];
        }

        $view_file = 'views/pricing/list.php';
        break;
}

include 'views/layout.php';