<?php
/**
 * ASTPMS — API: Apply Job
 */

header('Content-Type: application/json');

require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../models/Student.php';
require_once '../models/Job.php';
require_once '../models/Application.php';

initSession();

if (!isStudentLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please sign in as a student.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh.']);
    exit;
}

$jobId     = (int)($_POST['job_id'] ?? 0);
$studentId = $_SESSION['student_id'];

if (!$jobId) {
    echo json_encode(['success' => false, 'message' => 'Invalid job ID.']);
    exit;
}

$studentModel     = new Student($pdo);
$jobModel         = new Job($pdo);
$applicationModel = new Application($pdo);

$student = $studentModel->getById($studentId);
$job     = $jobModel->getById($jobId);

if (!$job) {
    echo json_encode(['success' => false, 'message' => 'Job posting not found.']);
    exit;
}

// Check eligibility
if (!$jobModel->isStudentEligible($jobId, $student)) {
    echo json_encode(['success' => false, 'message' => 'You do not meet the minimum CGPA or branch requirements for this position.']);
    exit;
}

$result = $applicationModel->apply($studentId, $jobId, $student['resume_path']);

if ($result === true) {
    logActivity($pdo, $studentId, 'student', 'apply_job', "Applied for job #{$jobId} ({$job['title']} at {$job['company_name']})", 'jobs');
    
    // Create notification
    $pdo->prepare("
        INSERT INTO notifications (recipient_id, recipient_role, sender_role, title, message, type)
        VALUES (?, 'student', 'system', ?, ?, 'Job Alert')
    ")->execute([
        $studentId,
        'Application Submitted',
        "Your application for {$job['title']} at {$job['company_name']} has been submitted successfully!"
    ]);

    echo json_encode(['success' => true, 'message' => "Successfully applied for {$job['title']} at {$job['company_name']}!"]);
} else {
    echo json_encode(['success' => false, 'message' => $result]);
}
