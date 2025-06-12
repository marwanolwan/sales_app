<?php
// actions/asset_type_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=assets&action=types"); 
    exit();
}

// التحقق من الصلاحيات وتوكن الحماية
require_permission('manage_assets'); // نفترض أن نفس الصلاحية تدير الأنواع
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$type_id = isset($_POST['type_id']) ? (int)$_POST['type_id'] : null;

// --- 2. التحقق من صحة البيانات ---
if (!$type_id) {
    $_SESSION['error_message'] = "معرف نوع الأصل غير صالح.";
    header("Location: ../index.php?page=assets&action=types");
    exit();
}

// --- 3. تنفيذ عملية الحذف مع التحقق ---
try {
    // **الأهم: التحقق مما إذا كان هذا النوع مستخدمًا أم لا**
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE type_id = ?");
    $stmt_check->execute([$type_id]);
    $asset_count = $stmt_check->fetchColumn();

    if ($asset_count > 0) {
        // إذا كان النوع مستخدمًا، لا تقم بالحذف وأرسل رسالة خطأ واضحة
        $_SESSION['error_message'] = "لا يمكن حذف هذا النوع لأنه مستخدم حاليًا من قبل {$asset_count} أصل. يرجى تغيير نوع هذه الأصول أولاً.";
    } else {
        // إذا لم يكن النوع مستخدمًا، قم بعملية الحذف
        $stmt_delete = $pdo->prepare("DELETE FROM asset_types WHERE type_id = ?");
        $stmt_delete->execute([$type_id]);
        
        if ($stmt_delete->rowCount() > 0) {
            $_SESSION['success_message'] = "تم حذف نوع الأصل بنجاح.";
        } else {
            $_SESSION['warning_message'] = "لم يتم العثور على نوع الأصل المحدد لحذفه (ربما تم حذفه بالفعل).";
        }
    }

} catch (PDOException $e) {
    error_log("Asset type delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء عملية الحذف.";
}

// --- 4. إعادة التوجيه إلى قائمة أنواع الأصول ---
header("Location: ../index.php?page=assets&action=types");
exit();