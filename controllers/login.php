<?php
// controllers/login.php

// هذا الملف يعالج الطلبات ويجهز البيانات للعرض
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=dashboard");
    exit();
}

$page_title = "تسجيل الدخول";
$view_file = 'views/login_form.php';

// لا يوجد قالب layout لصفحة تسجيل الدخول، لذلك نعرضها مباشرة
include 'views/login_form.php';