<?php
/**
 * ASTPMS 2.0 — Admin Company Partner Management
 */

$pageTitle = 'Manage Companies — Admin Panel';
require_once 'views/header.php';
require_once 'views/sidebar.php';

$error = '';
$success = '';

// Toggle active status
if (isset($_GET['toggle']) && validateCsrfToken($_GET['csrf'] ?? '')) {
    $companyId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE companies SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$companyId]);
    $success = 'Company status updated successfully.';
}

$companies = $pdo->query("
    SELECT c.*, COUNT(j.id) as job_count
    FROM companies c
    LEFT JOIN jobs j ON c.id = j.company_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
")->fetchAll();
?>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h3 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-building text-primary-color me-2"></i>Recruiting Partner Companies
            </h3>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0;">
                Manage corporate partner accounts, view active job postings, and control verification status.
            </p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert-premium alert-premium-success mb-4"><i class="fas fa-check-circle me-2"></i><?= h($success) ?></div>
    <?php endif; ?>

    <div class="card-premium p-4" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="companiesTable">
                <thead class="table-light">
                    <tr>
                        <th>Company Name</th>
                        <th>HR Work Email</th>
                        <th>Phone</th>
                        <th>Industry Sector</th>
                        <th>Active Drives</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= h($c['name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= h($c['city'] ?? 'Pune') ?></div>
                        </td>
                        <td><?= h($c['email']) ?></td>
                        <td><?= h($c['phone'] ?? 'N/A') ?></td>
                        <td><span class="badge-premium bg-light-primary" style="font-size:0.75rem;"><?= h($c['industry'] ?? 'Technology') ?></span></td>
                        <td><span class="fw-bold"><?= (int)$c['job_count'] ?> Drives</span></td>
                        <td>
                            <?php if ($c['is_active']): ?>
                                <span class="badge-premium bg-light-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                            <?php else: ?>
                                <span class="badge-premium bg-light-danger"><i class="fas fa-times-circle me-1"></i>Pending / Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="companies.php?toggle=<?= $c['id'] ?>&csrf=<?= generateCsrfToken() ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Toggle status for this company?');">
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
    $('#companiesTable').DataTable({ pageLength: 10, responsive: true });
});
</script>

<?php require_once 'views/footer.php'; ?>
