<?php
// actions/customer_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=customers");
    exit();
}

require_permission('manage_customers');
verify_csrf_token();

// 1. استلام البيانات
$action = $_POST['action'] ?? 'add';
$customer_id = ($action == 'edit') ? (int)($_POST['customer_id'] ?? null) : null;

$is_main_account = isset($_POST['is_main_account']) ? 1 : 0;
$name = trim($_POST['name'] ?? '');
$customer_code = trim($_POST['customer_code'] ?? '');
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$address = trim($_POST['address'] ?? '');
$representative_id = !empty($_POST['representative_id']) ? (int)$_POST['representative_id'] : null;
$promoter_id = !empty($_POST['promoter_id']) ? (int)$_POST['promoter_id'] : null;
$latitude = !empty($_POST['latitude']) ? filter_var($_POST['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
$longitude = !empty($_POST['longitude']) ? filter_var($_POST['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
$status = $_POST['status'] ?? 'active';
// **تصحيح استباقي 1: تأكد من أن التاريخ هو NULL إذا كان فارغًا**
$opening_date = !empty($_POST['opening_date']) ? $_POST['opening_date'] : null;
$main_account_id = !$is_main_account && !empty($_POST['main_account_id']) ? (int)$_POST['main_account_id'] : null;

// 2. التحقق من الصحة (يبقى كما هو)
$errors = [];
// ... (Your validation logic here) ...

if (!empty($errors)) {
    // ... (Your error redirection logic here) ...
}

// 3. منطق قاعدة البيانات
try {
    if ($action == 'add' && $is_main_account) {
        $customer_code = 'MC-' . strtoupper(uniqid());
    }

    // بناء مصفوفة البيانات
    $data = [
        'customer_code' => $customer_code,
        'name' => $name,
        'category_id' => $category_id,
        'address' => $address,
        'representative_id' => $representative_id,
        'promoter_id' => $promoter_id,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'status' => $status,
        'opening_date' => $opening_date,
        'is_main_account' => $is_main_account,
        'main_account_id' => $main_account_id,
    ];

    if ($action == 'add') {
        // التحقق من تكرار الرمز قبل الإضافة
        $stmt_check = $pdo->prepare("SELECT customer_id FROM customers WHERE customer_code = :code");
        $stmt_check->execute(['code' => $data['customer_code']]);
        if ($stmt_check->fetch()) {
            throw new Exception("رمز العميل '{$data['customer_code']}' موجود بالفعل.");
        }
        
        $sql = "INSERT INTO customers (customer_code, name, category_id, address, representative_id, promoter_id, latitude, longitude, status, opening_date, is_main_account, main_account_id)
                VALUES (:customer_code, :name, :category_id, :address, :representative_id, :promoter_id, :latitude, :longitude, :status, :opening_date, :is_main_account, :main_account_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $_SESSION['success_message'] = "تم إضافة العميل بنجاح.";
    
    } elseif ($action == 'edit' && $customer_id) {
        $data['customer_id'] = $customer_id;

        // التحقق من تكرار الرمز عند التعديل (فقط إذا كان الرمز قد تغير)
        $stmt_check = $pdo->prepare("SELECT customer_id FROM customers WHERE customer_code = :code AND customer_id != :id");
        $stmt_check->execute(['code' => $data['customer_code'], 'id' => $customer_id]);
        if ($stmt_check->fetch()) {
            throw new Exception("رمز العميل '{$data['customer_code']}' مستخدم من قبل عميل آخر.");
        }

        $sql = "UPDATE customers SET 
                    customer_code = :customer_code, name = :name, category_id = :category_id, address = :address, 
                    representative_id = :representative_id, promoter_id = :promoter_id, latitude = :latitude, 
                    longitude = :longitude, status = :status, opening_date = :opening_date, 
                    is_main_account = :is_main_account, main_account_id = :main_account_id
                WHERE customer_id = :customer_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $_SESSION['success_message'] = "تم تحديث بيانات العميل بنجاح.";
    }
} catch (PDOException $e) {
    // **التعديل المؤقت لكشف الخطأ**
    $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
    // إذا كنت تريد رؤية البيانات التي تسببت في الخطأ، أزل التعليق عن الأسطر التالية
    // session_write_close(); // أغلق الجلسة قبل طباعة أي شيء
    // echo "<pre>An error occurred. Data sent to database:";
    // print_r($data ?? []);
    // echo "\nSQL Query: " . ($sql ?? 'Not available');
    // echo "\nPDO Error Info: ";
    // print_r($e->errorInfo);
    // echo "</pre>";
    // die();
} catch (Exception $e) {
    // للتعامل مع الأخطاء التي نلقيها يدويًا (مثل تكرار الرمز)
    $_SESSION['error_message'] = $e->getMessage();
}

header("Location: ../index.php?page=customers");
exit();