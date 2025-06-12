<?php
// core/functions.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ======== دوال الأمان والمصادقة ========

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit();
    }
}

function check_permission($feature_name) {
    global $pdo;
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    $role = $_SESSION['user_role'];
    
    // ذاكرة تخزين مؤقتة للصلاحيات داخل الجلسة
    if (!isset($_SESSION['permissions'])) {
        try {
            $stmt = $pdo->prepare("SELECT feature, can_access FROM role_permissions WHERE role = :role");
            $stmt->execute(['role' => $role]);
            $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            error_log("Permission check failed: " . $e->getMessage());
            return false;
        }
    }
    
    return isset($_SESSION['permissions'][$feature_name]) && $_SESSION['permissions'][$feature_name] == 1;
}

function require_permission($feature_name) {
    require_login();
    if (!check_permission($feature_name)) {
        $_SESSION['error_message'] = "ليس لديك الصلاحية للوصول إلى هذه الصفحة.";
        header("Location: index.php?page=dashboard");
        exit();
    }
}

// ======== دوال حماية CSRF ========

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

function verify_csrf_token($token = null) {
    $token = $token ?? $_POST['csrf_token'] ?? '';
    
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        unset($_SESSION['csrf_token']);
        die('خطأ في التحقق من صحة الطلب (CSRF). الرجاء تحديث الصفحة والمحاولة مرة أخرى.');
    }
    unset($_SESSION['csrf_token']);
}