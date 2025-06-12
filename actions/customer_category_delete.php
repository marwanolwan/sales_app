<?php
// actions/customer_category_delete.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=customer_categories");
    exit();
}

require_permission('manage_customer_categories');
verify_csrf_token();

$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;

if (!$category_id) {
    $_SESSION['error_message'] = "معرف التصنيف غير صالح.";
    header("Location: ../index.php?page=customer_categories");
    exit();
}

try {
    // التحقق من وجود عملاء مرتبطين بهذا التصنيف
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE category_id = ?");
    $stmt_check->execute([$category_id]);
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error_message'] = "لا يمكن حذف هذا التصنيف لأنه مرتبط بعملاء حاليين. يرجى تحديث بيانات العملاء أولاً.";
    } else {
        // إذا لم يكن هناك عملاء مرتبطون، قم بالحذف
        $stmt = $pdo->prepare("DELETE FROM customer_categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success_message'] = "تم حذف تصنيف العميل بنجاح.";
    }
} catch (PDOException $e) {
    error_log("Customer category delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف التصنيف.";
}

header("Location: ../index.php?page=customer_categories");
exit();