<?php
// actions/region_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=regions");
    exit();
}

require_permission('manage_regions');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$region_id = ($action == 'edit') ? (int)($_POST['region_id'] ?? null) : null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name)) {
    $_SESSION['error_message'] = "اسم المنطقة مطلوب.";
    header("Location: ../index.php?page=regions&action={$action}" . ($region_id ? "&id={$region_id}" : ''));
    exit();
}

try {
    $sql_check = "SELECT region_id FROM regions WHERE name = ?";
    $params_check = [$name];
    if ($action == 'edit' && $region_id) {
        $sql_check .= " AND region_id != ?";
        $params_check[] = $region_id;
    }
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "اسم المنطقة موجود بالفعل.";
        header("Location: ../index.php?page=regions&action={$action}" . ($region_id ? "&id={$region_id}" : ''));
        exit();
    }

    if ($action == 'add') {
        $sql = "INSERT INTO regions (name, description) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description]);
        $_SESSION['success_message'] = "تم إضافة المنطقة بنجاح.";
    } elseif ($action == 'edit' && $region_id) {
        $sql = "UPDATE regions SET name = ?, description = ? WHERE region_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $region_id]);
        $_SESSION['success_message'] = "تم تحديث المنطقة بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Region save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=regions");
exit();