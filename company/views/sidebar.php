<?php
/**
 * ASTPMS — Company Sidebar
 */

$companyNavItems = [
    ['icon' => 'fas fa-chart-line',     'text' => 'Dashboard',       'href' => 'index.php',        'page' => 'index.php'],
    ['icon' => 'fas fa-briefcase',      'text' => 'Job Postings',    'href' => 'jobs.php',         'page' => 'jobs.php'],
    ['icon' => 'fas fa-users-viewfinder','text' => 'Applicants',     'href' => 'applications.php', 'page' => 'applications.php'],
    ['icon' => 'far fa-calendar-check', 'text' => 'Interviews',      'href' => 'interviews.php',   'page' => 'interviews.php'],
    ['icon' => 'far fa-building',       'text' => 'Company Profile', 'href' => 'profile.php',      'page' => 'profile.php'],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-user" style="background:linear-gradient(135deg,#0891B2,#06B6D4);">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1rem;flex-shrink:0;">
                <?= strtoupper(substr($_SESSION['company_name']??'C',0,2)) ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['company_name']??'Company') ?></div>
                <div class="sidebar-user-role" style="color:rgba(255,255,255,0.8);">
                    <i class="fas fa-circle" style="font-size:0.4rem;color:#4ADE80;vertical-align:middle;"></i>
                    Recruiter Partner
                </div>
            </div>
        </div>

        <div class="sidebar-section-title">Recruiter Menu</div>
        <ul class="sidebar-menu">
            <?php foreach ($companyNavItems as $item): ?>
            <li class="sidebar-menu-item <?= $currentPage === $item['page'] ? 'active' : '' ?>">
                <a href="<?= htmlspecialchars($item['href']) ?>" class="sidebar-link" title="<?= htmlspecialchars($item['text']) ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span class="sidebar-text"><?= htmlspecialchars($item['text']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-logout-text">Sign Out</span>
        </a>
    </div>
</aside>

<div class="main-content" id="mainContent">
