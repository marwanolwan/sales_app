<?php
// actions/pricing_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// require_permission('manage_pricing');
verify_csrf_token();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=pricing");
    exit();
}

$pdo->beginTransaction();

try {
    $offer_id = isset($_POST['offer_id']) && !empty($_POST['offer_id']) ? (int)$_POST['offer_id'] : null;
    $product_id = (int)$_POST['product_id'];
    $levels_data = $_POST['levels'] ?? [];

    if (empty($product_id) || empty($levels_data)) {
        throw new Exception("بيانات غير كافية. يرجى ملء الحقول المطلوبة.");
    }

    // الخطوة 1: حفظ أو تحديث العرض الرئيسي
    if ($offer_id) { // تحديث
        $stmt_offer = $pdo->prepare("UPDATE price_offers SET product_id = ?, updated_at = NOW() WHERE offer_id = ?");
        $stmt_offer->execute([$product_id, $offer_id]);
    } else { // إضافة
        $stmt_offer = $pdo->prepare("INSERT INTO price_offers (product_id, created_by_user_id) VALUES (?, ?)");
        $stmt_offer->execute([$product_id, $_SESSION['user_id']]);
        $offer_id = $pdo->lastInsertId();
    }

    // تتبع المستويات الموجودة لتحديد ما يجب حذفه
    $existing_level_ids = [];
    if ($offer_id) {
         $stmt_existing = $pdo->prepare("SELECT level_id FROM price_levels WHERE offer_id = ?");
         $stmt_existing->execute([$offer_id]);
         $existing_level_ids = $stmt_existing->fetchAll(PDO::FETCH_COLUMN);
    }
    $submitted_level_ids = [];


    // الخطوة 2: المرور على المستويات وحفظها
    foreach ($levels_data as $level_item) {
        $level_id = isset($level_item['level_id']) && !empty($level_item['level_id']) ? (int)$level_item['level_id'] : null;
        $submitted_level_ids[] = $level_id;

        $params = [
            $offer_id,
            $level_item['condition_quantity'],
            $level_item['price_per_unit'],
            $level_item['pieces_per_unit'] ?? 1,
            $level_item['bonus_same_item_quantity'] ?? 0,
            $level_item['notes'] ?? null
        ];

        if ($level_id) { // تحديث مستوى حالي
            $sql_level = "UPDATE price_levels SET offer_id = ?, condition_quantity = ?, price_per_unit = ?, pieces_per_unit = ?, bonus_same_item_quantity = ?, notes = ? WHERE level_id = ?";
            $params[] = $level_id;
        } else { // إضافة مستوى جديد
            $sql_level = "INSERT INTO price_levels (offer_id, condition_quantity, price_per_unit, pieces_per_unit, bonus_same_item_quantity, notes) VALUES (?, ?, ?, ?, ?, ?)";
        }
        $stmt_level = $pdo->prepare($sql_level);
        $stmt_level->execute($params);

        if (!$level_id) {
            $level_id = $pdo->lastInsertId();
        }

        // الخطوة 3: معالجة أصناف البونص الإضافية لهذا المستوى
        // أولاً: حذف كل البونص القديم لهذا المستوى
        $stmt_delete_bonus = $pdo->prepare("DELETE FROM bonus_items WHERE level_id = ?");
        $stmt_delete_bonus->execute([$level_id]);

        // ثانيًا: إضافة البونص الجديد إذا كان موجودًا
        if (!empty($level_item['bonus_items'])) {
            $stmt_bonus = $pdo->prepare("INSERT INTO bonus_items (level_id, bonus_product_id, bonus_quantity, bonus_price) VALUES (?, ?, ?, ?)");
            foreach ($level_item['bonus_items'] as $bonus_item) {
                if (!empty($bonus_item['bonus_product_id']) && !empty($bonus_item['bonus_quantity'])) {
                    $stmt_bonus->execute([
                        $level_id,
                        (int)$bonus_item['bonus_product_id'],
                        $bonus_item['bonus_quantity'],
                        !empty($bonus_item['bonus_price']) ? $bonus_item['bonus_price'] : null
                    ]);
                }
            }
        }
    }
    
    // الخطوة 4: حذف المستويات التي أزالها المستخدم من الواجهة
    $levels_to_delete = array_diff($existing_level_ids, $submitted_level_ids);
    if (!empty($levels_to_delete)) {
        $placeholders = implode(',', array_fill(0, count($levels_to_delete), '?'));
        $stmt_delete_levels = $pdo->prepare("DELETE FROM price_levels WHERE level_id IN ($placeholders)");
        $stmt_delete_levels->execute($levels_to_delete);
    }


    $pdo->commit();
    $_SESSION['success_message'] = "تم حفظ التسعير بنجاح.";

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Pricing save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "فشل حفظ التسعير: " . $e->getMessage();
}

header("Location: ../index.php?page=pricing");
exit();