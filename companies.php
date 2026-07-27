<?php
/**
 * ASTPMS 2.0 — Companies / Recruiting Partners Showcase
 */

require_once 'config/app.php';
require_once 'config/auth.php';
require_once 'config/db.php';
require_once 'models/Company.php';

initSession();

$companyModel = new Company($pdo);
$companies    = $companyModel->getAllActive();

$pageTitle = 'Recruiting Partners — ASTPMS';
$isLoggedIn = isStudentLoggedIn();

if (!$isLoggedIn) {
    require_once 'views/public_header.php';
} else {
    require_once 'views/header.php';
    require_once 'views/sidebar.php';
}
?>

<section class="py-5 bg-section-page text-center" style="margin-top:var(--header-height);">
    <div class="container" data-aos="fade-up">
        <span class="badge-premium bg-light-primary mb-2"><i class="fas fa-building me-1"></i>Corporate Network</span>
        <h1 class="fw-extrabold mb-2" style="font-family:'Outfit',sans-serif;color:var(--primary);">Our Recruiting Partners</h1>
        <p style="color:var(--text-muted);max-width:650px;margin:0 auto;" class="lead">
            Top global IT corporations, consulting firms, and product engineering leaders visiting our campus.
        </p>
    </div>
</section>

<section class="py-5 bg-main-page">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($companies as $c): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card-premium p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:52px;height:52px;border-radius:14px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--primary-mid);font-size:1.3rem;flex-shrink:0;">
                                <?= strtoupper(substr($c['name'], 0, 2)) ?>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;"><?= h($c['name']) ?></h5>
                                <div style="font-size:0.78rem;color:var(--accent);font-weight:700;">Verified Partner</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3" style="font-size:0.8rem;color:var(--text-muted);">
                            <span><i class="fas fa-industry me-1 text-primary-color"></i><?= h($c['industry'] ?? 'Technology') ?></span>
                            <span><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= h($c['city'] ?? 'India') ?></span>
                        </div>

                        <p style="font-size:0.85rem;color:var(--text-muted);line-height:1.5;margin-bottom:1rem;">
                            <?= h(substr($c['description'] ?? 'Leading multinational corporation hiring graduates.', 0, 140)) ?>...
                        </p>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center" style="border-color:var(--border-light) !important;">
                        <?php if (!empty($c['website'])): ?>
                            <a href="<?= h($c['website']) ?>" target="_blank" style="font-size:0.8rem;color:var(--primary-mid);text-decoration:none;font-weight:600;">
                                <i class="fas fa-external-link-alt me-1"></i>Visit Website
                            </a>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <a href="jobs.php" class="btn-premium" style="padding:4px 12px;font-size:0.75rem;">View Drives</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'views/footer.php'; ?>
