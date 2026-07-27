<?php
/**
 * ASTPMS 2.0 — Public Navigation Header (Sticky, Glassmorphic, Shrink on Scroll)
 * Includes all requested navigation items and controls
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle   = $pageTitle ?? 'ASTPMS — Training & Placement Management System';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="Advanced Student Training & Placement Management System (ASTPMS) — Official University Career & Recruitment Portal.">

    <!-- Google Fonts: Poppins + Inter + Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800;900&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- AOS Animations -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <!-- ASTPMS Premium CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">

    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<!-- ── Page Loader ─────────────────────────────────────────── -->
<div id="page-loader">
    <div class="loader-logo">
        <i class="fas fa-graduation-cap text-white" style="font-size:1.5rem;"></i>
    </div>
    <div class="loader-bar"></div>
    <div style="font-size:0.75rem;color:var(--text-muted);font-family:'Poppins',sans-serif;">Loading ASTPMS Enterprise Portal...</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     STICKY GLASS HEADER / NAVBAR
══════════════════════════════════════════════════════════════ -->
<header class="sticky-header" id="mainHeader">
    <div class="container-fluid px-3 px-lg-4">
        <div class="d-flex align-items-center justify-content-between">

            <!-- Left: Logo & College Name -->
            <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="nav-logo-icon">
                    <i class="fas fa-graduation-cap text-white" style="font-size:1.25rem;"></i>
                </div>
                <div class="lh-1">
                    <div class="brand-text-title">ASTPMS</div>
                    <div class="brand-text-sub">TRAINING &amp; PLACEMENT CELL</div>
                </div>
            </a>

            <!-- Center Navigation Menu (Desktop) -->
            <nav class="d-none d-xl-flex align-items-center gap-1">
                <a href="index.php"          class="header-nav-link <?= $currentPage === 'index.php' || $currentPage === '' ? 'active' : '' ?>"><i class="fas fa-home"></i>Home</a>
                <a href="about.php"          class="header-nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>"><i class="fas fa-info-circle"></i>About</a>
                <a href="placements.php"     class="header-nav-link <?= $currentPage === 'placements.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i>Placements</a>
                <a href="companies.php"      class="header-nav-link <?= $currentPage === 'companies.php' ? 'active' : '' ?>"><i class="fas fa-building"></i>Companies</a>
                <a href="training.php"       class="header-nav-link <?= $currentPage === 'training.php' ? 'active' : '' ?>"><i class="fas fa-graduation-cap"></i>Training</a>
                <a href="higher_studies.php" class="header-nav-link <?= $currentPage === 'higher_studies.php' ? 'active' : '' ?>"><i class="fas fa-university"></i>Higher Studies</a>
                <a href="jobs.php"           class="header-nav-link <?= $currentPage === 'jobs.php' ? 'active' : '' ?>"><i class="fas fa-briefcase"></i>Jobs</a>
                <a href="contact.php"        class="header-nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>"><i class="fas fa-envelope"></i>Contact</a>
                <a href="help.php"           class="header-nav-link <?= $currentPage === 'help.php' ? 'active' : '' ?>"><i class="fas fa-question-circle"></i>Help</a>
                <a href="faq.php"            class="header-nav-link <?= $currentPage === 'faq.php' ? 'active' : '' ?>"><i class="fas fa-comments"></i>FAQ</a>
            </nav>

            <!-- Right Controls: Search + Dark Mode + Auth -->
            <div class="d-flex align-items-center gap-2">
                <!-- Search Input -->
                <div class="search-input-group d-none d-md-flex" style="width:200px;">
                    <i class="fas fa-search" style="color:var(--text-muted);font-size:0.85rem;"></i>
                    <input type="text" id="globalSearchInput" placeholder="Search portal...">
                </div>

                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" class="theme-toggle-btn" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>

                <!-- Auth Buttons -->
                <?php if (!empty($_SESSION['student_id'])): ?>
                    <a href="index.php" class="btn-premium" style="padding:6px 14px;font-size:0.8rem;">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-premium-outline" style="border-color:#38BDF8;color:#38BDF8 !important;padding:6px 14px;font-size:0.8rem;">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <a href="register.php" class="btn-premium" style="padding:6px 16px;font-size:0.8rem;background:linear-gradient(135deg, #06B6D4, #2563EB);">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button class="theme-toggle-btn d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

        </div>
    </div>
</header>

<!-- Mobile Navigation Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" style="background:var(--primary);color:#fff;">
    <div class="offcanvas-header border-bottom border-secondary">
        <div class="d-flex align-items-center gap-2">
            <div class="nav-logo-icon"><i class="fas fa-graduation-cap text-white"></i></div>
            <span class="brand-text-title">ASTPMS</span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex flex-column gap-2">
            <a href="index.php"          class="header-nav-link"><i class="fas fa-home me-2"></i>Home</a>
            <a href="about.php"          class="header-nav-link"><i class="fas fa-info-circle me-2"></i>About</a>
            <a href="placements.php"     class="header-nav-link"><i class="fas fa-chart-line me-2"></i>Placements</a>
            <a href="companies.php"      class="header-nav-link"><i class="fas fa-building me-2"></i>Companies</a>
            <a href="training.php"       class="header-nav-link"><i class="fas fa-graduation-cap me-2"></i>Training</a>
            <a href="higher_studies.php" class="header-nav-link"><i class="fas fa-university me-2"></i>Higher Studies</a>
            <a href="jobs.php"           class="header-nav-link"><i class="fas fa-briefcase me-2"></i>Jobs</a>
            <a href="contact.php"        class="header-nav-link"><i class="fas fa-envelope me-2"></i>Contact</a>
            <a href="help.php"           class="header-nav-link"><i class="fas fa-question-circle me-2"></i>Help</a>
            <a href="faq.php"            class="header-nav-link"><i class="fas fa-comments me-2"></i>FAQ</a>
            <hr class="border-secondary">
            <a href="login.php"          class="btn-premium-outline w-100 text-center justify-content-center mb-2"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
            <a href="register.php"       class="btn-premium w-100 text-center justify-content-center"><i class="fas fa-user-plus me-1"></i>Register</a>
        </div>
    </div>
</div>
