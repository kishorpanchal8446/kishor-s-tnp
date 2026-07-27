<?php
/**
 * ASTPMS 2.0 — Admin Applications Tracker
 */

$pageTitle = 'Manage Applications — Admin Panel';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $appId     = (int)$_POST['application_id'];
    $newStatus = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $appId]);
    $success = 'Application status updated to ' . $newStatus;
}

$applications = $pdo->query("
    SELECT a.*, s.name as student_name, s.email as student_email, s.cgpa, s.branch, j.role as job_title, c.name as company_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    ORDER BY a.applied_at DESC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-file-signature text-primary-color me-2"></i>Master Student Job Applications
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Review student applications across all company recruitment drives and update selection stages.
            </p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert-premium alert-premium-success mb-4"><i class="fas fa-check-circle me-2"></i><?= h($success) ?></div>
    <?php endif; ?>

    <div class="card-premium p-4" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="appsTable">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Branch &amp; CGPA</th>
                        <th>Job Role</th>
                        <th>Company</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($app['student_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($app['student_email']) ?></div>
                        </td>
                        <td>
                            <div><?= h($app['branch']) ?></div>
                            <span class="badge-premium bg-light-success" style="font-size:0.7rem;"><?= h($app['cgpa']) ?> CGPA</span>
                        </td>
                        <td><span class="fw-bold"><?= h($app['job_title']) ?></span></td>
                        <td><span class="fw-semibold" style="color:var(--primary-mid);"><?= h($app['company_name']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                        <td>
                            <?php
                            $badgeMap = [
                                'Applied' => 'bg-light-primary',
                                'Shortlisted' => 'bg-light-cyan',
                                'Interview Scheduled' => 'bg-light-warning',
                                'Selected' => 'bg-light-success',
                                'Rejected' => 'bg-light-danger'
                            ];
                            $cls = $badgeMap[$app['status']] ?? 'bg-light-primary';
                            ?>
                            <span class="badge-premium <?= $cls ?>"><?= h($app['status']) ?></span>
                        </td>
                        <td>
                            <form method="POST" action="applications.php" class="d-flex gap-1 align-items-center">
                                <?= csrfInput() ?>
                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="font-size:0.78rem;width:130px;">
                                    <?php foreach (['Applied', 'Shortlisted', 'Interview Scheduled', 'Selected', 'Rejected'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $app['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary" style="padding:2px 8px;font-size:0.75rem;">
                                    Save
                                </button>
                            </form>
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
    $('#appsTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
