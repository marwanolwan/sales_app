<?php
// actions/item_sales_delete_monthly.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=item_sales"); 
    exit();
}

// التحقق من الصلاحيات وتوكن CSRF
require_permission('manage_item_sales');
verify_csrf_token();

// استلام البيانات من النموذج
$delete_year = isset($_POST['delete_year']) ? (int)$_POST['delete_year'] : null;
$delete_month = isset($_POST['delete_month']) ? (int)$_POST['delete_month'] : null;

// التحقق من صحة البيانات
if (!$delete_year || !$delete_month) {
    $_SESSION['error_message'] = "الرجاء اختيار سنة وشهر صالحين للحذف.";
    // **التصحيح هنا أيضًا: إعادة التوجيه إلى الصفحة الصحيحة**
    header("Location: ../index.php?page=item_sales");
    exit();
}

try {
    $sql_delete = "DELETE FROM monthly_item_sales WHERE year = :year AND month = :month";
    $params_delete = ['year' => $delete_year, 'month' => $delete_month];
    
    // إذا كان المستخدم مشرفًا، فإنه يحذف فقط مبيعات فريقه
    if ($_SESSION['user_role'] == 'supervisor') {
        $sql_delete .= " AND representative_id IN (SELECT user_id FROM users WHERE supervisor_id = :supervisor_id)";
        $params_delete['supervisor_id'] = $_SESSION['user_id'];
    }
    
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute($params_delete);
    $deleted_rows = $stmt_delete->rowCount();
    $_SESSION['success_message'] = "تم حذف {$deleted_rows} سجل مبيعات أصناف بنجاح.";

} catch (PDOException $e) {
    error_log("Monthly item sales delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في قاعدة البيانات أثناء حذف المبيعات.";
}

// **التصحيح الرئيسي هنا: تم تغيير 'item_sales_import' إلى 'item_sales'**
header("Location: ../index.php?page=item_sales");
exit();