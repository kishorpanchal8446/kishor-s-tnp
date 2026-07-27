<?php
/**
 * ASTPMS — API: Global Live Search
 */

header('Content-Type: application/json');

require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';

$query = sanitize($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];
$search = '%' . $query . '%';

// Search Placement Jobs
$stmt = $pdo->prepare("
    SELECT j.id, j.title, c.name AS company, 'job' AS type
    FROM jobs j
    JOIN companies c ON j.company_id = c.id
    WHERE j.title LIKE ? OR c.name LIKE ? OR j.skills_required LIKE ?
    LIMIT 4
");
$stmt->execute([$search, $search, $search]);
foreach ($stmt->fetchAll() as $row) {
    $results[] = [
        'title'    => $row['title'],
        'subtitle' => 'Job at ' . $row['company'],
        'url'      => 'jobs.php?q=' . urlencode($row['title']),
        'icon'     => 'fas fa-briefcase text-warning',
    ];
}

// Search Training Programs
$stmt = $pdo->prepare("
    SELECT id, title, trainer_name FROM training_programs
    WHERE title LIKE ? OR description LIKE ?
    LIMIT 3
");
$stmt->execute([$search, $search]);
foreach ($stmt->fetchAll() as $row) {
    $results[] = [
        'title'    => $row['title'],
        'subtitle' => 'Training Program by ' . ($row['trainer_name'] ?? 'T&P Cell'),
        'url'      => 'training.php',
        'icon'     => 'fas fa-graduation-cap text-success',
    ];
}

// Search Higher Studies
$stmt = $pdo->prepare("
    SELECT id, title, exam_name FROM higher_studies
    WHERE title LIKE ? OR exam_name LIKE ?
    LIMIT 3
");
$stmt->execute([$search, $search]);
foreach ($stmt->fetchAll() as $row) {
    $results[] = [
        'title'    => $row['title'],
        'subtitle' => 'Higher Studies: ' . $row['exam_name'],
        'url'      => 'higher_studies.php',
        'icon'     => 'fas fa-university text-purple',
    ];
}

echo json_encode($results);
