<?php
// actions/user_login.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=login");
    exit();
}
// لا نحتاج للتحقق من الصلاحية هنا
verify_csrf_token();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error_message_login'] = "الرجاء إدخال اسم المستخدم وكلمة المرور.";
    header("Location: ../index.php?page=login");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        unset($_SESSION['permissions']);
        header("Location: ../index.php?page=dashboard");
        exit();
    } else {
        $_SESSION['error_message_login'] = "اسم المستخدم أو كلمة المرور غير صحيحة أو الحساب غير مفعل.";
        header("Location: ../index.php?page=login");
        exit();
    }
} catch (PDOException $e) {
    error_log("Login failed: " . $e->getMessage());
    $_SESSION['error_message_login'] = "حدث خطأ فني، يرجى المحاولة لاحقًا.";
    header("Location: ../index.php?page=login");
    exit();
}