<?php
// core/db.php

$host = 'localhost';
$db_name = 'sales_app'; // تأكد من اسم قاعدة البيانات الصحيح
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // في بيئة الإنتاج، يجب تسجيل الخطأ وليس عرضه
    error_log("Database Connection Error: " . $e->getMessage());
    die("عذرًا، حدث خطأ فني في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقًا.");
}