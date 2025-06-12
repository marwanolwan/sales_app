<?php
// actions/posm_stock_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=posm&action=stock_entry"); 
    exit();
}

require_permission('manage_assets'); // يمكن إعادة استخدام الصلاحية
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$movement_type = $_POST['movement_type'] ?? '';
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
$notes = trim($_POST['notes'] ?? '');
$current_user_id = (int)$_SESSION['user_id'];

// --- 2. التحقق من صحة البيانات الأساسية ---
$errors = [];
if (empty($movement_type) || !in_array($movement_type, ['Stock In', 'Dispatch to Rep', 'Deliver to Customer'])) {
    $errors[] = "نوع الحركة غير صالح.";
}
if ($item_id <= 0) {
    $errors[] = "يجب اختيار المادة الترويجية.";
}
if ($quantity <= 0) {
    $errors[] = "يجب إدخال كمية صحيحة (أكبر من صفر).";
}

// --- 3. التحقق من الصحة بناءً على نوع الحركة ---
// 3.1 التحقق من توفر الرصيد قبل الصرف أو التسليم
if ($movement_type === 'Dispatch to Rep' || $movement_type === 'Deliver to Customer') {
    try {
        if ($movement_type === 'Dispatch to Rep') {
            // التحقق من رصيد المخزن الرئيسي
            $sql_stock = "SELECT SUM(CASE WHEN movement_type = 'Stock In' THEN quantity ELSE -quantity END) as current_stock
                          FROM posm_stock_movements WHERE item_id = ?";
            $stmt_stock = $pdo->prepare($sql_stock);
            $stmt_stock->execute([$item_id]);
            $current_stock = (int)$stmt_stock->fetchColumn();

            if ($quantity > $current_stock) {
                $errors[] = "الكمية المطلوبة ({$quantity}) أكبر من الرصيد المتاح في المخزن الرئيسي ({$current_stock}).";
            }
        } elseif ($movement_type === 'Deliver to Customer') {
            // التحقق من رصيد المندوب
             if (empty($user_id)) {
                $errors[] = "يجب تحديد المروج عند التسليم لعميل.";
            } else {
                $sql_promoter_stock = "SELECT SUM(CASE WHEN movement_type = 'Dispatch to Rep' THEN quantity ELSE -quantity END) as promoter_balance
                                  FROM posm_stock_movements WHERE item_id = ? AND user_id = ? AND movement_type IN ('Dispatch to Rep', 'Deliver to Customer')";
                $stmt_promoter_stock = $pdo->prepare($sql_promoter_stock);
                $stmt_promoter_stock->execute([$item_id, $user_id]);
                $promoter_balance = (int)$stmt_promoter_stock->fetchColumn();

                if ($quantity > $promoter_balance) {
                    $errors[] = "الكمية المطلوبة للتسليم ({$quantity}) أكبر من الرصيد المتاح في عهدة المروج ({$promoter_balance}).";
                }
            }
        }
    } catch (PDOException $e) {
        $errors[] = "خطأ في التحقق من الرصيد.";
        error_log("POSM Stock Check Failed: " . $e->getMessage());
    }
}

// 3.2 التحقق من الحقول الإلزامية لكل نوع
if ($movement_type === 'Dispatch to Rep' && empty($user_id)) {
    $errors[] = "يجب تحديد المندوب عند صرف كمية من المخزن.";
}
if ($movement_type === 'Deliver to Customer' && (empty($user_id) || empty($customer_id))) {
    $errors[] = "يجب تحديد المندوب والعميل عند تسليم كمية لعميل.";
}


if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: ../index.php?page=posm&action=stock_entry");
    exit();
}

// --- 4. تنفيذ عملية الحفظ في قاعدة البيانات ---
try {
    $sql = "INSERT INTO posm_stock_movements 
                (item_id, movement_type, quantity, user_id, customer_id, notes) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // في حركة "إدخال للمخزن"، المسجل هو المستخدم الحالي، وليس هناك عميل
    $final_user_id = ($movement_type === 'Stock In') ? $current_user_id : $user_id;
    
    $stmt->execute([
        $item_id,
        $movement_type,
        $quantity,
        $final_user_id,
        $customer_id, // سيكون NULL تلقائيًا إذا لم يتم إرساله
        $notes
    ]);
    
    $_SESSION['success_message'] = "تم تسجيل حركة المخزون بنجاح.";

} catch (PDOException $e) {
    error_log("POSM Stock Save Failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ الحركة.";
}

// --- 5. إعادة التوجيه إلى لوحة التحكم ---
header("Location: ../index.php?page=posm&action=dashboard");
exit();