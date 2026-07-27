<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['resume_file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

require_once '../config/db.php';
require_once '../models/Student.php';
require_once '../models/Notification.php';

try {
    $studentId = $_SESSION['student_id'];
    $file = $_FILES['resume_file'];
    
    $studentModel = new Student($pdo);
    $notifModel = new Notification($pdo);
    $student = $studentModel->getById($studentId);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student record not found.']);
        exit;
    }

    // 1. Validate File Format
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if ($fileExt !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed.']);
        exit;
    }

    // 2. Validate File Size (2 MB = 2,097,152 bytes)
    $maxSize = 2 * 1024 * 1024;
    if ($fileSize > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the 2MB limit.']);
        exit;
    }

    if ($fileError !== 0) {
        echo json_encode(['success' => false, 'message' => 'An error occurred during file upload. Error Code: ' . $fileError]);
        exit;
    }

    // 3. Define Upload Destination
    $uploadDir = '../uploads/resumes/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Secure file naming (student_id + sanitize name)
    $newFileName = 'resume_student_' . $studentId . '_' . time() . '.pdf';
    $destPath = $uploadDir . $newFileName;
    $dbPath = 'uploads/resumes/' . $newFileName;

    if (move_uploaded_file($fileTmpName, $destPath)) {
        // Evaluate dynamic Mock Resume Score: base score 65, add score based on skills counts
        $skills = !empty($student['skills']) ? explode(',', $student['skills']) : [];
        $skillsCount = count(array_filter(array_map('trim', $skills)));
        
        $baseScore = 70;
        $bonus = min($skillsCount * 4, 25); // Max bonus is 25
        $resumeScore = $baseScore + $bonus;

        // Save path and score to DB
        if ($studentModel->updateResume($studentId, $dbPath, $resumeScore)) {
            $notifModel->add(
                $studentId,
                'Resume Uploaded',
                'Your resume was updated successfully. Calculated ATS Score: ' . $resumeScore . '%.',
                'Result'
            );
            echo json_encode([
                'success' => true, 
                'message' => 'Resume uploaded and evaluated successfully!',
                'score' => $resumeScore,
                'path' => $dbPath
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save resume details in database.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to write file to storage. Check folder permissions.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
