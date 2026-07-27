<?php
/**
 * ASTPMS 2.0 — Recruiter Job Drive Posting Manager
 */

$pageTitle = 'Manage Job Drives — Recruiter Portal';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$companyId = $_SESSION['company_id'];
$success   = '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_job']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $role        = sanitize($_POST['role'] ?? '');
    $minCgpa     = (float)($_POST['min_cgpa'] ?? 6.0);
    $packageLpa  = (float)($_POST['package_lpa'] ?? 5.0);
    $location    = sanitize($_POST['location'] ?? 'Pune');
    $jobType     = sanitize($_POST['job_type'] ?? 'Full Time');
    $deadline    = sanitize($_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days')));
    $branches    = isset($_POST['branches']) ? implode(',', array_map('sanitize', $_POST['branches'])) : 'Computer Science,Information Technology';
    $description = sanitize($_POST['description'] ?? '');

    if (empty($role) || empty($deadline)) {
        $error = 'Job Role and Deadline are required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO jobs (company_id, role, min_cgpa, eligible_branches, location, package_lpa, job_type, deadline, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open')
        ");
        $stmt->execute([$companyId, $role, $minCgpa, $branches, $location, $packageLpa, $jobType, $deadline, $description]);
        $success = 'Campus recruitment drive posted successfully!';
    }
}

$jobs = $pdo->query("
    SELECT j.*, COUNT(a.id) as app_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.company_id = $companyId
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
                Post new recruitment drives, specify min CGPA and eligible branches, and manage open deadlines.
            </p>
        </div>
        <button class="btn-premium" data-bs-toggle="modal" data-bs-target="#newJobModal">
            <i class="fas fa-plus me-1"></i>Post New Job Drive
        </button>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert-premium alert-premium-success mb-4"><i class="fas fa-check-circle me-2"></i><?= h($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    <div class="alert-premium alert-premium-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?></div>
    <?php endif; ?>

    <div class="card-premium p-4" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="companyJobsTable">
                <thead class="table-light">
                    <tr>
                        <th>Job Role</th>
                        <th>Location &amp; Type</th>
                        <th>Package (LPA)</th>
                        <th>Min CGPA</th>
                        <th>Eligible Branches</th>
                        <th>Applicants</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $j): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($j['role']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h(substr($j['description'] ?? '', 0, 50)) ?>...</div>
                        </td>
                        <td><?= h($j['location']) ?> <br><small class="text-muted"><?= h($j['job_type']) ?></small></td>
                        <td><span class="fw-bold text-success">₹<?= h($j['package_lpa']) ?> LPA</span></td>
                        <td><span class="fw-bold"><?= h($j['min_cgpa']) ?></span></td>
                        <td><span class="badge-premium bg-light-cyan"><?= (int)$j['app_count'] ?> Applicants</span></td>
                        <td><?= date('M d, Y', strtotime($j['deadline'])) ?></td>
                        <td><span class="badge-premium bg-light-success"><?= h($j['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newJobModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content card-premium">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary-color"></i>Post New Job Drive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="jobs.php">
                <?= csrfInput() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-premium">Job Role / Designation</label>
                            <input type="text" name="role" class="form-control-premium" required placeholder="e.g. Software Engineer / Developer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Package (CTC in LPA)</label>
                            <input type="number" step="0.1" name="package_lpa" class="form-control-premium" required placeholder="e.g. 7.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-premium">Minimum CGPA Required</label>
                            <input type="number" step="0.1" min="0" max="10" name="min_cgpa" class="form-control-premium" value="6.5" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-premium">Location</label>
                            <input type="text" name="location" class="form-control-premium" value="Pune" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-premium">Job Type</label>
                            <select name="job_type" class="form-control-premium">
                                <option value="Full Time">Full Time</option>
                                <option value="Internship">Internship</option>
                                <option value="Internship + Full Time">Internship + Full Time</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control-premium" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Eligible Branches</label>
                            <div class="d-flex flex-wrap gap-2">
                                <label><input type="checkbox" name="branches[]" value="Computer Science" checked> CS</label>
                                <label><input type="checkbox" name="branches[]" value="Information Technology" checked> IT</label>
                                <label><input type="checkbox" name="branches[]" value="Electronics & Communication" checked> ENTC</label>
                                <label><input type="checkbox" name="branches[]" value="Electrical"> Electrical</label>
                                <label><input type="checkbox" name="branches[]" value="Mechanical"> Mechanical</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label-premium">Job Description &amp; Responsibilities</label>
                            <textarea name="description" class="form-control-premium" rows="4" required placeholder="Describe technical requirements..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_job" class="btn-premium">Post Job Drive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#companyJobsTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
