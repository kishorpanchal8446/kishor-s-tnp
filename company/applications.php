<?php
/**
 * ASTPMS 2.0 — Recruiter Applicant Reviewer & Resume Downloader
 */

$pageTitle = 'Review Applicants — Recruiter Portal';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$companyId = $_SESSION['company_id'];
$success   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $appId     = (int)$_POST['application_id'];
    $newStatus = sanitize($_POST['status']);

    $stmt = $pdo->prepare("
        UPDATE applications a
        JOIN jobs j ON a.job_id = j.id
        SET a.status = ?
        WHERE a.id = ? AND j.company_id = ?
    ");
    $stmt->execute([$newStatus, $appId, $companyId]);
    $success = 'Candidate application status updated to ' . $newStatus;
}

$applicants = $pdo->query("
    SELECT a.*, s.name as student_name, s.email as student_email, s.cgpa, s.branch, s.phone,
           j.role as job_title, d.file_path as resume_path, d.original_name as resume_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    LEFT JOIN student_documents d ON s.id = d.student_id AND d.doc_type = 'resume'
    WHERE j.company_id = $companyId
    ORDER BY a.applied_at DESC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-users-viewfinder text-primary-color me-2"></i>Student Applicants &amp; Resumes
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Review student profile scores, download PDF resumes, and shortlist candidates for interview rounds.
            </p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert-premium alert-premium-success mb-4"><i class="fas fa-check-circle me-2"></i><?= h($success) ?></div>
    <?php endif; ?>

    <div class="card-premium p-4" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="applicantsTable">
                <thead class="table-light">
                    <tr>
                        <th>Candidate Name</th>
                        <th>Branch</th>
                        <th>CGPA</th>
                        <th>Applied For</th>
                        <th>Resume Document</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($app['student_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($app['student_email']) ?> · <?= h($app['phone'] ?? 'No Phone') ?></div>
                        </td>
                        <td><?= h($app['branch']) ?></td>
                        <td><span class="fw-bold text-success"><?= h($app['cgpa']) ?> / 10.0</span></td>
                        <td><span class="fw-semibold"><?= h($app['job_title']) ?></span></td>
                        <td>
                            <?php if (!empty($app['resume_path']) && file_exists(BASE_PATH . '/' . $app['resume_path'])): ?>
                                <a href="../<?= h($app['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline-danger" style="font-size:0.78rem;">
                                    <i class="far fa-file-pdf me-1"></i>Download PDF
                                </a>
                            <?php else: ?>
                                <span style="font-size:0.78rem;color:var(--text-muted);">No Resume Uploaded</span>
                            <?php endif; ?>
                        </td>
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
    $('#applicantsTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
