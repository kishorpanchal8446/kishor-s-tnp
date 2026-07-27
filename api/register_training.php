<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['training_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

require_once '../config/db.php';
require_once '../models/Training.php';
require_once '../models/Notification.php';

try {
    $studentId = $_SESSION['student_id'];
    $trainingId = (int)$_POST['training_id'];

    $trainingModel = new Training($pdo);
    $notifModel = new Notification($pdo);

    // Retrieve training details to get the name
    $stmt = $pdo->prepare("SELECT * FROM training WHERE id = ?");
    $stmt->execute([$trainingId]);
    $training = $stmt->fetch();

    if (!$training) {
        echo json_encode(['success' => false, 'message' => 'Training program not found.']);
        exit;
    }

    if ($trainingModel->isRegistered($studentId, $trainingId)) {
        echo json_encode(['success' => false, 'message' => 'You are already registered for this training program.']);
        exit;
    }

    if ($trainingModel->register($studentId, $trainingId)) {
        $notifModel->add(
            $studentId,
            'Registered for Training',
            'You successfully enrolled in ' . $training['name'] . ', starting ' . date('M d, Y', strtotime($training['start_date'])) . '.',
            'Training'
        );
        echo json_encode(['success' => true, 'message' => 'Enrolled in ' . $training['name'] . ' successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
