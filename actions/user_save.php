<?php
// actions/user_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=users");
    exit();
}

require_permission('manage_users');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$user_id = ($action == 'edit') ? (int)($_POST['user_id'] ?? null) : null;
$username = trim($_POST['username'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$role = $_POST['role'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$region_id = !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null;
$supervisor_id = !empty($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : null;
$is_active = isset($_POST['is_active']) ? 1 : 0;

$errors = [];
if (empty($username) || empty($full_name) || empty($role)) {
    $errors[] = "الرجاء ملء الحقول الإلزامية (اسم المستخدم، الاسم الكامل، الدور).";
}
if (($action == 'add' && empty($password)) || (!empty($password) && $password != $confirm_password)) {
    $errors[] = "كلمة المرور مطلوبة عند الإضافة ويجب أن تتطابق.";
}
if ($role == 'supervisor' && empty($region_id)) {
    $errors[] = "يجب تحديد منطقة للمشرف.";
}
if (in_array($role, ['representative', 'promoter']) && empty($supervisor_id)) {
    $errors[] = "يجب تحديد مشرف للمندوب/المروج.";
}
if ($user_id == 1 && ($role != 'admin' || !$is_active)) {
    $errors[] = "لا يمكن تغيير دور أو حالة مدير النظام الرئيسي.";
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: ../index.php?page=users&action={$action}" . ($user_id ? "&id={$user_id}" : ''));
    exit();
}

try {
    if ($action == 'add') {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "اسم المستخدم موجود بالفعل.";
            header("Location: ../index.php?page=users&action=add");
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password_hash, full_name, role, region_id, supervisor_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $username, $password_hash, $full_name, $role,
            ($role == 'supervisor') ? $region_id : null,
            (in_array($role, ['representative', 'promoter'])) ? $supervisor_id : null,
            $is_active
        ]);
        $_SESSION['success_message'] = "تم إضافة المستخدم بنجاح.";
    } elseif ($action == 'edit' && $user_id) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username AND user_id != :user_id");
        $stmt->execute(['username' => $username, 'user_id' => $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "اسم المستخدم موجود بالفعل لمستخدم آخر.";
            header("Location: ../index.php?page=users&action=edit&id={$user_id}");
            exit();
        }

        $sql = "UPDATE users SET username = ?, full_name = ?, role = ?, region_id = ?, supervisor_id = ?, is_active = ?";
        $params = [
            $username, $full_name, $role,
            ($role == 'supervisor') ? $region_id : null,
            (in_array($role, ['representative', 'promoter'])) ? $supervisor_id : null,
            $is_active
        ];

        if (!empty($password)) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($password, PASSWORD_BCRYPT);
        }
        $sql .= " WHERE user_id = ?";
        $params[] = $user_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم تحديث بيانات المستخدم بنجاح.";
    }

} catch (PDOException $e) {
    error_log("User save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=users");
exit();