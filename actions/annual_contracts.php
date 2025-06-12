<?php
// controllers/annual_contracts.php
require_permission('manage_promotions');

$action = $_GET['action'] ?? 'list';
$customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : null;
$contract_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;

if (!$customer_id) { header("Location: index.php?page=promotions"); exit(); }

$stmt_cust = $pdo->prepare("SELECT name FROM customers WHERE customer_id = ?");
$stmt_cust->execute([$customer_id]);
$customer = $stmt_cust->fetch();
if (!$customer) { header("Location: index.php?page=promotions"); exit(); }

$page_title = "العقود السنوية للزبون: " . htmlspecialchars($customer['name']);
$view_file = '';

switch ($action) {
    case 'add':
    case 'edit':
        $page_title .= ($action == 'add') ? ' - إضافة عقد جديد' : ' - تعديل عقد';
        $contract_data = null;
        if ($action == 'edit' && $contract_id) {
            $stmt = $pdo->prepare("SELECT * FROM annual_contracts WHERE contract_id = ? AND customer_id = ?");
            $stmt->execute([$contract_id, $customer_id]);
            $contract_data = $stmt->fetch();
            if (!$contract_data) {
                $_SESSION['error_message'] = "العقد غير موجود.";
                header("Location: index.php?page=annual_contracts&customer_id={$customer_id}");
                exit();
            }
        }
        $view_file = 'views/promotions/contract_form.php';
        break;

    case 'list':
    default:
        $stmt = $pdo->prepare("SELECT * FROM annual_contracts WHERE customer_id = ? ORDER BY year DESC");
        $stmt->execute([$customer_id]);
        $contracts = $stmt->fetchAll();
        $view_file = 'views/promotions/contract_list.php';
        break;
}

include 'views/layout.php';