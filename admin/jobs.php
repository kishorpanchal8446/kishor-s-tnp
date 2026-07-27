<?php
/**
 * ASTPMS 2.0 — Admin Job Drives Management
 */

$pageTitle = 'Manage Jobs — Admin Panel';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$success = '';

if (isset($_GET['toggle']) && validateCsrfToken($_GET['csrf'] ?? '')) {
    $jobId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE jobs SET status = IF(status='Open','Closed','Open') WHERE id = ?");
    $stmt->execute([$jobId]);
    $success = 'Job status updated successfully.';
}

$jobs = $pdo->query("
    SELECT j.*, c.name as company_name, COUNT(a.id) as app_count
    FROM jobs j
    JOIN companies c ON j.company_id = c.id
    LEFT JOIN applications a ON j.id = a.job_id
    GROUP BY j.id
    ORDER BY j.created_at DESC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-briefcase text-primary-color me-2"></i>Campus Placement Job Drives
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Monitor corporate recruitment drives, applicant counts, package criteria, and status.
            </p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert-premium alert-premium-success mb-4"><i class="fas fa-check-circle me-2"></i><?= h($success) ?></div>
    <?php endif; ?>

    <div class="card-premium p-4" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="jobsTable">
                <thead class="table-light">
                    <tr>
                        <th>Job Role</th>
                        <th>Recruiting Company</th>
                        <th>Package (LPA)</th>
                        <th>Min CGPA</th>
                        <th>Applicants</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $j): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($j['role']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($j['location']) ?> · <?= h($j['job_type']) ?></div>
                        </td>
                        <td><span class="fw-semibold" style="color:var(--primary-mid);"><?= h($j['company_name']) ?></span></td>
                        <td><span class="fw-bold text-success">₹<?= h($j['package_lpa']) ?> LPA</span></td>
                        <td><?= h($j['min_cgpa']) ?></td>
                        <td><span class="badge-premium bg-light-cyan"><?= (int)$j['app_count'] ?> Applicants</span></td>
                        <td><?= date('M d, Y', strtotime($j['deadline'])) ?></td>
                        <td>
                            <?php if ($j['status'] === 'Open'): ?>
                                <span class="badge-premium bg-light-success">Open</span>
                            <?php else: ?>
                                <span class="badge-premium bg-light-danger">Closed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="jobs.php?toggle=<?= $j['id'] ?>&csrf=<?= generateCsrfToken() ?>" class="btn btn-sm btn-outline-secondary">
                                Toggle Status
                            </a>
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
    $('#jobsTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
