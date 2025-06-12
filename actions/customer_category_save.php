<?php
// actions/customer_category_save.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=customer_categories");
    exit();
}

require_permission('manage_customer_categories');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$category_id = ($action == 'edit') ? (int)($_POST['category_id'] ?? null) : null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name)) {
    $_SESSION['error_message'] = "اسم التصنيف مطلوب.";
    header("Location: ../index.php?page=customer_categories&action={$action}" . ($category_id ? "&id={$category_id}" : ''));
    exit();
}

try {
    // التحقق من عدم تكرار الاسم
    $sql_check = "SELECT category_id FROM customer_categories WHERE name = ? AND (? IS NULL OR category_id != ?)";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$name, $category_id, $category_id]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "اسم التصنيف موجود بالفعل.";
        header("Location: ../index.php?page=customer_categories&action={$action}" . ($category_id ? "&id={$category_id}" : ''));
        exit();
    }

    if ($action == 'add') {
        $sql = "INSERT INTO customer_categories (name, description) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description]);
        $_SESSION['success_message'] = "تم إضافة تصنيف العميل بنجاح.";
    } elseif ($action == 'edit' && $category_id) {
        $sql = "UPDATE customer_categories SET name = ?, description = ? WHERE category_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $category_id]);
        $_SESSION['success_message'] = "تم تحديث بيانات تصنيف العميل بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Customer category save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ البيانات.";
}

header("Location: ../index.php?page=customer_categories");
exit();