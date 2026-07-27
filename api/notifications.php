<?php
/**
 * ASTPMS — API: Notifications Manager
 */

header('Content-Type: application/json');

require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../models/Notification.php';

initSession();

if (!isStudentLoggedIn() && !isAdminLoggedIn() && !isCompanyLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

$notificationModel = new Notification($pdo);

if ($action === 'mark_read' && $id) {
    $res = $notificationModel->markAsRead($id);
    echo json_encode(['success' => $res]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
