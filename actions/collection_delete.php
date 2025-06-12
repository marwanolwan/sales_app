<?php
// actions/collection_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=collections");
    exit();
}

// 1. التحقق من الصلاحيات والـ CSRF
require_permission('manage_collections');
verify_csrf_token();

// 2. استلام البيانات من النموذج
$collection_id = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : null;

if (!$collection_id) {
    $_SESSION['error_message'] = "معرف سجل التحصيل غير صالح.";
    header("Location: ../index.php?page=collections");
    exit();
}

try {
    // 3. التحقق من صلاحيات المشرف (اختياري ولكنه جيد للأمان)
    // يتأكد من أن المشرف لا يمكنه حذف سجل لا يخص أحد مندوبيه
    if ($_SESSION['user_role'] == 'supervisor') {
       $stmt_check_owner = $pdo->prepare(
           "SELECT mc.collection_id 
            FROM monthly_collections mc 
            JOIN users u ON mc.representative_id = u.user_id 
            WHERE mc.collection_id = ? AND u.supervisor_id = ?"
       );
       $stmt_check_owner->execute([$collection_id, $_SESSION['user_id']]);
       if ($stmt_check_owner->fetch() === false) {
           $_SESSION['error_message'] = "ليس لديك صلاحية لحذف هذا السجل.";
           header("Location: ../index.php?page=collections");
           exit();
       }
    }

    // 4. تنفيذ استعلام الحذف
    $stmt_delete = $pdo->prepare("DELETE FROM monthly_collections WHERE collection_id = ?");
    $stmt_delete->execute([$collection_id]);
    
    // التحقق من أن صفًا واحدًا قد تم حذفه
    if ($stmt_delete->rowCount() > 0) {
        $_SESSION['success_message'] = "تم حذف سجل التحصيل بنجاح.";
    } else {
        $_SESSION['error_message'] = "لم يتم العثور على سجل التحصيل المطلوب لحذفه.";
    }

} catch (PDOException $e) {
    // تسجيل الخطأ الفعلي
    error_log("Collection delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حذف السجل.";
}

// 5. إعادة التوجيه إلى صفحة القائمة
// من الأفضل عدم تمرير الشهر والسنة، والسماح للصفحة بالتحميل بقيمها الافتراضية
header("Location: ../index.php?page=collections");
exit();