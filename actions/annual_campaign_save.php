<?php
// actions/annual_campaign_save.php
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
$redirect_url = "../index.php?page=annual_campaigns&customer_id={$customer_id}";

// التحقق من وجود customer_id
if (!$customer_id) {
    $_SESSION['error_message'] = "معرف العميل غير صالح.";
    header("Location: ../index.php?page=promotions");
    exit();
}

// استلام البيانات من النموذج
$promo_type_id = (int)($_POST['promo_type_id'] ?? 0);
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$duration = (int)($_POST['contract_duration_months'] ?? 0);
$monthly_value = (float)($_POST['monthly_value'] ?? 0);
$total_value = (float)($_POST['total_value'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// التحقق من صحة البيانات
if (empty($promo_type_id) || empty($start_date) || empty($end_date) || $duration <= 0) {
    $_SESSION['error_message'] = "الرجاء ملء جميع الحقول الإلزامية والتأكد من صحة التواريخ.";
    header("Location: {$redirect_url}&action={$action}" . ($campaign_id ? "&campaign_id={$campaign_id}" : ''));
    exit();
}

try {
    if ($action == 'add') {
        $sql = "INSERT INTO annual_campaigns (customer_id, promo_type_id, start_date, end_date, contract_duration_months, monthly_value, total_value, notes, created_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$customer_id, $promo_type_id, $start_date, $end_date, $duration, $monthly_value, $total_value, $notes, $_SESSION['user_id']];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تمت إضافة الحملة السنوية بنجاح.";

    } elseif ($action == 'edit' && $campaign_id) {
        $sql = "UPDATE annual_campaigns SET promo_type_id = ?, start_date = ?, end_date = ?, contract_duration_months = ?, monthly_value = ?, total_value = ?, notes = ?
                WHERE annual_campaign_id = ? AND customer_id = ?";
        $params = [$promo_type_id, $start_date, $end_date, $duration, $monthly_value, $total_value, $notes, $campaign_id, $customer_id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success_message'] = "تم تحديث الحملة السنوية بنجاح.";
    }

} catch (PDOException $e) {
    error_log("Annual campaign save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات أثناء حفظ الحملة.";
    $redirect_url .= "&action={$action}" . ($campaign_id ? "&campaign_id={$campaign_id}" : '');
}

header("Location: {$redirect_url}");
exit();