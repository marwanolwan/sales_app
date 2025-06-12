<?php
require_once '../core/db.php';
require_once '../core/functions.php';
// require_permission('manage_pricing');
verify_csrf_token();
$level_id = (int)($_POST['level_id'] ?? 0);
if ($level_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM price_levels WHERE level_id = ?");
        $stmt->execute([$level_id]);
        $_SESSION['success_message'] = "تم حذف مستوى السعر بنجاح.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "فشل حذف المستوى.";
    }
}
header("Location: ../index.php?page=pricing");
exit();