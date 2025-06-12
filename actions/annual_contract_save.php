<?php
// actions/annual_contract_save.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

require_permission('manage_promotions');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$customer_id = (int)($_POST['customer_id'] ?? 0);
$contract_id = ($action == 'edit') ? (int)($_POST['contract_id'] ?? 0) : null;
$redirect_url = "../index.php?page=annual_contracts&customer_id={$customer_id}";

if (!$customer_id) {
    $_SESSION['error_message'] = "معرف العميل غير صالح.";
    header("Location: ../index.php?page=promotions");
    exit();
}

// استلام البيانات
$year = (int)($_POST['year'] ?? 0);
$notes = trim($_POST['notes'] ?? '');
$current_file_path = $_POST['current_file_path'] ?? null;
$file_path_to_save = $current_file_path; // القيمة الافتراضية

// استلام بيانات الأهداف
$targets = [];
for ($i = 1; $i <= 3; $i++) {
    $targets[$i] = [
        'value' => !empty($_POST["target_{$i}_value"]) ? (float)$_POST["target_{$i}_value"] : null,
        'bonus' => !empty($_POST["target_{$i}_bonus"]) ? (float)$_POST["target_{$i}_bonus"] : null,
    ];
}

if (empty($year)) {
    $_SESSION['error_message'] = "الرجاء تحديد سنة العقد.";
    header("Location: {$redirect_url}&action={$action}" . ($contract_id ? "&id={$contract_id}" : ''));
    exit();
}

// معالجة رفع الملف
if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/annual_contracts/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    $file = $_FILES['contract_file'];
    $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (in_array($file_ext, $allowed_ext)) {
        // حذف الملف القديم إذا كان موجودًا وسيتم رفع ملف جديد
        if ($action == 'edit' && $current_file_path && file_exists($upload_dir . $current_file_path)) {
            unlink($upload_dir . $current_file_path);
        }
        
        $new_filename = "contract_{$customer_id}_{$year}_" . uniqid() . '.' . $file_ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
            $file_path_to_save = $new_filename;
        } else {
            $_SESSION['error_message'] = "فشل في رفع الملف. تحقق من صلاحيات المجلد.";
            header("Location: {$redirect_url}&action={$action}" . ($contract_id ? "&id={$contract_id}" : ''));
            exit();
        }
    } else {
        $_SESSION['error_message'] = "نوع الملف غير مسموح به.";
        header("Location: {$redirect_url}&action={$action}" . ($contract_id ? "&id={$contract_id}" : ''));
        exit();
    }
}


try {
    // التحقق من عدم وجود عقد لنفس العميل في نفس السنة
    $sql_check = "SELECT contract_id FROM annual_contracts WHERE customer_id = ? AND year = ? AND contract_id != ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$customer_id, $year, $contract_id ?? 0]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "يوجد عقد مسجل بالفعل لهذا العميل في سنة {$year}.";
        header("Location: {$redirect_url}&action={$action}" . ($contract_id ? "&id={$contract_id}" : ''));
        exit();
    }

    $params = [
        $customer_id, $year,
        $targets[1]['value'], $targets[1]['bonus'],
        $targets[2]['value'], $targets[2]['bonus'],
        $targets[3]['value'], $targets[3]['bonus'],
        $file_path_to_save, $notes
    ];
    
    if ($action == 'add') {
        $sql = "INSERT INTO annual_contracts (customer_id, year, target_1_value, target_1_bonus, target_2_value, target_2_bonus, target_3_value, target_3_bonus, contract_file_path, notes, created_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params[] = $_SESSION['user_id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تمت إضافة العقد بنجاح.";
    } elseif ($action == 'edit' && $contract_id) {
        $sql = "UPDATE annual_contracts SET customer_id = ?, year = ?, target_1_value = ?, target_1_bonus = ?, target_2_value = ?, target_2_bonus = ?, target_3_value = ?, target_3_bonus = ?, contract_file_path = ?, notes = ?
                WHERE contract_id = ?";
        $params[] = $contract_id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم تحديث العقد بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Contract save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ العقد.";
    $redirect_url .= "&action={$action}" . ($contract_id ? "&id={$contract_id}" : '');
}

header("Location: {$redirect_url}");
exit();