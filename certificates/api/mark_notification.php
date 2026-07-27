<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../config/db.php';
require_once '../models/Notification.php';

try {
    $studentId = $_SESSION['student_id'];
    $action = $_POST['action'] ?? '';
    
    $notifModel = new Notification($pdo);

    if ($action === 'mark_all') {
        if ($notifModel->markAllAsRead($studentId)) {
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read.']);
        }
    } elseif ($action === 'mark_single' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($notifModel->markAsRead($id, $studentId)) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
