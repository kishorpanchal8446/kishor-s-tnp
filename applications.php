<?php
/**
 * ASTPMS — Student Applications Tracker
 */

$pageTitle = 'My Applications — ASTPMS';
require_once 'views/header.php';
require_once 'views/sidebar.php';
require_once 'models/Application.php';

$applicationModel = new Application($pdo);
$applications     = $applicationModel->getByStudent($_SESSION['student_id']);
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-clipboard-list text-primary-color me-2"></i>My Applications Tracker
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                You have submitted <strong><?= count($applications) ?></strong> job applications.
            </p>
        </div>
        <a href="jobs.php" class="btn-premium" style="font-size:0.82rem;">
            <i class="fas fa-plus me-1"></i>Apply for More Jobs
        </a>
    </div>

    <!-- Applications Table Card -->
    <div class="card-premium p-4" data-aos="fade-up">
        <div style="overflow-x:auto;">
            <table class="table-premium w-100" id="applicationsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job Role</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Package</th>
                        <th>Applied On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $i => $app):
                        $statusClass = match ($app['status']) {
                            'Selected'            => 'status-selected',
                            'Shortlisted'         => 'status-shortlisted',
                            'Interview Scheduled' => 'status-interview',
                            'Rejected'            => 'status-rejected',
                            default               => 'status-applied',
                        };
                    ?>
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.8rem;"><?= $i + 1 ?></td>
                        <td>
                            <div class="fw-bold" style="font-size:0.875rem;color:var(--text);"><?= h($app['role']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($app['location']) ?></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;color:var(--primary-mid);">
                                    <?= strtoupper(substr($app['company_name'], 0, 2)) ?>
                                </div>
                                <span style="font-size:0.875rem;font-weight:600;"><?= h($app['company_name']) ?></span>
                            </div>
                        </td>
                        <td><span class="badge-premium bg-light-primary" style="font-size:0.72rem;"><?= h($app['job_type']) ?></span></td>
                        <td style="font-weight:700;color:var(--success);font-size:0.875rem;">₹<?= h($app['package_lpa']) ?> LPA</td>
                        <td style="font-size:0.78rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                        <td><span class="badge-premium <?= $statusClass ?>" style="padding:6px 12px;font-size:0.75rem;"><?= h($app['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="fas fa-inbox" style="font-size:2.5rem;opacity:0.3;margin-bottom:0.5rem;display:block;"></i>
                            No applications submitted yet. <a href="jobs.php" style="color:var(--primary-mid);font-weight:600;">Browse available jobs</a>.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extraScripts = "
<script>
\$(document).ready(function() {
    \$('#applicationsTable').DataTable({
        pageLength: 10,
        order: [[5, 'desc']],
        language: { search: 'Search applications:' }
    });
});
</script>";
require_once 'views/footer.php';
?>
