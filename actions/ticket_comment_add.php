<?php
// actions/ticket_comment_add.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // إعادة توجيه هادئة إذا تم الوصول للملف مباشرة
    header("Location: ../index.php?page=dashboard"); 
    exit();
}

require_login(); // يجب أن يكون المستخدم مسجلاً دخوله ليضيف تعليقًا
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$comment_text = trim($_POST['comment_text'] ?? '');
$current_user_id = (int)$_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];
// ملاحظة: هذا الملف لا يدعم رفع المرفقات مع التعليق حاليًا للتبسيط، ولكن يمكن إضافته بسهولة

// --- 2. التحقق من صحة البيانات والصلاحيات ---
if ($ticket_id <= 0) {
    $_SESSION['error_message'] = "معرف التذكرة غير صالح.";
    header("Location: ../index.php?page=tickets");
    exit();
}

// يجب أن يكون هناك نص للتعليق
if (empty($comment_text)) {
    $_SESSION['error_message'] = "لا يمكن إضافة تعليق فارغ.";
    header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}#comment-form");
    exit();
}

try {
    // التحقق من أن المستخدم لديه صلاحية للتعليق على هذه التذكرة
    $stmt_check = $pdo->prepare("SELECT created_by_user_id, assigned_to_user_id, assigned_to_department_id FROM tickets WHERE ticket_id = ?");
    $stmt_check->execute([$ticket_id]);
    $ticket_info = $stmt_check->fetch();

    if (!$ticket_info) {
        $_SESSION['error_message'] = "التذكرة التي تحاول التعليق عليها غير موجودة.";
        header("Location: ../index.php?page=tickets");
        exit();
    }
    
    $is_allowed = false;
    // السماح بالتعليق إذا كان المستخدم هو المنشئ، أو المكلف، أو مدير
    if ($ticket_info['created_by_user_id'] == $current_user_id || 
        $ticket_info['assigned_to_user_id'] == $current_user_id || 
        $current_user_role == 'admin') 
    {
        $is_allowed = true;
    }

    // (إضافة) السماح بالتعليق إذا كان المستخدم ينتمي للقسم المسندة إليه التذكرة
    if (!$is_allowed && $ticket_info['assigned_to_department_id']) {
        // هذا الجزء يتطلب وجود عمود department_id في جدول users
        // إذا لم يكن موجودًا، يمكنك تجاهل هذا التحقق أو تعديله
        // $stmt_dept = $pdo->prepare("SELECT department_id FROM users WHERE user_id = ?");
        // $stmt_dept->execute([$current_user_id]);
        // $user_department = $stmt_dept->fetchColumn();
        // if ($user_department == $ticket_info['assigned_to_department_id']) {
        //     $is_allowed = true;
        // }
    }

    if (!$is_allowed) {
        $_SESSION['error_message'] = "ليس لديك صلاحية لإضافة تعليق على هذه التذكرة.";
        header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}");
        exit();
    }

} catch (PDOException $e) {
    error_log("Comment permission check failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ أثناء التحقق من صلاحيات التعليق.";
    header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}");
    exit();
}

// --- 3. حفظ التعليق في قاعدة البيانات ---
try {
    $sql = "INSERT INTO ticket_comments (ticket_id, user_id, comment_text) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $ticket_id,
        $current_user_id,
        $comment_text
    ]);
    
    // تحديث حقل updated_at في التذكرة الرئيسية لإظهار أنها نشطة
    $stmt_touch = $pdo->prepare("UPDATE tickets SET updated_at = NOW() WHERE ticket_id = ?");
    $stmt_touch->execute([$ticket_id]);


    // (اختياري ومتقدم) إنشاء إشعارات
    // إرسال إشعار للمنشئ إذا كان المعلق هو المكلف
    if ($current_user_id == $ticket_info['assigned_to_user_id'] && $current_user_id != $ticket_info['created_by_user_id']) {
        // كود إنشاء إشعار للمنشئ ($ticket_info['created_by_user_id'])
    }
    // إرسال إشعار للمكلف إذا كان المعلق هو المنشئ
    elseif ($current_user_id == $ticket_info['created_by_user_id'] && $ticket_info['assigned_to_user_id'] && $current_user_id != $ticket_info['assigned_to_user_id']) {
        // كود إنشاء إشعار للمكلف ($ticket_info['assigned_to_user_id'])
    }
    
    $_SESSION['success_message'] = "تمت إضافة تعليقك بنجاح.";

} catch (PDOException $e) {
    error_log("Add comment failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء إضافة التعليق.";
}

// --- 4. إعادة التوجيه إلى صفحة المهمة ---
// إضافة مرساة (anchor) للرابط للانتقال مباشرة إلى أسفل قسم التعليقات
header("Location: ../index.php?page=tickets&action=view&id={$ticket_id}#comment-form");
exit();