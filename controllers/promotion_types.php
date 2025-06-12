<?php
// controllers/promotion_types.php

// الصلاحية المطلوبة (افترض أنها manage_promotions)
require_permission('manage_promotions');

// تحديد الإجراء والـ ID
$action = $_GET['action'] ?? 'list';
$promo_type_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// إعداد عنوان الصفحة
$page_title = "إدارة أنواع الدعاية";
$form_title = "إضافة نوع دعاية جديد"; // العنوان الافتراضي للنموذج

// جلب قائمة أنواع الدعاية (للعرض في الجدول)
$stmt = $pdo->query("SELECT pt.*, (SELECT COUNT(*) FROM annual_campaigns WHERE promo_type_id = pt.promo_type_id) + (SELECT COUNT(*) FROM temp_campaigns WHERE promo_type_id = pt.promo_type_id) as campaign_count FROM promotion_types pt ORDER BY pt.name ASC");
$promotion_types = $stmt->fetchAll();

// إعداد بيانات النموذج (للتعديل أو لنموذج فارغ)
$promo_type_data = null;
$form_action = 'add'; // الإجراء الافتراضي للنموذج هو الإضافة

if ($action == 'edit' && $promo_type_id) {
    $form_title = 'تعديل نوع الدعاية';
    $form_action = 'edit';
    $stmt_edit = $pdo->prepare("SELECT * FROM promotion_types WHERE promo_type_id = ?");
    $stmt_edit->execute([$promo_type_id]);
    $promo_type_data = $stmt_edit->fetch();
    if (!$promo_type_data) {
        $_SESSION['error_message'] = "نوع الدعاية المطلوب للتعديل غير موجود.";
        header("Location: index.php?page=promotion_types");
        exit();
    }
}

// تحديد ملف الواجهة الرئيسي وعرض القالب
$view_file = 'views/promotions/type_list.php';
include 'views/layout.php';