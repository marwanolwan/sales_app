<?php
// actions/sales_target_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=sales_targets");
    exit();
}

require_permission('manage_sales_targets');
verify_csrf_token();

$target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : null;

if (!$target_id) {
    $_SESSION['error_message'] = "معرف الهدف غير صالح.";
    header("Location: ../index.php?page=sales_targets");
    exit();
}

try {
    if ($_SESSION['user_role'] == 'supervisor') {
        $stmt_verify = $pdo->prepare(
            "SELECT st.target_id FROM sales_targets st JOIN users u ON st.representative_id = u.user_id 
             WHERE st.target_id = ? AND u.supervisor_id = ?"
        );
        $stmt_verify->execute([$target_id, $_SESSION['user_id']]);
        if (!$stmt_verify->fetch()) {
             $_SESSION['error_message'] = "ليس لديك صلاحية لحذف هذا الهدف.";
             header("Location: ../index.php?page=sales_targets");
             exit();
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM sales_targets WHERE target_id = ?");
    $stmt->execute([$target_id]);
    $_SESSION['success_message'] = "تم حذف الهدف بنجاح.";
} catch (PDOException $e) {
    error_log("Sales target delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في حذف الهدف.";
}

// أعد التوجيه إلى الصفحة السابقة، أو الافتراضية إذا لم تكن هناك معلومات
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php?page=sales_targets';
header("Location: " . $redirect_url);
exit();