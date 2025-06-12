<?php
// actions/item_target_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=item_targets");
    exit();
}

require_permission('manage_item_targets');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$item_target_id = ($action == 'edit') ? (int)($_POST['item_target_id'] ?? null) : null;
$representative_id = (int)($_POST['representative_id'] ?? null);
$product_id = (int)($_POST['product_id'] ?? null);
$year = (int)($_POST['year'] ?? null);
$month = (int)($_POST['month'] ?? null);
$target_quantity = filter_var($_POST['target_quantity'] ?? 0, FILTER_VALIDATE_FLOAT);

if (empty($representative_id) || empty($product_id) || empty($year) || empty($month) || $target_quantity === false || $target_quantity < 0) {
    $_SESSION['error_message'] = "الرجاء ملء جميع الحقول بشكل صحيح. الكمية المستهدفة يجب أن تكون رقمًا موجبًا أو صفرًا.";
    header("Location: ../index.php?page=item_targets&action={$action}" . ($item_target_id ? "&id={$item_target_id}" : ''));
    exit();
}

try {
    if ($_SESSION['user_role'] == 'supervisor') {
        $stmt_verify = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND supervisor_id = ?");
        $stmt_verify->execute([$representative_id, $_SESSION['user_id']]);
        if (!$stmt_verify->fetch()) {
             $_SESSION['error_message'] = "لا يمكنك إضافة هدف لهذا المندوب لأنه لا يتبع لك.";
             header("Location: ../index.php?page=item_targets&action={$action}");
             exit();
        }
    }
    
    $sql_check = "SELECT item_target_id FROM item_sales_targets WHERE representative_id = ? AND product_id = ? AND year = ? AND month = ?";
    $params_check = [$representative_id, $product_id, $year, $month];
    if ($action == 'edit' && $item_target_id) {
        $sql_check .= " AND item_target_id != ?";
        $params_check[] = $item_target_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "يوجد هدف مسجل بالفعل لهذا المندوب وهذا الصنف في نفس الشهر والسنة.";
        header("Location: ../index.php?page=item_targets&year={$year}&month={$month}");
        exit();
    }

    if ($action == 'add') {
        $sql = "INSERT INTO item_sales_targets (representative_id, product_id, year, month, target_quantity, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$representative_id, $product_id, $year, $month, $target_quantity, $_SESSION['user_id']]);
        $_SESSION['success_message'] = "تم إضافة الهدف بنجاح.";
    } elseif ($action == 'edit' && $item_target_id) {
        $sql = "UPDATE item_sales_targets SET target_quantity = ? WHERE item_target_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$target_quantity, $item_target_id]);
        $_SESSION['success_message'] = "تم تحديث الهدف بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Item target save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=item_targets&year={$year}&month={$month}");
exit();