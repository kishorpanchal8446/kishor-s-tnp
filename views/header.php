<?php
/**
 * ASTPMS — Shared Student Header
 * Includes: session guard, DB, models, glassmorphism navbar
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Notification.php';

initSession();
requireStudentLogin();

$studentModel      = new Student($pdo);
$notificationModel = new Notification($pdo);
$student           = $studentModel->getById($_SESSION['student_id']);
$unreadCount       = $notificationModel->getUnreadCount($_SESSION['student_id']);
$latestNotifications = array_slice($notificationModel->getByStudent($_SESSION['student_id']), 0, 5);

// Avatar with fallback
$avatar = (!empty($student['profile_pic']) && file_exists(BASE_PATH . '/' . $student['profile_pic']))
    ? $student['profile_pic']
    : 'assets/images/default_avatar.png';

// CSRF token for forms in page
$csrfToken = generateCsrfToken();

// Current page for sidebar active state
$currentPage = basename($_SERVER['PHP_SELF']);

// Page title (override in each page before including header)
$pageTitle = $pageTitle ?? 'Dashboard — ASTPMS';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="ASTPMS — Training & Placement Management System Student Portal">

    <!-- Google Fonts: Inter + Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- AOS Animations -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- ASTPMS Premium CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">

    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<!-- ── Page Loader ─────────────────────────────────────────── -->
<div id="page-loader">
    <div class="loader-logo">
        <i class="fas fa-graduation-cap text-white" style="font-size:1.4rem;"></i>
    </div>
    <div class="loader-bar"></div>
    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;font-family:'Inter',sans-serif;">Loading ASTPMS...</div>
</div>

<!-- ── Sidebar Overlay (mobile) ────────────────────────────── -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ═══════════════════════════════════════════════════════════
     TOP NAVIGATION BAR
══════════════════════════════════════════════════════════════ -->
<header class="top-navbar">
    <!-- Left: Toggle + Logo -->
    <div class="d-flex align-items-center gap-3">
        <button id="sidebar-toggle" class="theme-toggle-btn" title="Toggle Sidebar" style="font-size:1.1rem;">
            <i class="fas fa-bars"></i>
        </button>
        <a href="index.php" class="d-flex align-items-center text-decoration-none gap-2">
            <div class="nav-logo-icon">
                <i class="fas fa-graduation-cap text-white" style="font-size:1.1rem;"></i>
            </div>
            <span class="logo-text d-none d-sm-inline">ASTPMS</span>
        </a>
        <div class="d-none d-lg-block" style="border-left:1px solid var(--border);padding-left:1rem;margin-left:0.25rem;font-size:0.8rem;color:var(--text-muted);font-weight:500;">
            Training &amp; Placement Portal
        </div>
    </div>

    <!-- Right: Search + Actions + Profile -->
    <div class="d-flex align-items-center gap-2">
        <!-- Global Search -->
        <div class="search-input-group d-none d-md-flex">
            <i class="fas fa-search" style="color:var(--text-muted);font-size:0.85rem;"></i>
            <input type="text" id="global-search" placeholder="Search jobs, training, companies...">
        </div>

        <!-- Search (mobile) -->
        <button class="theme-toggle-btn d-md-none" id="mobileSearchToggle" title="Search" style="font-size:0.95rem;">
            <i class="fas fa-search"></i>
        </button>

        <!-- Dark Mode Toggle -->
        <button id="theme-toggle" class="theme-toggle-btn" title="Toggle Dark Mode">
            <i class="fas fa-moon" id="theme-icon"></i>
        </button>

        <!-- Notifications -->
        <div class="dropdown">
            <button class="notif-btn" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                <span class="notif-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-premium dropdown-menu-end p-0" aria-labelledby="notifDropdown" style="width:340px;border-radius:var(--radius-lg);overflow:hidden;">
                <!-- Header -->
                <div style="background:linear-gradient(135deg,var(--primary),var(--primary-mid));padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:#fff;font-weight:700;font-size:0.9rem;"><i class="fas fa-bell me-2"></i>Notifications</span>
                    <span style="background:rgba(255,255,255,0.2);color:#fff;padding:3px 8px;border-radius:10px;font-size:0.72rem;font-weight:600;"><?= $unreadCount ?> New</span>
                </div>
                <!-- List -->
                <div style="max-height:320px;overflow-y:auto;">
                    <?php if (!empty($latestNotifications)): ?>
                        <?php foreach ($latestNotifications as $notif):
                            $nIcon = match ($notif['type']) {
                                'Job Alert'         => 'fas fa-briefcase text-warning',
                                'Interview Update'  => 'fas fa-calendar-alt text-danger',
                                'Training'          => 'fas fa-graduation-cap text-success',
                                'Result'            => 'fas fa-poll text-info',
                                'Announcement'      => 'fas fa-bullhorn text-purple',
                                default             => 'fas fa-info-circle',
                            };
                            $unreadClass = $notif['is_read'] == 0 ? 'fw-medium' : '';
                        ?>
                        <div class="p-3 border-bottom <?= $unreadClass ?>" style="border-color:var(--border)!important;font-size:0.82rem;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''" onclick="markNotifRead(<?= $notif['id'] ?>)">
                            <div class="d-flex gap-3">
                                <i class="<?= $nIcon ?> mt-1" style="font-size:1rem;width:18px;flex-shrink:0;"></i>
                                <div>
                                    <div style="color:var(--text);font-weight:<?= $notif['is_read'] ? '500' : '700' ?>;"><?= htmlspecialchars($notif['title']) ?></div>
                                    <div style="color:var(--text-muted);margin-top:2px;font-size:0.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;"><?= htmlspecialchars($notif['message']) ?></div>
                                    <div style="color:var(--text-muted);margin-top:4px;font-size:0.7rem;opacity:0.7;"><i class="fas fa-clock me-1"></i><?= date('M d, g:i A', strtotime($notif['created_at'])) ?></div>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                <div style="width:8px;height:8px;background:var(--primary-mid);border-radius:50%;flex-shrink:0;margin-top:4px;"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center p-4" style="color:var(--text-muted);">
                            <i class="fas fa-bell-slash" style="font-size:2rem;opacity:0.3;"></i>
                            <p style="margin-top:0.5rem;font-size:0.82rem;">No notifications yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Footer -->
                <div class="text-center p-2" style="border-top:1px solid var(--border);">
                    <a href="notifications.php" style="color:var(--primary-mid);font-weight:600;font-size:0.8rem;text-decoration:none;">
                        View All Notifications <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-sm">
                <div class="d-none d-lg-block text-start" style="line-height:1.2;">
                    <div style="font-size:0.82rem;font-weight:700;color:var(--text);white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($student['name'] ?? 'Student') ?></div>
                    <div style="font-size:0.7rem;color:var(--text-muted);"><?= htmlspecialchars($student['branch'] ?? 'Student') ?></div>
                </div>
                <i class="fas fa-chevron-down d-none d-lg-inline" style="font-size:0.7rem;color:var(--text-muted);"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-premium dropdown-menu-end" aria-labelledby="profileDropdown">
                <li>
                    <div style="padding:10px 14px;border-bottom:1px solid var(--border);margin-bottom:4px;">
                        <div style="font-size:0.75rem;color:var(--text-muted);">Signed in as</div>
                        <div style="font-size:0.82rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($student['email'] ?? '') ?></div>
                    </div>
                </li>
                <li><a class="dropdown-item" href="profile.php"><i class="far fa-user text-primary-color"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="resume.php"><i class="far fa-file-alt text-primary-color"></i>My Resume</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog text-muted"></i>Settings</a></li>
                <li class="dropdown-divider-premium"></li>
                <li><a class="dropdown-item danger" href="logout.php"><i class="fas fa-sign-out-alt"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>
