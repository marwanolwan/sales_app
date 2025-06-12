<?php
// actions/temp_campaign_save.php
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
$campaign_id = ($action == 'edit') ? (int)($_POST['campaign_id'] ?? 0) : null;
$redirect_url = "../index.php?page=temp_campaigns&customer_id={$customer_id}";

if (!$customer_id) {
    $_SESSION['error_message'] = "معرف العميل غير صالح.";
    header("Location: ../index.php?page=promotions");
    exit();
}

// استلام البيانات من النموذج
$promo_type_id = (int)($_POST['promo_type_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
$value = (float)($_POST['value'] ?? 0);
$status = $_POST['status'] ?? 'active';
$notes = trim($_POST['notes'] ?? '');

// التحقق من صحة البيانات
if (empty($promo_type_id) || empty($description)) {
    $_SESSION['error_message'] = "الرجاء اختيار نوع الدعاية وإدخال وصف للحملة.";
    header("Location: {$redirect_url}&action={$action}" . ($campaign_id ? "&campaign_id={$campaign_id}" : ''));
    exit();
}

try {
    if ($action == 'add') {
        $sql = "INSERT INTO temp_campaigns (customer_id, promo_type_id, description, start_date, end_date, value, status, notes, created_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$customer_id, $promo_type_id, $description, $start_date, $end_date, $value, $status, $notes, $_SESSION['user_id']];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تمت إضافة الحملة المؤقتة بنجاح.";

    } elseif ($action == 'edit' && $campaign_id) {
        $sql = "UPDATE temp_campaigns SET promo_type_id = ?, description = ?, start_date = ?, end_date = ?, value = ?, status = ?, notes = ?
                WHERE temp_campaign_id = ? AND customer_id = ?";
        $params = [$promo_type_id, $description, $start_date, $end_date, $value, $status, $notes, $campaign_id, $customer_id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم تحديث الحملة المؤقتة بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Temp campaign save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ الحملة.";
    // إعادة التوجيه إلى صفحة النموذج في حالة الخطأ
    $redirect_url .= "&action={$action}" . ($campaign_id ? "&campaign_id={$campaign_id}" : '');
}

header("Location: {$redirect_url}");
exit();