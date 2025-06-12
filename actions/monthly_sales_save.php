<?php
// actions/monthly_sales_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=monthly_sales");
    exit();
}

require_permission('manage_monthly_sales');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$sale_id = ($action == 'edit') ? (int)($_POST['sale_id'] ?? null) : null;
$representative_id = (int)($_POST['representative_id'] ?? null);
$year = (int)($_POST['year'] ?? null);
$month = (int)($_POST['month'] ?? null);
$net_sales_amount = filter_var($_POST['net_sales_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$notes = trim($_POST['notes'] ?? '');

if (empty($representative_id) || empty($year) || empty($month) || $net_sales_amount === false || $net_sales_amount < 0) {
    $_SESSION['error_message'] = "الرجاء ملء جميع الحقول بشكل صحيح.";
    header("Location: ../index.php?page=monthly_sales&action={$action}" . ($sale_id ? "&id={$sale_id}" : ''));
    exit();
}

try {
    // التحقق من أن المشرف لا يضيف لمندوب خارج فريقه
    if ($_SESSION['user_role'] == 'supervisor') {
        $stmt_verify = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND supervisor_id = ?");
        $stmt_verify->execute([$representative_id, $_SESSION['user_id']]);
        if (!$stmt_verify->fetch()) {
             $_SESSION['error_message'] = "لا يمكنك إضافة سجل لهذا المندوب.";
             header("Location: ../index.php?page=monthly_sales&action={$action}");
             exit();
        }
    }
    
    // التحقق من التكرار
    $sql_check = "SELECT sale_id FROM monthly_sales WHERE representative_id = ? AND year = ? AND month = ?";
    $params_check = [$representative_id, $year, $month];
    if ($action == 'edit' && $sale_id) {
        $sql_check .= " AND sale_id != ?";
        $params_check[] = $sale_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "توجد مبيعات مسجلة بالفعل لهذا المندوب في هذا الشهر. يمكنك تعديل السجل الحالي.";
        header("Location: ../index.php?page=monthly_sales&year={$year}&month={$month}");
        exit();
    }

    if ($action == 'add') {
        $sql = "INSERT INTO monthly_sales (representative_id, year, month, net_sales_amount, notes, recorded_by_user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$representative_id, $year, $month, $net_sales_amount, $notes, $_SESSION['user_id']]);
        $_SESSION['success_message'] = "تم تسجيل المبيعات بنجاح.";
    } elseif ($action == 'edit' && $sale_id) {
        $sql = "UPDATE monthly_sales SET net_sales_amount = ?, notes = ? WHERE sale_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$net_sales_amount, $notes, $sale_id]);
        $_SESSION['success_message'] = "تم تحديث سجل المبيعات بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Monthly sale save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=monthly_sales&year={$year}&month={$month}");
exit();