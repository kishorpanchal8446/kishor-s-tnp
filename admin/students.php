<?php
$pageTitle = 'Manage Students — Admin ASTPMS';
require_once 'views/header.php';
require_once 'views/sidebar.php';

// ── Handle Actions ────────────────────────────────────────
$actionMsg = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_status') {
        $id = (int)$_POST['student_id'];
        $pdo->prepare("UPDATE students SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        $actionMsg  = 'Student status updated.';
        $actionType = 'success';
    } elseif ($action === 'update_placement') {
        $id = (int)$_POST['student_id'];
        $status = sanitize($_POST['placement_status'] ?? '');
        $allowed = ['Placed','Unplaced','In Process','Higher Studies','Opted Out'];
        if (in_array($status, $allowed)) {
            $pdo->prepare("UPDATE students SET placement_status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $id]);
            logActivity($pdo, $id, 'admin', 'update_placement', "Placement status changed to $status", 'students');
            $actionMsg  = 'Placement status updated.';
            $actionType = 'success';
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['student_id'];
        $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
        logActivity($pdo, null, 'admin', 'delete_student', "Student #$id deleted", 'students');
        $actionMsg  = 'Student record deleted.';
        $actionType = 'danger';
    } elseif ($action === 'send_notification') {
        $studentId = (int)$_POST['student_id'];
        $message   = sanitize($_POST['notif_message'] ?? '');
        $title     = sanitize($_POST['notif_title']   ?? 'Admin Notification');
        if ($studentId && $message) {
            $pdo->prepare("INSERT INTO notifications (recipient_id, recipient_role, sender_role, title, message) VALUES (?,?,?,?,?)")
                ->execute([$studentId, 'student', 'admin', $title, $message]);
            $actionMsg  = 'Notification sent.';
            $actionType = 'success';
        }
    }
}

// ── Filters ───────────────────────────────────────────────
$filterBranch   = sanitize($_GET['branch']   ?? '');
$filterStatus   = sanitize($_GET['status']   ?? '');
$filterYear     = sanitize($_GET['year']     ?? '');
$filterVerified = sanitize($_GET['verified'] ?? '');

$conditions = ["is_active = 1"];
$params     = [];

if ($filterBranch)   { $conditions[] = "branch = ?";            $params[] = $filterBranch; }
if ($filterStatus)   { $conditions[] = "placement_status = ?";  $params[] = $filterStatus; }
if ($filterYear)     { $conditions[] = "passing_year = ?";      $params[] = $filterYear;   }
if ($filterVerified !== '') { $conditions[] = "email_verified = ?"; $params[] = (int)$filterVerified; }

$where = 'WHERE ' . implode(' AND ', $conditions);
$stmt  = $pdo->prepare("SELECT * FROM students $where ORDER BY created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Filter options
$branches = $pdo->query("SELECT DISTINCT branch FROM students WHERE branch IS NOT NULL ORDER BY branch")->fetchAll(PDO::FETCH_COLUMN);
$years    = $pdo->query("SELECT DISTINCT passing_year FROM students WHERE passing_year IS NOT NULL ORDER BY passing_year DESC")->fetchAll(PDO::FETCH_COLUMN);

// Summary stats
$totalCount    = count($students);
$placedCount   = count(array_filter($students, fn($s) => $s['placement_status'] === 'Placed'));
$unplacedCount = count(array_filter($students, fn($s) => $s['placement_status'] === 'Unplaced'));
$avgCgpa       = $totalCount > 0 ? round(array_sum(array_column($students, 'cgpa')) / $totalCount, 2) : 0;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
    <div>
        <h3 class="fw-bold mb-0" style="font-family:'Outfit',sans-serif;"><i class="fas fa-users text-primary-color me-2"></i>Student Management</h3>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:4px;"><?= $totalCount ?> students found</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.exportTableToCSV('studentsTable','students_<?= date('Y-m-d') ?>.csv')" class="btn-premium-outline" style="font-size:0.82rem;">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </button>
        <a href="students.php?action=import" class="btn-premium" style="font-size:0.82rem;">
            <i class="fas fa-upload me-1"></i>Import
        </a>
    </div>
</div>

<?php if ($actionMsg): ?>
<div class="alert-premium alert-premium-<?= $actionType ?> mb-4" data-aos="fade-up">
    <i class="fas fa-<?= $actionType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= h($actionMsg) ?>
</div>
<?php endif; ?>

<!-- Summary Pills -->
<div class="row g-3 mb-4">
    <?php
    $summaryCards = [
        ['val'=>$totalCount,  'label'=>'Showing', 'color'=>'blue'],
        ['val'=>$placedCount,  'label'=>'Placed',   'color'=>'green'],
        ['val'=>$unplacedCount,'label'=>'Unplaced', 'color'=>'red'],
        ['val'=>$avgCgpa,      'label'=>'Avg CGPA', 'color'=>'purple'],
    ];
    foreach ($summaryCards as $sc): ?>
    <div class="col-6 col-md-3">
        <div class="card-premium p-3 text-center" style="border-left:3px solid var(--<?= $sc['color'] === 'blue' ? 'primary-mid' : ($sc['color']==='green'?'success':($sc['color']==='red'?'danger':'purple')) ?>);">
            <div style="font-size:1.5rem;font-weight:800;color:var(--text);"><?= $sc['val'] ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);"><?= $sc['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filter Bar -->
<div class="card-premium p-3 mb-4" data-aos="fade-up">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label-premium">Branch</label>
            <select class="form-control-premium" name="branch">
                <option value="">All Branches</option>
                <?php foreach ($branches as $b): ?>
                <option value="<?= h($b) ?>" <?= $filterBranch === $b ? 'selected' : '' ?>><?= h($b) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label-premium">Placement Status</label>
            <select class="form-control-premium" name="status">
                <option value="">All</option>
                <?php foreach (['Placed','Unplaced','In Process','Higher Studies','Opted Out'] as $s): ?>
                <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label-premium">Passing Year</label>
            <select class="form-control-premium" name="year">
                <option value="">All Years</option>
                <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $filterYear === (string)$y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label-premium">Email Verified</label>
            <select class="form-control-premium" name="verified">
                <option value="">All</option>
                <option value="1" <?= $filterVerified === '1' ? 'selected' : '' ?>>Verified</option>
                <option value="0" <?= $filterVerified === '0' ? 'selected' : '' ?>>Not Verified</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-premium" style="flex:1;justify-content:center;">
                <i class="fas fa-filter me-1"></i>Filter
            </button>
            <a href="students.php" class="btn-premium-outline" style="flex-shrink:0;">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Students Table -->
<div class="card-premium" data-aos="fade-up">
    <div style="overflow-x:auto;">
        <table class="table-premium w-100" id="studentsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Branch</th>
                    <th>CGPA</th>
                    <th>Year</th>
                    <th>Verified</th>
                    <th>Placement</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $s):
                    $statusColors = ['Placed'=>'status-selected','In Process'=>'status-interview','Unplaced'=>'status-rejected','Higher Studies'=>'status-shortlisted','Opted Out'=>'status-withdrawn'];
                    $sColor = $statusColors[$s['placement_status']] ?? 'status-applied';
                    $avatar = (!empty($s['profile_pic']) && file_exists('../' . $s['profile_pic'])) ? '../' . $s['profile_pic'] : '../assets/images/default_avatar.png';
                ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:0.78rem;"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= h($avatar) ?>" alt="Avatar" class="avatar-sm" style="width:34px;height:34px;border-radius:8px;">
                            <div>
                                <div class="fw-semibold" style="font-size:0.875rem;"><?= h($s['name']) ?></div>
                                <div style="font-size:0.72rem;color:var(--text-muted);"><?= h($s['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.8rem;font-weight:600;"><?= h($s['student_id']) ?></td>
                    <td style="font-size:0.8rem;"><?= h($s['branch']) ?></td>
                    <td>
                        <span style="font-weight:700;color:<?= (float)$s['cgpa'] >= 8 ? 'var(--success)' : ((float)$s['cgpa'] >= 6 ? 'var(--warning)' : 'var(--danger)') ?>;">
                            <?= h($s['cgpa']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;"><?= h($s['passing_year']) ?></td>
                    <td>
                        <?php if (!empty($s['is_verified'])): ?>
                            <span style="color:var(--success);font-size:0.78rem;"><i class="fas fa-check-circle"></i> Yes</span>
                        <?php else: ?>
                            <span style="color:var(--danger);font-size:0.78rem;"><i class="fas fa-times-circle"></i> No</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge-premium <?= $sColor ?>" style="padding:3px 10px;font-size:0.7rem;"><?= h($s['placement_status']) ?></span></td>
                    <td style="font-size:0.75rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="student_detail.php?id=<?= $s['id'] ?>" class="btn-premium" style="padding:4px 10px;font-size:0.72rem;" title="View Profile">
                                <i class="fas fa-eye"></i>
                            </a>
                            <!-- Update Placement Status -->
                            <form method="POST" class="d-inline">
                                <?= csrfInput() ?>
                                <input type="hidden" name="action" value="update_placement">
                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                <select name="placement_status" class="form-control-premium" style="padding:3px 6px;font-size:0.72rem;height:auto;display:inline-block;width:auto;border-radius:8px;" onchange="this.form.submit()">
                                    <?php foreach (['Placed','Unplaced','In Process','Higher Studies','Opted Out'] as $ps): ?>
                                    <option value="<?= $ps ?>" <?= $s['placement_status'] === $ps ? 'selected' : '' ?>><?= $ps ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <!-- Send Notification -->
                            <button type="button" class="btn-premium btn-accent" style="padding:4px 10px;font-size:0.72rem;" title="Send Notification" data-bs-toggle="modal" data-bs-target="#notifModal" data-student-id="<?= $s['id'] ?>" data-student-name="<?= h($s['name']) ?>">
                                <i class="far fa-bell"></i>
                            </button>
                            <!-- Delete -->
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete student <?= addslashes($s['name']) ?>? This cannot be undone.')">
                                <?= csrfInput() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn-premium btn-danger" style="padding:4px 10px;font-size:0.72rem;" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                <tr><td colspan="10" class="text-center py-5" style="color:var(--text-muted);">
                    <i class="fas fa-users-slash" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem;"></i>
                    No students found matching the filters.
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade modal-premium" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="notifModalLabel"><i class="far fa-bell text-primary-color me-2"></i>Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?= csrfInput() ?>
                <input type="hidden" name="action" value="send_notification">
                <input type="hidden" name="student_id" id="notifStudentId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <div style="font-size:0.82rem;color:var(--text-muted);">Sending to: <strong id="notifStudentName" style="color:var(--text);"></strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Notification Title</label>
                        <input type="text" name="notif_title" class="form-control-premium" placeholder="e.g. Interview Scheduled" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Message</label>
                        <textarea name="notif_message" class="form-control-premium" rows="4" placeholder="Your notification message..." required style="resize:vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-premium"><i class="fas fa-paper-plane me-1"></i>Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = "
<script>
// Init DataTable
\$(document).ready(function() {
    \$('#studentsTable').DataTable({
        pageLength: 20,
        order: [[8,'desc']],
        columnDefs: [{orderable:false,targets:[9]}],
        language: { search: 'Search students:', lengthMenu: 'Show _MENU_ per page' }
    });
});

// Notification Modal
document.getElementById('notifModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('notifStudentId').value   = btn.dataset.studentId;
    document.getElementById('notifStudentName').textContent = btn.dataset.studentName;
});
</script>";
require_once 'views/footer.php';
?>
