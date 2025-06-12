<?php
// login.php (Controller & View combined for simplicity)

$error_message = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=dashboard");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "الرجاء إدخال اسم المستخدم وكلمة المرور.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND is_active = TRUE");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // إعادة إنشاء الجلسة للحماية من Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // مسح أي صلاحيات قديمة من الجلسة
            unset($_SESSION['permissions']); 

            header("Location: index.php?page=dashboard");
            exit();
        } else {
            $error_message = "اسم المستخدم أو كلمة المرور غير صحيحة أو الحساب غير مفعل.";
        }
    }
}

// عرض الواجهة
$page_title = "تسجيل الدخول";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة المبيعات</title>
    <link rel="stylesheet" href="css/style.css">
    <style> /* نفس الستايل من ملفك الأصلي */ </style>
</head>
<body>
    <div class="login-container">
        <h2>تسجيل الدخول</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="index.php?page=login" method="POST">
            <div>
                <input type="text" name="username" placeholder="اسم المستخدم" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="كلمة المرور" required>
            </div>
            <div>
                <button type="submit">دخول</button>
            </div>
        </form>
    </div>
</body>
</html>