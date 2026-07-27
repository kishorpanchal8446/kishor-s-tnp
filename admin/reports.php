<?php
/**
 * ASTPMS 2.0 — Admin Reports & Analytics Exporter
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

initSession();
requireAdminLogin();

// Export CSV handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=placement_report_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student ID', 'Name', 'Email', 'Branch', 'CGPA', 'Placement Status', 'Company', 'Package (LPA)']);

    $rows = $pdo->query("
        SELECT student_id, name, email, branch, cgpa, placement_status, placed_company, placed_package
        FROM students
        ORDER BY placement_status DESC, cgpa DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    exit;
}

$pageTitle = 'Placement Reports — Admin Panel';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$totalStudents  = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active=1")->fetchColumn();
$placedStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE placement_status='Placed'")->fetchColumn();
$unplacedCount  = $totalStudents - $placedStudents;
$highestPackage = $pdo->query("SELECT MAX(placed_package) FROM students")->fetchColumn() ?: 0.00;
$avgPackage     = $pdo->query("SELECT AVG(placed_package) FROM students WHERE placement_status='Placed'")->fetchColumn() ?: 0.00;

$students = $pdo->query("
    SELECT student_id, name, email, branch, cgpa, placement_status, placed_company, placed_package
    FROM students
    ORDER BY placement_status DESC, cgpa DESC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-file-export text-primary-color me-2"></i>Campus Placement Reports &amp; Analytics
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Generate official placement summaries and export CSV/Excel student rosters for university administration.
            </p>
        </div>
        <a href="reports.php?export=csv" class="btn-premium" style="background:linear-gradient(135deg,#10B981,#059669);">
            <i class="fas fa-file-csv me-2"></i>Export Placement CSV Report
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4" data-aos="fade-up">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-blue mx-auto mb-2"><i class="fas fa-users"></i></div>
                <h3 class="fw-bold mb-0"><?= $totalStudents ?></h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Total Batch Size</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-green mx-auto mb-2"><i class="fas fa-user-check"></i></div>
                <h3 class="fw-bold mb-0 text-success"><?= $placedStudents ?></h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Placed Students</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-cyan mx-auto mb-2"><i class="fas fa-trophy"></i></div>
                <h3 class="fw-bold mb-0 text-primary-color">₹<?= number_format($highestPackage, 2) ?> LPA</h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Highest Offer</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-purple mx-auto mb-2"><i class="fas fa-chart-line"></i></div>
                <h3 class="fw-bold mb-0 text-purple">₹<?= number_format($avgPackage, 2) ?> LPA</h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Average Package</div>
            </div>
        </div>
    </div>

    <!-- Student Roster Table -->
    <div class="card-premium p-4" data-aos="fade-up">
        <h5 class="fw-bold mb-3" style="font-family:'Outfit',sans-serif;"><i class="fas fa-list me-2 text-primary-color"></i>Batch Placement Roster</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportsTable">
                <thead class="table-light">
                    <tr>
                        <th>Student ID</th>
                        <th>Name &amp; Email</th>
                        <th>Branch</th>
                        <th>CGPA</th>
                        <th>Placement Status</th>
                        <th>Placed Company</th>
                        <th>Package (LPA)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><span class="fw-bold"><?= h($s['student_id']) ?></span></td>
                        <td>
                            <div class="fw-bold"><?= h($s['name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($s['email']) ?></div>
                        </td>
                        <td><?= h($s['branch']) ?></td>
                        <td><span class="fw-bold"><?= h($s['cgpa']) ?></span></td>
                        <td>
                            <?php if ($s['placement_status'] === 'Placed'): ?>
                                <span class="badge-premium bg-light-success">Placed</span>
                            <?php else: ?>
                                <span class="badge-premium bg-light-danger">Unplaced</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="fw-semibold" style="color:var(--primary-mid);"><?= h($s['placed_company'] ?? '—') ?></span></td>
                        <td>
                            <?php if ($s['placed_package']): ?>
                                <span class="fw-bold text-success">₹<?= h($s['placed_package']) ?> LPA</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#reportsTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
