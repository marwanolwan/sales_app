<?php
// actions/sales_target_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=sales_targets");
    exit();
}

require_permission('manage_sales_targets');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$target_id = ($action == 'edit') ? (int)($_POST['target_id'] ?? null) : null;
$representative_id = (int)($_POST['representative_id'] ?? null);
$year = (int)($_POST['year'] ?? null);
$month = (int)($_POST['month'] ?? null);
$target_amount = filter_var($_POST['target_amount'] ?? 0, FILTER_VALIDATE_FLOAT);

if (!$representative_id || !$year || !$month || $target_amount === false || $target_amount < 0) {
    $_SESSION['error_message'] = "الرجاء ملء جميع الحقول بشكل صحيح. الهدف يجب أن يكون رقمًا موجبًا.";
    header("Location: ../index.php?page=sales_targets&action={$action}" . ($target_id ? "&id={$target_id}" : "&year={$year}&month={$month}"));
    exit();
}

try {
    // التحقق من تكرار الهدف
    $sql_check = "SELECT target_id FROM sales_targets WHERE representative_id = ? AND year = ? AND month = ? AND (? IS NULL OR target_id != ?)";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$representative_id, $year, $month, $target_id, $target_id]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "يوجد هدف مسجل بالفعل لهذا المندوب في هذه الفترة.";
        header("Location: ../index.php?page=sales_targets&action={$action}" . ($target_id ? "&id={$target_id}" : "&year={$year}&month={$month}"));
        exit();
    }
    
    // التحقق من صلاحية المشرف
    if ($_SESSION['user_role'] == 'supervisor') {
        $stmt_verify = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND supervisor_id = ?");
        $stmt_verify->execute([$representative_id, $_SESSION['user_id']]);
        if (!$stmt_verify->fetch()) {
             $_SESSION['error_message'] = "لا يمكنك إدارة أهداف هذا المندوب.";
             header("Location: ../index.php?page=sales_targets&year={$year}&month={$month}");
             exit();
        }
    }

    if ($action == 'add') {
        $sql = "INSERT INTO sales_targets (representative_id, year, month, target_amount, created_by_user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$representative_id, $year, $month, $target_amount, $_SESSION['user_id']]);
        $_SESSION['success_message'] = "تم إضافة الهدف بنجاح.";
    } elseif ($action == 'edit' && $target_id) {
        $sql = "UPDATE sales_targets SET representative_id = ?, year = ?, month = ?, target_amount = ? WHERE target_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$representative_id, $year, $month, $target_amount, $target_id]);
        $_SESSION['success_message'] = "تم تحديث الهدف بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Sales target save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=sales_targets&year={$year}&month={$month}");
exit();