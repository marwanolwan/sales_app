<?php
// controllers/collections.php

require_permission('manage_collections');

$page_title = "إدارة التحصيل الشهري";
$action = $_GET['action'] ?? 'list';
$collection_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// --- إعداد المتغيرات الأساسية ---
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];
$view_file = '';

// --- جلب البيانات اللازمة للفلاتر والنماذج ---

// جلب المندوبين المتاحين للمستخدم الحالي (مدير أو مشرف)
$reps_sql = "SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE";
$reps_params = [];
if ($current_user_role === 'supervisor') {
    $reps_sql .= " AND supervisor_id = ?";
    $reps_params[] = $current_user_id;
}
$reps_sql .= " ORDER BY full_name ASC";
$stmt_reps = $pdo->prepare($reps_sql);
$stmt_reps->execute($reps_params);
$representatives = $stmt_reps->fetchAll();

// مصفوفة الشهور (تستخدم في عدة أماكن)
$months_map = [1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل', 5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس', 9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر'];

// الفلاتر الرئيسية
$filter_year = $_GET['year'] ?? date('Y');
$filter_month = $_GET['month'] ?? date('n');

// --- التوجيه حسب الإجراء المطلوب ---
switch ($action) {
    case 'add':
    case 'edit':
        $collection_data = null;
        if ($action == 'edit' && $collection_id) {
            // استعلام لجلب بيانات السجل المراد تعديله
            $stmt = $pdo->prepare("SELECT * FROM monthly_collections WHERE collection_id = ?");
            $stmt->execute([$collection_id]);
            $collection_data = $stmt->fetch();

            if (!$collection_data) {
                $_SESSION['error_message'] = "سجل التحصيل غير موجود.";
                header("Location: index.php?page=collections");
                exit();
            }

            // التحقق من صلاحية المشرف (لا يمكنه تعديل سجل لا يخص فريقه)
            if ($current_user_role === 'supervisor') {
                $stmt_check = $pdo->prepare("SELECT u.user_id FROM users u WHERE u.user_id = ? AND u.supervisor_id = ?");
                $stmt_check->execute([$collection_data['representative_id'], $current_user_id]);
                if ($stmt_check->fetch() === false) {
                    $_SESSION['error_message'] = "ليس لديك صلاحية لتعديل هذا السجل.";
                    header("Location: index.php?page=collections");
                    exit();
                }
            }
        }
        $view_file = 'views/collections/form.php';
        break;
    
    case 'import':
        $page_title = "استيراد التحصيلات من Excel";
        $view_file = 'views/collections/import.php';
        break;
        
    case 'import_preview':
        $page_title = "معاينة استيراد التحصيلات";

        // التحقق من وجود بيانات في الجلسة أولاً
        if (empty($_SESSION['import_preview_data'])) {
            $_SESSION['error_message'] = "لا توجد بيانات للمعاينة أو انتهت صلاحية الجلسة.";
            header("Location: index.php?page=collections&action=import");
            exit();
        }

        // إذا كانت البيانات موجودة، قم بتمريرها إلى الواجهة
        $preview_data = $_SESSION['import_preview_data']['data'] ?? [];
        $import_errors = $_SESSION['import_preview_data']['errors'] ?? [];
        
        // تنظيف الجلسة فورًا بعد قراءة البيانات لمنع استخدامها مرة أخرى
        unset($_SESSION['import_preview_data']);
        
        $view_file = 'views/collections/import_preview.php';
        break;
    
    case 'list':
    default:
        // جلب قائمة التحصيلات المسجلة للفترة المحددة
        $sql = "SELECT mc.*, u.full_name as representative_name, rec.full_name as recorder_name
                FROM monthly_collections mc
                JOIN users u ON mc.representative_id = u.user_id
                LEFT JOIN users rec ON mc.recorded_by_user_id = rec.user_id
                WHERE mc.year = ? AND mc.month = ?";
        
        $params = [$filter_year, $filter_month];

        if ($current_user_role === 'supervisor') {
            $sql .= " AND u.supervisor_id = ?";
            $params[] = $current_user_id;
        }
        $sql .= " ORDER BY u.full_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $collections = $stmt->fetchAll();
        
        $view_file = 'views/collections/list.php';
        break;
}

// عرض القالب الرئيسي وتمرير الواجهة المحددة له
include 'views/layout.php';