<?php
/**
 * ASTPMS — Company Header
 */

require_once dirname(__DIR__) . '/../config/app.php';
require_once dirname(__DIR__) . '/../config/auth.php';
require_once dirname(__DIR__) . '/../config/db.php';

initSession();
requireCompanyLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle   = $pageTitle ?? 'Company Portal — ASTPMS';
$csrfToken   = generateCsrfToken();
$companyName = $_SESSION['company_name'] ?? 'Company';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<div id="page-loader">
    <div class="loader-logo" style="background:linear-gradient(135deg,#0891B2,#06B6D4);">
        <i class="fas fa-building text-white" style="font-size:1.4rem;"></i>
    </div>
    <div class="loader-bar"></div>
    <div style="font-size:0.75rem;color:var(--text-muted);font-family:'Inter',sans-serif;">Loading Recruiter Portal...</div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Top Navbar -->
<header class="top-navbar">
    <div class="d-flex align-items-center gap-3">
        <button id="sidebar-toggle" class="theme-toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
        <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
            <div class="nav-logo-icon" style="background:linear-gradient(135deg,#0891B2,#06B6D4);">
                <i class="fas fa-building text-white" style="font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-family:'Outfit',sans-serif;font-weight:800;color:var(--text);font-size:1rem;line-height:1.1;">ASTPMS</div>
                <div style="font-size:0.65rem;color:var(--accent);font-weight:700;letter-spacing:0.5px;">RECRUITER PORTAL</div>
            </div>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button id="theme-toggle" class="theme-toggle-btn">
            <i class="fas fa-moon" id="theme-icon"></i>
        </button>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0891B2,#06B6D4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.8rem;">
                    <?= strtoupper(substr($companyName, 0, 2)) ?>
                </div>
                <div class="d-none d-md-block text-start" style="line-height:1.2;">
                    <div style="font-size:0.82rem;font-weight:700;color:var(--text);"><?= htmlspecialchars($companyName) ?></div>
                    <div style="font-size:0.68rem;color:var(--accent);font-weight:600;">Verified Recruiter</div>
                </div>
                <i class="fas fa-chevron-down d-none d-md-inline" style="font-size:0.65rem;color:var(--text-muted);"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-premium dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="far fa-building text-primary-color"></i>Company Profile</a></li>
                <li class="dropdown-divider-premium"></li>
                <li><a class="dropdown-item danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>
