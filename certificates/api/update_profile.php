<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

require_once '../config/db.php';
require_once '../models/Student.php';
require_once '../models/Notification.php';

try {
    $studentId = $_SESSION['student_id'];
    
    // Extract and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $branch = trim($_POST['branch'] ?? '');
    $semester = (int)($_POST['semester'] ?? 0);
    $cgpa = (float)($_POST['cgpa'] ?? 0.0);
    $skills = trim($_POST['skills'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($department) || empty($branch)) {
        echo json_encode(['success' => false, 'message' => 'All core profile fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    if ($semester < 1 || $semester > 8) {
        echo json_encode(['success' => false, 'message' => 'Semester must be between 1 and 8.']);
        exit;
    }

    if ($cgpa < 0.0 || $cgpa > 10.0) {
        echo json_encode(['success' => false, 'message' => 'CGPA must be between 0.0 and 10.0.']);
        exit;
    }

    $studentModel = new Student($pdo);
    $notifModel = new Notification($pdo);

    $updateData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'department' => $department,
        'branch' => $branch,
        'semester' => $semester,
        'cgpa' => $cgpa,
        'skills' => $skills
    ];

    if ($studentModel->updateProfile($studentId, $updateData)) {
        // Log notification
        $notifModel->add(
            $studentId,
            'Profile Updated',
            'Your academic and contact profile details were updated successfully.',
            'Result'
        );
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile details. Database error.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
