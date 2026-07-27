<?php
/**
 * ASTPMS — Admin Views Header
 */

require_once dirname(__DIR__) . '/../config/app.php';
require_once dirname(__DIR__) . '/../config/auth.php';
require_once dirname(__DIR__) . '/../config/db.php';

initSession();
requireAdminLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle   = $pageTitle ?? 'Admin Panel — ASTPMS';
$csrfToken   = generateCsrfToken();

// Quick stats for header
$totalStudents  = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active=1")->fetchColumn();
$placedStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE placement_status='Placed'")->fetchColumn();
$pendingMsgs    = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$adminName      = $_SESSION['admin_name'] ?? 'Admin';
$adminRole      = $_SESSION['admin_role'] ?? 'admin';
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<div id="page-loader">
    <div class="loader-logo" style="background:linear-gradient(135deg,#1E3A8A,#7C3AED);">
        <i class="fas fa-shield-alt text-white" style="font-size:1.4rem;"></i>
    </div>
    <div class="loader-bar"></div>
    <div style="font-size:0.75rem;color:var(--text-muted);font-family:'Inter',sans-serif;">Loading Admin Panel...</div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Top Navbar -->
<header class="top-navbar" style="background:linear-gradient(135deg,#0F172A,#1E293B);border-bottom:1px solid rgba(255,255,255,0.06);">
    <div class="d-flex align-items-center gap-3">
        <button id="sidebar-toggle" class="theme-toggle-btn" style="background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1);color:#fff;">
            <i class="fas fa-bars"></i>
        </button>
        <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
            <div class="nav-logo-icon" style="background:linear-gradient(135deg,#7C3AED,#2563EB);">
                <i class="fas fa-shield-alt text-white" style="font-size:1rem;"></i>
            </div>
            <div>
                <div style="font-family:'Outfit',sans-serif;font-weight:800;color:#fff;font-size:1rem;line-height:1.1;">ASTPMS</div>
                <div style="font-size:0.65rem;color:rgba(255,255,255,0.5);font-weight:600;letter-spacing:0.5px;">ADMIN PANEL</div>
            </div>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Quick Stats Pills -->
        <div class="d-none d-xl-flex gap-2">
            <span style="background:rgba(37,99,235,0.2);color:#93C5FD;padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:600;">
                <i class="fas fa-users me-1"></i><?= $totalStudents ?> Students
            </span>
            <span style="background:rgba(22,163,74,0.2);color:#86EFAC;padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:600;">
                <i class="fas fa-check-circle me-1"></i><?= $placedStudents ?> Placed
            </span>
        </div>

        <!-- View Site -->
        <a href="../index.php" target="_blank" class="theme-toggle-btn text-decoration-none" style="background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);" title="View Student Portal">
            <i class="fas fa-external-link-alt" style="font-size:0.85rem;"></i>
        </a>

        <!-- Dark Mode -->
        <button id="theme-toggle" class="theme-toggle-btn" style="background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);">
            <i class="fas fa-moon" id="theme-icon"></i>
        </button>

        <!-- Contact Messages Bell -->
        <?php if ($pendingMsgs > 0): ?>
        <a href="contact_messages.php" class="notif-btn text-decoration-none" style="background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);" title="<?= $pendingMsgs ?> unread messages">
            <i class="fas fa-envelope"></i>
            <span class="notif-badge"><?= $pendingMsgs ?></span>
        </a>
        <?php endif; ?>

        <!-- Admin Profile -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7C3AED,#2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.8rem;">
                    <?= strtoupper(substr($adminName, 0, 2)) ?>
                </div>
                <div class="d-none d-md-block" style="line-height:1.2;">
                    <div style="font-size:0.82rem;font-weight:700;color:#fff;"><?= htmlspecialchars($adminName) ?></div>
                    <div style="font-size:0.65rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.5px;"><?= ucfirst(str_replace('_',' ',$adminRole)) ?></div>
                </div>
                <i class="fas fa-chevron-down d-none d-md-inline" style="font-size:0.65rem;color:rgba(255,255,255,0.4);"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-premium dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="far fa-user text-primary-color"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog text-muted"></i>Settings</a></li>
                <li class="dropdown-divider-premium"></li>
                <li><a class="dropdown-item danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>
