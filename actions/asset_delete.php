<?php
// actions/asset_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=assets"); 
    exit();
}

// التحقق من الصلاحيات وتوكن الحماية
require_permission('manage_assets');
verify_csrf_token();

// --- 1. استلام ID الأصل من النموذج ---
$asset_id = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;

// --- 2. التحقق من صحة البيانات ---
if (!$asset_id) {
    $_SESSION['error_message'] = "معرف الأصل غير صالح.";
    header("Location: ../index.php?page=assets");
    exit();
}

// --- 3. تنفيذ عملية الحذف ---
try {
    // ملاحظة: إذا كانت هناك جداول أخرى مرتبطة بالأصول (مثل سجلات الصيانة)،
    // يجب التحقق منها هنا قبل الحذف أو استخدام ON DELETE CASCADE في قاعدة البيانات.
    
    // في تصميمنا الحالي، لا توجد ارتباطات تمنع الحذف.
    
    $stmt_delete = $pdo->prepare("DELETE FROM assets WHERE asset_id = ?");
    $stmt_delete->execute([$asset_id]);
    
    if ($stmt_delete->rowCount() > 0) {
        $_SESSION['success_message'] = "تم حذف الأصل بنجاح.";
    } else {
        // هذه الحالة تحدث إذا حاول المستخدم حذف أصل تم حذفه بالفعل
        $_SESSION['warning_message'] = "لم يتم العثور على الأصل المحدد لحذفه.";
    }

} catch (PDOException $e) {
    error_log("Asset delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء عملية الحذف. قد يكون الأصل مرتبطًا ببيانات أخرى.";
}

// --- 4. إعادة التوجيه إلى قائمة الأصول ---
header("Location: ../index.php?page=assets");
exit();