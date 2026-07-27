<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['job_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
    exit;
}

require_once '../config/db.php';
require_once '../models/Student.php';
require_once '../models/Job.php';
require_once '../models/Application.php';
require_once '../models/Notification.php';

try {
    $studentId = $_SESSION['student_id'];
    $jobId = (int)$_POST['job_id'];

    $studentModel = new Student($pdo);
    $jobModel = new Job($pdo);
    $appModel = new Application($pdo);
    $notifModel = new Notification($pdo);

    $student = $studentModel->getById($studentId);
    $job = $jobModel->getById($jobId);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student record not found.']);
        exit;
    }

    if (!$job) {
        echo json_encode(['success' => false, 'message' => 'Job record not found.']);
        exit;
    }

    // 1. Check if student already applied
    if ($appModel->hasApplied($studentId, $jobId)) {
        echo json_encode(['success' => false, 'message' => 'You have already applied for this position.']);
        exit;
    }

    // 2. Check eligibility criteria
    if (!$jobModel->checkEligibility($student, $job)) {
        echo json_encode(['success' => false, 'message' => 'Eligibility criteria not met. Please check CGPA, branch, or passing year.']);
        exit;
    }

    // 3. Process application
    if ($appModel->apply($studentId, $jobId)) {
        // Record notifications log
        $notifModel->add(
            $studentId,
            'Job Applied successfully',
            'Your application for ' . $job['role'] . ' at ' . $job['company_name'] . ' has been recorded.',
            'Job Alert'
        );
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully to ' . $job['company_name'] . '!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to apply. Database error occurred.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
