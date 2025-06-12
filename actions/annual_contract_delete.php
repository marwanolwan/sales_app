<?php
// actions/annual_contract_delete.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

require_permission('manage_promotions');
verify_csrf_token();

$customer_id = (int)($_POST['customer_id'] ?? 0);
$contract_id = (int)($_POST['contract_id'] ?? 0);
$redirect_url = "../index.php?page=annual_contracts&customer_id={$customer_id}";

if (!$customer_id || !$contract_id) {
    $_SESSION['error_message'] = "بيانات العقد غير صالحة.";
    header("Location: ../index.php?page=promotions");
    exit();
}

try {
    // جلب مسار الملف قبل الحذف
    $stmt_path = $pdo->prepare("SELECT contract_file_path FROM annual_contracts WHERE contract_id = ?");
    $stmt_path->execute([$contract_id]);
    $file_path = $stmt_path->fetchColumn();

    // حذف السجل من قاعدة البيانات
    $stmt_delete = $pdo->prepare("DELETE FROM annual_contracts WHERE contract_id = ? AND customer_id = ?");
    $stmt_delete->execute([$contract_id, $customer_id]);
    
    // إذا تم الحذف بنجاح، احذف الملف الفعلي
    if ($stmt_delete->rowCount() > 0 && $file_path) {
        $full_path = '../uploads/annual_contracts/' . $file_path;
        if (file_exists($full_path) && is_file($full_path)) {
            unlink($full_path);
        }
    }
    
    $_SESSION['success_message'] = "تم حذف العقد بنجاح.";

} catch (PDOException $e) {
    error_log("Contract delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حذف العقد.";
}

header("Location: {$redirect_url}");
exit();