<?php
// actions/promotion_type_save.php
require_once '../core/db.php';
require_once '../core/functions.php';

require_permission('manage_promotions');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$id = ($action == 'edit') ? (int)($_POST['promo_type_id'] ?? null) : null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_annual = isset($_POST['is_annual']) ? 1 : 0;

if (empty($name)) {
    $_SESSION['error_message'] = "اسم نوع الدعاية مطلوب.";
    header("Location: ../index.php?page=promotion_types&action={$action}" . ($id ? "&id={$id}" : ''));
    exit();
}

try {
    // التحقق من تكرار الاسم
    $sql_check = "SELECT promo_type_id FROM promotion_types WHERE name = ? AND promo_type_id != ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$name, $id ?? 0]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "اسم نوع الدعاية موجود بالفعل.";
        header("Location: ../index.php?page=promotion_types&action={$action}" . ($id ? "&id={$id}" : ''));
        exit();
    }

    if ($action == 'add') {
        $sql = "INSERT INTO promotion_types (name, description, is_annual) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $is_annual]);
        $_SESSION['success_message'] = "تم إضافة النوع بنجاح.";
    } elseif ($action == 'edit' && $id) {
        $sql = "UPDATE promotion_types SET name = ?, description = ?, is_annual = ? WHERE promo_type_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $is_annual, $id]);
        $_SESSION['success_message'] = "تم تحديث النوع بنجاح.";
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات.";
}

header("Location: ../index.php?page=promotion_types");
exit();