<?php
/**
 * ASTPMS — API: Check Duplicate Records
 */

header('Content-Type: application/json');

require_once '../config/app.php';
require_once '../config/db.php';
require_once '../config/auth.php';

$field = sanitize($_GET['field'] ?? '');
$value = sanitize($_GET['value'] ?? '');
$role  = sanitize($_GET['role']  ?? 'student');

if (empty($field) || empty($value)) {
    echo json_encode(['exists' => false]);
    exit;
}

$tables = ['student' => 'students', 'admin' => 'admins', 'company' => 'companies'];
$table  = $tables[$role] ?? 'students';
$allowedFields = ['email', 'student_id', 'enrollment_number'];

if (!in_array($field, $allowedFields)) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE {$field} = ?");
    $stmt->execute([$value]);
    $exists = (bool)$stmt->fetch();
    echo json_encode(['exists' => $exists]);
} catch (PDOException $e) {
    echo json_encode(['exists' => false]);
}
