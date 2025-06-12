<?php
// views/login_form.php
// هذا الملف يعرض النموذج فقط
$error_message = $_SESSION['error_message_login'] ?? '';
unset($_SESSION['error_message_login']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style> /* نفس ستايل تسجيل الدخول */ </style>
</head>
<body>
    <div class="login-container">
        <h2>تسجيل الدخول</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="actions/user_login.php" method="POST">
            <?php csrf_input(); ?>
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