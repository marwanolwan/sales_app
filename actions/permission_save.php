<?php
// actions/permission_save.php

require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=permissions");
    exit();
}

require_permission('manage_permissions');
verify_csrf_token();

// البيانات المرسلة من النموذج
$submitted_permissions = $_POST['permissions'] ?? [];

// قائمة الأدوار المعرفة في النظام
$roles = ['admin', 'supervisor', 'representative', 'promoter'];

// قائمة بكل الميزات الممكنة (يجب أن تكون متطابقة مع القائمة في المتحكم)
$all_possible_features = [
    'manage_users', 'manage_regions', 'manage_permissions', 'manage_customer_categories',
    'manage_customers', 'manage_product_families', 'manage_products', 'manage_sales_targets',
    'manage_monthly_sales', 'manage_item_sales', 'manage_item_targets', 
    'view_dashboard_summaries', 'view_sales_analysis', 'view_item_target_analysis',
];

try {
    $pdo->beginTransaction();

    // استعلام واحد فعال يقوم بالإضافة أو التحديث
    $stmt_upsert = $pdo->prepare(
        "INSERT INTO role_permissions (role, feature, can_access) 
         VALUES (:role, :feature, :can_access)
         ON DUPLICATE KEY UPDATE can_access = VALUES(can_access)"
    );

    foreach ($roles as $role) {
        foreach ($all_possible_features as $feature) {
            // تحديد القيمة: 1 إذا تم تحديدها في النموذج، و 0 إذا لم يتم تحديدها
            $can_access = isset($submitted_permissions[$role][$feature]) ? 1 : 0;
            
            // فرض صلاحيات المدير الأساسية (حتى لو حاول أحد تعديلها من طرف العميل)
            $forced_admin_permissions = ['manage_permissions', 'view_dashboard_summaries'];
            if ($role == 'admin' && in_array($feature, $forced_admin_permissions)) {
                $can_access = 1;
            }

            // تنفيذ الاستعلام
            $stmt_upsert->execute([
                'role'       => $role,
                'feature'    => $feature,
                'can_access' => $can_access
            ]);
        }
    }
    
    $pdo->commit();
    
    // مسح ذاكرة التخزين المؤقت للصلاحيات في الجلسة لإجبارها على التحديث في الطلب التالي
    unset($_SESSION['permissions']);

    $_SESSION['success_message'] = "تم تحديث الصلاحيات بنجاح.";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Permissions save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "خطأ في تحديث الصلاحيات.";
}

header("Location: ../index.php?page=permissions");
exit();