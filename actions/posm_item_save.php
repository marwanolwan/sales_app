<?php
// actions/posm_item_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

// التأكد من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=posm&action=items_list"); 
    exit();
}

// التحقق من الصلاحيات وتوكن الحماية
require_permission('manage_assets'); // نفترض استخدام نفس صلاحية الأصول
verify_csrf_token();

// --- 1. استلام البيانات من النموذج ---
$action = $_POST['action'] ?? 'add_item';
$item_id = ($action == 'edit_item') ? (int)($_POST['item_id'] ?? null) : null;
$item_name = trim($_POST['item_name'] ?? '');
$item_code = trim($_POST['item_code'] ?? '');
$description = trim($_POST['description'] ?? '');

// --- 2. التحقق من صحة البيانات ---
if (empty($item_name)) {
    $_SESSION['error_message'] = "اسم المادة الترويجية مطلوب.";
    // إعادة التوجيه إلى نفس الصفحة مع الحفاظ على السياق
    header("Location: ../index.php?page=posm&action={$action}" . ($item_id ? "&id={$item_id}" : ''));
    exit();
}

// --- 3. تنفيذ عملية الحفظ في قاعدة البيانات ---
try {
    // التحقق من عدم تكرار اسم المادة
    $sql_check_name = "SELECT item_id FROM posm_items WHERE item_name = ?";
    $params_check_name = [$item_name];
    if ($action == 'edit_item' && $item_id) {
        $sql_check_name .= " AND item_id != ?";
        $params_check_name[] = $item_id;
    }
    $stmt_check_name = $pdo->prepare($sql_check_name);
    $stmt_check_name->execute($params_check_name);

    if ($stmt_check_name->fetch()) {
        $_SESSION['error_message'] = "اسم المادة الترويجية موجود بالفعل.";
        header("Location: ../index.php?page=posm&action={$action}" . ($item_id ? "&id={$item_id}" : ''));
        exit();
    }
    
    // التحقق من عدم تكرار كود المادة (إذا تم إدخاله)
    if (!empty($item_code)) {
        $sql_check_code = "SELECT item_id FROM posm_items WHERE item_code = ?";
        $params_check_code = [$item_code];
        if ($action == 'edit_item' && $item_id) {
            $sql_check_code .= " AND item_id != ?";
            $params_check_code[] = $item_id;
        }
        $stmt_check_code = $pdo->prepare($sql_check_code);
        $stmt_check_code->execute($params_check_code);
        
        if ($stmt_check_code->fetch()) {
            $_SESSION['error_message'] = "كود المادة '{$item_code}' موجود بالفعل لمادة أخرى.";
            header("Location: ../index.php?page=posm&action={$action}" . ($item_id ? "&id={$item_id}" : ''));
            exit();
        }
    }

    // تنفيذ الإضافة أو التحديث
    if ($action == 'add_item') {
        $sql = "INSERT INTO posm_items (item_name, item_code, description) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $item_name,
            !empty($item_code) ? $item_code : null, // أدخل NULL إذا كان الكود فارغًا
            $description
        ]);
        $_SESSION['success_message'] = "تم إضافة المادة الترويجية بنجاح.";

    } elseif ($action == 'edit_item' && $item_id) {
        $sql = "UPDATE posm_items SET item_name = ?, item_code = ?, description = ? WHERE item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $item_name,
            !empty($item_code) ? $item_code : null,
            $description,
            $item_id
        ]);
        $_SESSION['success_message'] = "تم تحديث بيانات المادة الترويجية بنجاح.";
    }

} catch (PDOException $e) {
    error_log("POSM item save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ المادة.";
    // إعادة التوجيه إلى نفس الصفحة في حالة حدوث خطأ للحفاظ على البيانات المدخلة
    header("Location: ../index.php?page=posm&action={$action}" . ($item_id ? "&id={$item_id}" : ''));
    exit();
}

// --- 4. إعادة التوجيه إلى قائمة المواد الترويجية ---
header("Location: ../index.php?page=posm&action=items_list");
exit();