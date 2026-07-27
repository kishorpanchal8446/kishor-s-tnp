<?php
/**
 * ASTPMS — Admin Sidebar Navigation
 */
// Dynamic notification / message badges
$pendingMsgCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$openJobsCount   = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='Open'")->fetchColumn();

$adminNavItems = [
    ['section' => 'Overview'],
    ['icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard',         'href' => 'index.php',           'page' => 'index.php'],
    ['icon' => 'fas fa-chart-bar',      'text' => 'Analytics',         'href' => 'analytics.php',       'page' => 'analytics.php'],
    ['section' => 'Management'],
    ['icon' => 'fas fa-users',          'text' => 'Students',          'href' => 'students.php',        'page' => 'students.php'],
    ['icon' => 'fas fa-building',       'text' => 'Companies',         'href' => 'companies.php',       'page' => 'companies.php'],
    ['icon' => 'fas fa-briefcase',      'text' => 'Jobs',              'href' => 'jobs.php',            'page' => 'jobs.php', 'badge' => $openJobsCount > 0 ? $openJobsCount : null, 'badgeColor' => 'bg-primary'],
    ['icon' => 'fas fa-file-signature', 'text' => 'Applications',      'href' => 'applications.php',    'page' => 'applications.php'],
    ['section' => 'Programs'],
    ['icon' => 'fas fa-graduation-cap', 'text' => 'Training Programs', 'href' => 'training.php',        'page' => 'training.php'],
    ['icon' => 'far fa-calendar-alt',   'text' => 'Interviews',        'href' => 'interviews.php',      'page' => 'interviews.php'],
    ['icon' => 'fas fa-university',     'text' => 'Higher Studies',    'href' => 'higher_studies.php',  'page' => 'higher_studies.php'],
    ['section' => 'Communication'],
    ['icon' => 'far fa-bell',           'text' => 'Notifications',     'href' => 'notifications.php',   'page' => 'notifications.php'],
    ['icon' => 'fas fa-bullhorn',       'text' => 'Announcements',     'href' => 'announcements.php',   'page' => 'announcements.php'],
    ['icon' => 'fas fa-envelope',       'text' => 'Messages',          'href' => 'contact_messages.php','page' => 'contact_messages.php', 'badge' => $pendingMsgCount > 0 ? $pendingMsgCount : null, 'badgeColor' => 'bg-danger'],
    ['section' => 'Reports & Logs'],
    ['icon' => 'fas fa-file-export',    'text' => 'Reports',           'href' => 'reports.php',         'page' => 'reports.php'],
    ['icon' => 'fas fa-history',        'text' => 'Activity Logs',     'href' => 'activity_logs.php',   'page' => 'activity_logs.php'],
    ['section' => 'System'],
    ['icon' => 'fas fa-cog',            'text' => 'Settings',          'href' => 'settings.php',        'page' => 'settings.php'],
    ['icon' => 'far fa-user-circle',    'text' => 'My Profile',        'href' => 'profile.php',         'page' => 'profile.php'],
];
?>
<aside class="sidebar" id="sidebar" style="background:#0F172A;border-right:1px solid rgba(255,255,255,0.06);">
    <div class="sidebar-inner" style="padding:1rem 0.75rem;">

        <!-- Admin User Card -->
        <div class="sidebar-user" style="background:linear-gradient(135deg,#4C1D95,#6D28D9);border:1px solid rgba(255,255,255,0.1);">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1rem;flex-shrink:0;box-shadow:0 4px 12px rgba(0,0,0,0.2);">
                <?= strtoupper(substr($_SESSION['admin_name']??'A',0,2)) ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name" style="color:#fff;font-weight:700;font-size:0.875rem;"><?= htmlspecialchars($_SESSION['admin_name']??'Admin') ?></div>
                <div class="sidebar-user-role" style="color:rgba(255,255,255,0.65);font-size:0.7rem;display:flex;align-items:center;gap:4px;">
                    <i class="fas fa-shield-alt" style="font-size:0.6rem;color:#A5B4FC;"></i>
                    <?= ucfirst(str_replace('_',' ',$_SESSION['admin_role']??'admin')) ?>
                </div>
            </div>
        </div>

        <?php foreach ($adminNavItems as $item): ?>
            <?php if (isset($item['section'])): ?>
                <div class="sidebar-section-title" style="color:rgba(148,163,184,0.5);font-size:0.62rem;letter-spacing:1.2px;padding:12px 10px 4px;"><?= $item['section'] ?></div>
            <?php else: ?>
                <ul class="sidebar-menu" style="margin-bottom:2px;">
                    <li class="sidebar-menu-item <?= $currentPage === $item['page'] ? 'active' : '' ?>">
                        <a href="<?= htmlspecialchars($item['href']) ?>" class="sidebar-link" 
                           style="color:<?= $currentPage === $item['page'] ? '' : 'rgba(148,163,184,0.85)' ?>;"
                           title="<?= htmlspecialchars($item['text']) ?>">
                            <i class="<?= $item['icon'] ?>"></i>
                            <span class="sidebar-text"><?= htmlspecialchars($item['text']) ?></span>
                            <?php if (!empty($item['badge'])): ?>
                            <span class="link-badge <?= $item['badgeColor'] ?? '' ?>" style="margin-left:auto;font-size:0.65rem;padding:2px 7px;border-radius:10px;"><?= $item['badge'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="sidebar-footer" style="border-top:1px solid rgba(255,255,255,0.06);padding:12px 14px;">
        <a href="logout.php" class="sidebar-logout" style="color:#F87171;display:flex;align-items:center;gap:10px;text-decoration:none;font-weight:600;font-size:0.825rem;">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-logout-text">Sign Out</span>
        </a>
    </div>
</aside>

<div class="main-content" id="mainContent">
