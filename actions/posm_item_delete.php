<?php
// actions/posm_item_delete.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=posm&action=items_list"); 
    exit();
}

// التحقق من الصلاحيات وتوكن الحماية
require_permission('manage_assets'); // يمكن إعادة استخدام صلاحية الأصول
verify_csrf_token();

// --- 1. استلام ID المادة من النموذج ---
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : null;

// --- 2. التحقق من صحة البيانات ---
if (!$item_id) {
    $_SESSION['error_message'] = "معرف المادة الترويجية غير صالح.";
    header("Location: ../index.php?page=posm&action=items_list");
    exit();
}

// --- 3. تنفيذ عملية الحذف مع التحقق من الرصيد مرة أخرى من جانب الخادم ---
try {
    $pdo->beginTransaction();

    // **الأهم: إعادة التحقق من الرصيد من جانب الخادم**
    $sql_stock = "SELECT SUM(CASE WHEN movement_type = 'Stock In' THEN quantity ELSE -quantity END) as current_stock
                  FROM posm_stock_movements WHERE item_id = ?";
    $stmt_stock = $pdo->prepare($sql_stock);
    $stmt_stock->execute([$item_id]);
    $current_stock = (int)$stmt_stock->fetchColumn();

    if ($current_stock != 0) {
        // إذا كان الرصيد لا يساوي صفرًا (ربما قام مستخدم آخر بعمل حركة في نفس اللحظة)
        $_SESSION['error_message'] = "لا يمكن حذف هذه المادة لأن رصيدها الحالي ليس صفرًا. قد تكون هناك حركات جديدة تمت عليها.";
        $pdo->rollBack(); // التراجع عن أي شيء (احتياطي)
        header("Location: ../index.php?page=posm&action=items_list");
        exit();
    }
    
    // إذا كان الرصيد صفرًا، قم بحذف سجلات الحركات أولاً
    $stmt_delete_movements = $pdo->prepare("DELETE FROM posm_stock_movements WHERE item_id = ?");
    $stmt_delete_movements->execute([$item_id]);
    
    // ثم قم بحذف المادة نفسها
    $stmt_delete_item = $pdo->prepare("DELETE FROM posm_items WHERE item_id = ?");
    $stmt_delete_item->execute([$item_id]);
    
    if ($stmt_delete_item->rowCount() > 0) {
        $_SESSION['success_message'] = "تم حذف المادة الترويجية وسجل حركاتها بنجاح.";
    } else {
        $_SESSION['warning_message'] = "لم يتم العثور على المادة المحددة لحذفها.";
    }

    $pdo->commit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("POSM item delete failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء عملية الحذف.";
}

// --- 4. إعادة التوجيه إلى قائمة المواد الترويجية ---
header("Location: ../index.php?page=posm&action=items_list");
exit();