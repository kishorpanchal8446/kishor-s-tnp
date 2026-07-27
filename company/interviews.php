<?php
/**
 * ASTPMS 2.0 — Recruiter Interview Round Scheduler
 */

$pageTitle = 'Schedule Interviews — Recruiter Portal';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$companyId = $_SESSION['company_id'];
$success   = '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_interview']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $appId     = (int)$_POST['application_id'];
    $roundType = sanitize($_POST['round_type'] ?? 'Technical Round');
    $date      = sanitize($_POST['scheduled_date'] ?? date('Y-m-d'));
    $time      = sanitize($_POST['scheduled_time'] ?? '10:00:00');
    $venue     = sanitize($_POST['venue'] ?? 'Google Meet / Online');
    $joinUrl   = sanitize($_POST['join_url'] ?? '');

    if (empty($appId) || empty($date)) {
        $error = 'Candidate and date are required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO interviews (application_id, round_type, scheduled_date, scheduled_time, venue, join_url, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Scheduled')
        ");
        $stmt->execute([$appId, $roundType, $date, $time, $venue, $joinUrl]);

        // Update application status
        $pdo->prepare("UPDATE applications SET status = 'Interview Scheduled' WHERE id = ?")->execute([$appId]);
        $success = 'Interview round scheduled successfully!';
    }
}

// Fetch shortlisted candidates
$shortlistedApps = $pdo->query("
    SELECT a.id, s.name as student_name, j.role as job_title
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = $companyId AND a.status IN ('Applied', 'Shortlisted', 'Interview Scheduled')
")->fetchAll();

// Fetch scheduled interviews
$interviews = $pdo->query("
    SELECT i.*, s.name as student_name, s.email as student_email, j.role as job_title
    FROM interviews i
    JOIN applications a ON i.application_id = a.id
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = $companyId
    ORDER BY i.scheduled_date ASC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="far fa-calendar-check text-primary-color me-2"></i>Campus Interview Scheduler
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Schedule Technical and HR interview rounds with candidate calendar notifications and video links.
            </p>
        </div>
        <button class="btn-premium" data-bs-toggle="modal" data-bs-target="#scheduleModal">
            <i class="fas fa-plus me-1"></i>Schedule New Interview
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
            <table class="table table-hover align-middle mb-0" id="interviewsTable">
                <thead class="table-light">
                    <tr>
                        <th>Candidate Name</th>
                        <th>Job Role</th>
                        <th>Interview Round</th>
                        <th>Date &amp; Time</th>
                        <th>Venue / Link</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interviews as $iv): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($iv['student_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($iv['student_email']) ?></div>
                        </td>
                        <td><span class="fw-semibold"><?= h($iv['job_title']) ?></span></td>
                        <td><span class="badge-premium bg-light-purple"><?= h($iv['round_type']) ?></span></td>
                        <td>
                            <div class="fw-bold"><?= date('M d, Y', strtotime($iv['scheduled_date'])) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= date('h:i A', strtotime($iv['scheduled_time'])) ?></div>
                        </td>
                        <td>
                            <?php if (!empty($iv['join_url'])): ?>
                                <a href="<?= h($iv['join_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.78rem;">
                                    <i class="fas fa-video me-1"></i>Join Video Call
                                </a>
                            <?php else: ?>
                                <span><?= h($iv['venue']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge-premium bg-light-success"><?= h($iv['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-premium">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2 text-primary-color"></i>Schedule Interview Round</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="interviews.php">
                <?= csrfInput() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-premium">Select Shortlisted Candidate</label>
                        <select name="application_id" class="form-control-premium" required>
                            <option value="">-- Choose Candidate --</option>
                            <?php foreach ($shortlistedApps as $sa): ?>
                            <option value="<?= $sa['id'] ?>"><?= h($sa['student_name']) ?> — (<?= h($sa['job_title']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Round Type</label>
                        <select name="round_type" class="form-control-premium">
                            <option value="Technical Round 1">Technical Round 1</option>
                            <option value="Technical Round 2">Technical Round 2</option>
                            <option value="HR Round">HR Round</option>
                            <option value="Coding Assessment">Coding Assessment</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-premium">Scheduled Date</label>
                            <input type="date" name="scheduled_date" class="form-control-premium" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label-premium">Time</label>
                            <input type="time" name="scheduled_time" class="form-control-premium" required value="10:00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Video Meeting Link (Google Meet / Zoom / Teams)</label>
                        <input type="url" name="join_url" class="form-control-premium" placeholder="https://meet.google.com/abc-defg-hij">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Venue Details (If In-Person)</label>
                        <input type="text" name="venue" class="form-control-premium" value="Online Video Conference">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="schedule_interview" class="btn-premium">Schedule Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#interviewsTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
