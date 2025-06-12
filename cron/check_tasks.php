<?php
// cron/check_tasks.php
// هذا الملف مصمم ليتم تشغيله عبر Cron Job

// --- 1. إعداد البيئة ---

// تغيير الدليل الحالي إلى دليل المشروع الرئيسي لضمان عمل المسارات النسبية
chdir(dirname(__DIR__));

// تضمين الملفات الأساسية باستخدام مسارات مطلقة
require_once 'core/db.php';
include_once __DIR__ . '/../core/functions.php';

echo "Cron Job Started: " . date('Y-m-d H:i:s') . "\n";

// --- 2. القاعدة الأولى: تنبيه المهام المتأخرة ---

try {
    // جلب كل المهام التي تجاوزت تاريخ استحقاقها ولم تكتمل بعد
    // ولم يتم إرسال إشعار تأخير لها بعد (لتجنب إرسال إشعارات متكررة)
    $sql_overdue = "
        SELECT t.task_id, t.title, t.assigned_to_user_id
        FROM tasks t
        LEFT JOIN notifications n ON t.task_id = n.related_id AND n.type = 'overdue'
        WHERE t.status IN ('Not Started', 'In Progress')
          AND t.due_date IS NOT NULL
          AND t.due_date < NOW()
          AND n.notification_id IS NULL 
    ";
    
    $stmt_overdue = $pdo->query($sql_overdue);
    $overdue_tasks = $stmt_overdue->fetchAll();

    if (count($overdue_tasks) > 0) {
        $stmt_insert_notification = $pdo->prepare(
            "INSERT INTO notifications (user_id, message, link, type, related_id) VALUES (?, ?, ?, 'overdue', ?)"
        );
        
        foreach ($overdue_tasks as $task) {
            $message = "تنبيه: المهمة \"{$task['title']}\" قد تجاوزت تاريخ التسليم المحدد.";
            $link = "index.php?page=tasks&action=view&id={$task['task_id']}";
            
            // إرسال الإشعار للموظف المكلف بالمهمة
            $stmt_insert_notification->execute([$task['assigned_to_user_id'], $message, $link, $task['task_id']]);
            echo "Overdue notification sent for task ID: {$task['task_id']}\n";
        }
    } else {
        echo "No overdue tasks to notify.\n";
    }

} catch (PDOException $e) {
    // في بيئة الإنتاج، يجب تسجيل الخطأ في ملف سجل مخصص
    error_log("Cron Job (Overdue Tasks) Error: " . $e->getMessage());
    echo "Error processing overdue tasks: " . $e->getMessage() . "\n";
}


// --- 3. القاعدة الثانية: تنبيه المهام التي لم تبدأ بعد 24 ساعة ---

try {
    // جلب المهام التي تم إنشاؤها منذ أكثر من 24 ساعة وحالتها لا تزال "لم تبدأ"
    // ولم يتم إرسال إشعار بخصوصها من قبل
    $sql_not_started = "
        SELECT t.task_id, t.title, t.assigned_to_user_id
        FROM tasks t
        LEFT JOIN notifications n ON t.task_id = n.related_id AND n.type = 'not_started'
        WHERE t.status = 'Not Started'
          AND t.created_at < (NOW() - INTERVAL 24 HOUR)
          AND n.notification_id IS NULL
    ";
    
    $stmt_not_started = $pdo->query($sql_not_started);
    $not_started_tasks = $stmt_not_started->fetchAll();

    if (count($not_started_tasks) > 0) {
        $stmt_insert_notification = $pdo->prepare(
            "INSERT INTO notifications (user_id, message, link, type, related_id) VALUES (?, ?, ?, 'not_started', ?)"
        );

        foreach ($not_started_tasks as $task) {
            $message = "تذكير: لم يتم البدء في المهمة \"{$task['title']}\" بعد مرور 24 ساعة على إنشائها.";
            $link = "index.php?page=tasks&action=view&id={$task['task_id']}";
            
            $stmt_insert_notification->execute([$task['assigned_to_user_id'], $message, $link, $task['task_id']]);
            echo "Not Started notification sent for task ID: {$task['task_id']}\n";
        }
    } else {
        echo "No inactive tasks to notify.\n";
    }

} catch (PDOException $e) {
    error_log("Cron Job (Not Started Tasks) Error: " . $e->getMessage());
    echo "Error processing not started tasks: " . $e->getMessage() . "\n";
}

echo "Cron Job Finished: " . date('Y-m-d H:i:s') . "\n";