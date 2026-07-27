<?php
/**
 * ASTPMS — Student Sidebar Navigation
 * Responsive collapsible sidebar with active state detection
 */

// Profile completion
$completionPct = $studentModel->getProfileCompletionPercentage($_SESSION['student_id']);

// Sidebar nav items
$navItems = [
    ['icon' => 'fas fa-home',           'text' => 'Dashboard',          'href' => 'index.php',          'page' => 'index.php'],
    ['icon' => 'far fa-user',           'text' => 'My Profile',         'href' => 'profile.php',        'page' => 'profile.php'],
    ['icon' => 'far fa-file-alt',       'text' => 'Resume',             'href' => 'resume.php',         'page' => 'resume.php'],
    ['icon' => 'fas fa-briefcase',      'text' => 'Placement Jobs',     'href' => 'jobs.php',           'page' => 'jobs.php'],
    ['icon' => 'fas fa-clipboard-list', 'text' => 'My Applications',    'href' => 'applications.php',   'page' => 'applications.php'],
    ['icon' => 'far fa-calendar-alt',   'text' => 'Interviews',         'href' => 'interviews.php',     'page' => 'interviews.php'],
    ['icon' => 'fas fa-graduation-cap', 'text' => 'Training Programs',  'href' => 'training.php',       'page' => 'training.php'],
    ['icon' => 'fas fa-university',     'text' => 'Higher Studies',     'href' => 'higher_studies.php', 'page' => 'higher_studies.php'],
    ['icon' => 'far fa-bell',           'text' => 'Notifications',      'href' => 'notifications.php',  'page' => 'notifications.php', 'badge' => $unreadCount > 0 ? $unreadCount : null],
    ['icon' => 'fas fa-chart-bar',      'text' => 'My Reports',         'href' => 'reports.php',        'page' => 'reports.php'],
    ['icon' => 'fas fa-cog',            'text' => 'Settings',           'href' => 'settings.php',       'page' => 'settings.php'],
];
?>
<!-- ═══════════════════════════════════════════════════════════
     LEFT SIDEBAR
══════════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">

        <!-- User Card -->
        <div class="sidebar-user">
            <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:2px solid rgba(255,255,255,0.3);flex-shrink:0;">
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($student['name'] ?? 'Student') ?></div>
                <div class="sidebar-user-role">
                    <i class="fas fa-circle" style="font-size:0.4rem;vertical-align:middle;color:#4ADE80;"></i>
                    Online · Student
                </div>
            </div>
        </div>

        <!-- Profile Completion Bar -->
        <div class="mb-3" style="padding:0 0.25rem;" id="completionBar">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-size:0.68rem;color:var(--text-muted);font-weight:600;">Profile Completion</span>
                <span style="font-size:0.68rem;color:var(--primary-mid);font-weight:700;"><?= $completionPct ?>%</span>
            </div>
            <div style="height:4px;background:var(--border);border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:<?= $completionPct ?>%;background:linear-gradient(90deg,var(--primary-mid),var(--accent));border-radius:4px;transition:width 1s ease;"></div>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="sidebar-section-title">Navigation</div>
        <ul class="sidebar-menu">
            <?php foreach ($navItems as $item): ?>
            <li class="sidebar-menu-item <?= $currentPage === $item['page'] ? 'active' : '' ?>">
                <a href="<?= htmlspecialchars($item['href']) ?>" class="sidebar-link" title="<?= htmlspecialchars($item['text']) ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span class="sidebar-text"><?= htmlspecialchars($item['text']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                    <span class="link-badge"><?= (int)$item['badge'] > 9 ? '9+' : (int)$item['badge'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Other Links -->
        <div class="sidebar-section-title" style="margin-top:1.5rem;">Information</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item <?= $currentPage === 'contact.php' ? 'active' : '' ?>">
                <a href="contact.php" class="sidebar-link" title="Contact">
                    <i class="fas fa-envelope"></i>
                    <span class="sidebar-text">Contact T&amp;P Cell</span>
                </a>
            </li>
            <li class="sidebar-menu-item <?= $currentPage === 'about.php' ? 'active' : '' ?>">
                <a href="about.php" class="sidebar-link" title="About">
                    <i class="fas fa-info-circle"></i>
                    <span class="sidebar-text">About ASTPMS</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Logout Footer -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout" title="Sign Out">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-logout-text">Sign Out</span>
        </a>
    </div>
</aside>

<!-- ═══════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER BEGINS HERE
══════════════════════════════════════════════════════════════ -->
<div class="main-content" id="mainContent">
