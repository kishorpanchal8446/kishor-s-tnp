<?php
/**
 * ASTPMS 2.0 — Admin Training Workshops Management
 */

$pageTitle = 'Manage Training Programs — Admin Panel';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_training']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $name        = sanitize($_POST['name'] ?? '');
    $trainer     = sanitize($_POST['trainer'] ?? '');
    $startDate   = sanitize($_POST['start_date'] ?? '');
    $endDate     = sanitize($_POST['end_date'] ?? '');
    $venue       = sanitize($_POST['venue'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($name) || empty($trainer) || empty($startDate) || empty($endDate)) {
        $error = 'Please fill all required fields.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO training_programs (name, trainer, start_date, end_date, venue, description, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Upcoming')
        ");
        $stmt->execute([$name, $trainer, $startDate, $endDate, $venue, $description]);
        $success = 'Training workshop created successfully!';
    }
}

$trainings = $pdo->query("
    SELECT t.*, COUNT(tr.id) as reg_count
    FROM training_programs t
    LEFT JOIN training_registrations tr ON t.id = tr.training_id
    GROUP BY t.id
    ORDER BY t.start_date DESC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-graduation-cap text-primary-color me-2"></i>Skill Training Programs &amp; Workshops
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Create technical workshops, assign trainers, and track student attendance.
            </p>
        </div>
        <button class="btn-premium" data-bs-toggle="modal" data-bs-target="#newTrainingModal">
            <i class="fas fa-plus me-1"></i>Create New Workshop
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
            <table class="table table-hover align-middle mb-0" id="trainingTable">
                <thead class="table-light">
                    <tr>
                        <th>Workshop Name</th>
                        <th>Trainer</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Venue</th>
                        <th>Registrations</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trainings as $tr): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($tr['name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h(substr($tr['description'] ?? '', 0, 60)) ?>...</div>
                        </td>
                        <td><span class="fw-semibold" style="color:var(--primary-mid);"><?= h($tr['trainer']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($tr['start_date'])) ?></td>
                        <td><?= date('M d, Y', strtotime($tr['end_date'])) ?></td>
                        <td><?= h($tr['venue']) ?></td>
                        <td><span class="badge-premium bg-light-cyan"><?= (int)$tr['reg_count'] ?> Enrolled</span></td>
                        <td><span class="badge-premium bg-light-success"><?= h($tr['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newTrainingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-premium">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary-color"></i>New Training Workshop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="training.php">
                <?= csrfInput() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-premium">Workshop Name</label>
                        <input type="text" name="name" class="form-control-premium" required placeholder="e.g. Advanced Java & Microservices Bootcamp">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Trainer Name</label>
                        <input type="text" name="trainer" class="form-control-premium" required placeholder="e.g. Dr. A. P. Joshi">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-premium">Start Date</label>
                            <input type="date" name="start_date" class="form-control-premium" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label-premium">End Date</label>
                            <input type="date" name="end_date" class="form-control-premium" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Venue</label>
                        <input type="text" name="venue" class="form-control-premium" value="Seminar Hall B">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-premium">Description</label>
                        <textarea name="description" class="form-control-premium" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-premium-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_training" class="btn-premium">Create Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#trainingTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
