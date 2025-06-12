<?php
// ajax/ajax_get_reps_by_supervisor_or_all.php

// المسار الجديد للملفات الأساسية
require_once '../core/db.php';
require_once '../core/functions.php';

header('Content-Type: application/json');

try {
    // لا تستخدم require_permission هنا لأنها تعيد التوجيه، فقط تحقق
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'supervisor'])) {
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }

    $supervisor_id_filter = $_GET['supervisor_id'] ?? 'all';
    $requesting_user_role = $_SESSION['user_role'];
    $requesting_user_id = $_SESSION['user_id'];

    $sql = "SELECT user_id, full_name FROM users WHERE role = 'representative' AND is_active = TRUE";
    $params = [];

    if ($requesting_user_role === 'supervisor') {
        $sql .= " AND supervisor_id = :requesting_supervisor_id";
        $params[':requesting_supervisor_id'] = $requesting_user_id;
    } elseif ($requesting_user_role === 'admin' && $supervisor_id_filter !== 'all' && is_numeric($supervisor_id_filter)) {
        $sql .= " AND supervisor_id = :selected_supervisor_id";
        $params[':selected_supervisor_id'] = (int)$supervisor_id_filter;
    }
    
    $sql .= " ORDER BY full_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $representatives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($representatives);

} catch (Exception $e) {
    error_log("AJAX Error: " . $e->getMessage());
    echo json_encode(['error' => 'Database query failed.']);
}