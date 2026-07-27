<?php
/**
 * ASTPMS 2.0 — Recruiter Dashboard
 */

$pageTitle = 'Recruiter Dashboard — ASTPMS';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$companyId = $_SESSION['company_id'];

$totalJobs     = $pdo->query("SELECT COUNT(*) FROM jobs WHERE company_id = $companyId")->fetchColumn();
$activeJobs    = $pdo->query("SELECT COUNT(*) FROM jobs WHERE company_id = $companyId AND status='Open'")->fetchColumn();
$totalApps     = $pdo->query("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = $companyId")->fetchColumn();
$shortlisted   = $pdo->query("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = $companyId AND a.status IN ('Shortlisted', 'Interview Scheduled', 'Selected')")->fetchColumn();

$recentApps = $pdo->query("
    SELECT a.*, s.name as student_name, s.email as student_email, s.cgpa, s.branch, j.role as job_title
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = $companyId
    ORDER BY a.applied_at DESC
    LIMIT 6
")->fetchAll();
?>

<div class="container-fluid px-0">
    <!-- Welcome Header -->
    <div class="welcome-banner mb-4" data-aos="fade-up" style="background:linear-gradient(135deg, #0891B2 0%, #1E3A8A 100%);">
        <h2 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
            Welcome, <?= h($_SESSION['company_name']) ?>!
        </h2>
        <p style="color:rgba(255,255,255,0.85);margin-bottom:0;">
            Manage your campus recruitment drives, review student applicant profiles, and schedule interview rounds.
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-4" data-aos="fade-up">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-cyan mx-auto mb-2"><i class="fas fa-briefcase"></i></div>
                <h3 class="fw-bold mb-0"><?= $totalJobs ?></h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Total Job Drives</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-green mx-auto mb-2"><i class="fas fa-bullhorn"></i></div>
                <h3 class="fw-bold mb-0 text-success"><?= $activeJobs ?></h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Active Job Openings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-blue mx-auto mb-2"><i class="fas fa-users"></i></div>
                <h3 class="fw-bold mb-0 text-primary-color"><?= $totalApps ?></h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Total Applicants</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon stat-icon-purple mx-auto mb-2"><i class="fas fa-user-check"></i></div>
                <h3 class="fw-bold mb-0 text-purple"><?= $shortlisted ?></h3>
                <div style="font-size:0.8rem;color:var(--text-muted);">Shortlisted Candidates</div>
            </div>
        </div>
    </div>

    <!-- Recent Applicants -->
    <div class="card-premium p-4" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="font-family:'Outfit',sans-serif;"><i class="fas fa-user-graduate me-2 text-primary-color"></i>Recent Applicants</h5>
            <a href="applications.php" class="btn-premium-outline" style="font-size:0.78rem;padding:4px 10px;">View All Applicants</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Candidate Name</th>
                        <th>Branch</th>
                        <th>CGPA</th>
                        <th>Job Role</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentApps as $app): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($app['student_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($app['student_email']) ?></div>
                        </td>
                        <td><?= h($app['branch']) ?></td>
                        <td><span class="fw-bold text-success"><?= h($app['cgpa']) ?></span></td>
                        <td><span class="fw-semibold"><?= h($app['job_title']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                        <td><span class="badge-premium bg-light-primary"><?= h($app['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($recentApps)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No student applications received yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>
