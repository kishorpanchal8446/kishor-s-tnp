<?php
$pageTitle = 'Admin Dashboard — ASTPMS';
require_once 'views/header.php';
require_once 'views/sidebar.php';

// ── Dashboard Data ────────────────────────────────────────
$stats = [
    'total_students'    => $pdo->query("SELECT COUNT(*) FROM students WHERE is_active=1")->fetchColumn(),
    'placed_students'   => $pdo->query("SELECT COUNT(*) FROM students WHERE placement_status='Placed'")->fetchColumn(),
    'unplaced_students' => $pdo->query("SELECT COUNT(*) FROM students WHERE placement_status='Unplaced'")->fetchColumn(),
    'total_companies'   => $pdo->query("SELECT COUNT(*) FROM companies WHERE is_active=1")->fetchColumn(),
    'total_jobs'        => $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='Open'")->fetchColumn(),
    'total_apps'        => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
    'total_training'    => $pdo->query("SELECT COUNT(*) FROM training_programs WHERE status IN ('Active','Upcoming')")->fetchColumn(),
    'higher_studies'    => $pdo->query("SELECT COUNT(*) FROM higher_studies")->fetchColumn(),
    'pending_contacts'  => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    'announcements'     => $pdo->query("SELECT COUNT(*) FROM announcements WHERE is_active=1")->fetchColumn(),
];

$placementPct = $stats['total_students'] > 0
    ? round(($stats['placed_students'] / $stats['total_students']) * 100, 1)
    : 0;

// Recent students
$recentStudents = $pdo->query("SELECT id, name, email, branch, placement_status, cgpa, created_at FROM students ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Recent applications
$recentApps = $pdo->query("
    SELECT a.id, a.status, a.applied_at, s.name AS student_name, j.role, c.name AS company_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    ORDER BY a.applied_at DESC LIMIT 8
")->fetchAll();

// Branch-wise placement stats for chart
$branchStats = $pdo->query("
    SELECT branch, COUNT(*) AS total, SUM(placement_status='Placed') AS placed
    FROM students WHERE is_active=1
    GROUP BY branch
    LIMIT 6
")->fetchAll();

// Monthly placements for trend chart (last 6 months)
$monthlyPlacements = $pdo->query("
    SELECT DATE_FORMAT(updated_at, '%b %Y') AS month, COUNT(*) AS count
    FROM students WHERE placement_status='Placed'
    AND updated_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(updated_at, '%Y-%m')
    ORDER BY updated_at ASC
")->fetchAll();

// Activity logs
$recentLogs = $pdo->query("
    SELECT al.*, COALESCE(s.name, ad.name, c.name) AS user_name
    FROM activity_logs al
    LEFT JOIN students s ON al.user_id = s.id AND al.user_role='student'
    LEFT JOIN admins ad ON al.user_id = ad.id AND al.user_role='admin'
    LEFT JOIN companies c ON al.user_id = c.id AND al.user_role='company'
    ORDER BY al.created_at DESC LIMIT 10
")->fetchAll();

// Announcements
$announcements = $pdo->query("SELECT * FROM announcements WHERE is_active=1 ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Placement status counts
$inProcessCount = $pdo->query("SELECT COUNT(*) FROM students WHERE placement_status='In Process'")->fetchColumn();
$higherCount    = $pdo->query("SELECT COUNT(*) FROM students WHERE placement_status='Higher Studies'")->fetchColumn();
?>

<!-- Dashboard Content -->
<div class="container-fluid px-0">

    <!-- ═══ Welcome Banner ═══════════════════════════════════════════════ -->
    <div class="welcome-banner mb-4" data-aos="fade-down" data-aos-duration="600">
        <canvas id="particles-canvas" style="position:absolute;inset:0;pointer-events:none;z-index:1;border-radius:20px;"></canvas>
        <div class="row align-items-center" style="position:relative;z-index:2;">
            <div class="col-md-7">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(8px);">
                        <i class="fas fa-tachometer-alt" style="font-size:1.2rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:0.68rem;font-weight:700;color:rgba(255,255,255,0.55);text-transform:uppercase;letter-spacing:1px;">Control Center</div>
                        <h2 style="font-family:'Outfit',sans-serif;font-size:1.55rem;font-weight:800;color:#fff;margin:0;line-height:1.15;">
                            Admin Dashboard
                        </h2>
                    </div>
                </div>
                <p style="color:rgba(255,255,255,0.7);margin-bottom:0;font-size:0.875rem;">
                    Welcome back, <strong style="color:#A5B4FC;"><?= h($_SESSION['admin_name']) ?></strong> 👋 — Here's your placement system at a glance.
                </p>
                <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:8px;">
                    <span style="background:rgba(99,102,241,0.25);color:#C7D2FE;padding:4px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;border:1px solid rgba(99,102,241,0.3);">
                        <i class="fas fa-calendar-day me-1"></i><?= date('l, F j, Y') ?>
                    </span>
                    <span style="background:rgba(16,185,129,0.2);color:#6EE7B7;padding:4px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;border:1px solid rgba(16,185,129,0.25);">
                        <i class="fas fa-circle me-1" style="font-size:0.5rem;animation:dotPulse 2s infinite;"></i>System Online
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-4 mt-md-0" style="position:relative;z-index:2;">
                <div style="display:inline-flex;gap:16px;align-items:center;">
                    <!-- Placement Rate Circle -->
                    <div style="text-align:center;">
                        <div style="position:relative;width:90px;height:90px;margin:0 auto;">
                            <svg viewBox="0 0 90 90" style="transform:rotate(-90deg);width:90px;height:90px;">
                                <circle cx="45" cy="45" r="38" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="8"/>
                                <circle cx="45" cy="45" r="38" fill="none" stroke="url(#circleGrad)" stroke-width="8"
                                    stroke-dasharray="238.76"
                                    stroke-dashoffset="<?= 238.76 - (238.76 * $placementPct / 100) ?>"
                                    stroke-linecap="round"
                                    style="transition:stroke-dashoffset 2s cubic-bezier(0.4,0,0.2,1);"/>
                                <defs>
                                    <linearGradient id="circleGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#6366F1"/>
                                        <stop offset="100%" stop-color="#06B6D4"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                <div style="font-size:1.35rem;font-weight:800;color:#fff;font-family:'Outfit',sans-serif;line-height:1;"><?= $placementPct ?>%</div>
                            </div>
                        </div>
                        <div style="font-size:0.7rem;color:rgba(255,255,255,0.6);margin-top:6px;font-weight:600;">Placement Rate</div>
                    </div>
                    <!-- Quick Numbers -->
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="background:rgba(255,255,255,0.1);border-radius:12px;padding:8px 16px;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.12);text-align:left;">
                            <div style="font-size:1.2rem;font-weight:800;color:#fff;font-family:'Outfit',sans-serif;"><?= $stats['placed_students'] ?></div>
                            <div style="font-size:0.68rem;color:rgba(255,255,255,0.6);font-weight:600;">Placed Students</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.1);border-radius:12px;padding:8px 16px;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.12);text-align:left;">
                            <div style="font-size:1.2rem;font-weight:800;color:#fff;font-family:'Outfit',sans-serif;"><?= $stats['total_companies'] ?></div>
                            <div style="font-size:0.68rem;color:rgba(255,255,255,0.6);font-weight:600;">Recruiters</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Stats Cards Row ══════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['icon'=>'fas fa-users',          'label'=>'Total Students',    'val'=>$stats['total_students'],    'color'=>'blue',   'sub'=>'Registered & Active',   'href'=>'students.php'],
            ['icon'=>'fas fa-check-circle',   'label'=>'Placed Students',   'val'=>$stats['placed_students'],   'color'=>'green',  'sub'=>$placementPct.'% of batch','href'=>'students.php?filter=placed'],
            ['icon'=>'fas fa-building',        'label'=>'Companies',         'val'=>$stats['total_companies'],   'color'=>'purple', 'sub'=>'Active recruiters',     'href'=>'companies.php'],
            ['icon'=>'fas fa-briefcase',       'label'=>'Open Jobs',         'val'=>$stats['total_jobs'],        'color'=>'cyan',   'sub'=>'Live postings',         'href'=>'jobs.php'],
            ['icon'=>'fas fa-file-signature',  'label'=>'Applications',      'val'=>$stats['total_apps'],        'color'=>'yellow', 'sub'=>'Total received',        'href'=>'applications.php'],
            ['icon'=>'fas fa-graduation-cap',  'label'=>'Training Programs', 'val'=>$stats['total_training'],    'color'=>'red',    'sub'=>'Active/Upcoming',       'href'=>'training.php'],
            ['icon'=>'fas fa-university',      'label'=>'Higher Studies',    'val'=>$stats['higher_studies'],    'color'=>'purple', 'sub'=>'Available entries',     'href'=>'higher_studies.php'],
            ['icon'=>'fas fa-envelope',        'label'=>'Pending Messages',  'val'=>$stats['pending_contacts'],  'color'=>'red',    'sub'=>'Unread contact msgs',   'href'=>'contact_messages.php'],
        ];
        foreach ($statCards as $i => $card): ?>
        <div class="col-6 col-sm-4 col-md-3" data-aos="fade-up" data-aos-delay="<?= $i * 60 ?>" data-aos-duration="500">
            <a href="<?= h($card['href']) ?>" style="text-decoration:none;">
                <div class="card-premium card-stat p-3 h-100">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.67rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:4px;"><?= h($card['label']) ?></div>
                            <h3 class="fw-bold mb-0 counter" data-target="<?= $card['val'] ?>"
                                style="font-family:'Outfit',sans-serif;font-size:1.75rem;letter-spacing:-1px;color:var(--text);">
                                <?= $card['val'] ?>
                            </h3>
                            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px;"><?= h($card['sub']) ?></div>
                        </div>
                        <div class="stat-icon stat-icon-<?= $card['color'] ?>">
                            <i class="<?= $card['icon'] ?>"></i>
                        </div>
                    </div>
                    <!-- Mini progress bar -->
                    <div style="height:3px;background:var(--border-light);border-radius:4px;overflow:hidden;margin-top:10px;">
                        <?php
                        $pct = $card['val'] > 0 ? min(100, ($card['val'] / max(1, $stats['total_students'])) * 100) : 0;
                        $barColors = ['blue'=>'#3B82F6','green'=>'#10B981','purple'=>'#7C3AED','cyan'=>'#06B6D4','yellow'=>'#F59E0B','red'=>'#EF4444'];
                        $barColor  = $barColors[$card['color']] ?? '#6366F1';
                        ?>
                        <div style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width 1.5s ease;"></div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ═══ Charts Row ════════════════════════════════════════════════════ -->
    <div class="row g-4 mb-4">
        <!-- Branch-wise Placement Chart -->
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="0" data-aos-duration="600">
            <div class="card-premium chart-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0" style="color:var(--text);">
                            <i class="fas fa-chart-bar me-2" style="color:#6366F1;"></i>Branch-wise Placement
                        </h5>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">Comparison of total vs placed students per branch</div>
                    </div>
                    <a href="reports.php" class="btn-premium-outline" style="font-size:0.78rem;padding:6px 14px;border-radius:9px;">
                        <i class="fas fa-download me-1"></i>Export
                    </a>
                </div>
                <div style="position:relative;height:270px;">
                    <canvas id="branchChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Placement Status Doughnut -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100" data-aos-duration="600">
            <div class="card-premium chart-card p-4 h-100">
                <h5 class="fw-bold mb-1" style="color:var(--text);">
                    <i class="fas fa-chart-pie me-2" style="color:#6366F1;"></i>Placement Status
                </h5>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:16px;">Distribution across all students</div>
                <div style="position:relative;height:190px;display:flex;align-items:center;justify-content:center;">
                    <canvas id="statusChart"></canvas>
                    <!-- Center label -->
                    <div style="position:absolute;text-align:center;pointer-events:none;">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--text);font-family:'Outfit',sans-serif;"><?= $stats['total_students'] ?></div>
                        <div style="font-size:0.65rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total</div>
                    </div>
                </div>
                <div class="mt-3 d-flex flex-column gap-2">
                    <?php
                    $statuses = [
                        'Placed'        => [$stats['placed_students'], '#10B981'],
                        'In Process'    => [$inProcessCount,           '#3B82F6'],
                        'Unplaced'      => [$stats['unplaced_students'],'#EF4444'],
                        'Higher Studies'=> [$higherCount,               '#7C3AED'],
                    ];
                    foreach ($statuses as $lbl => [$count, $clr]):
                        $pct2 = $stats['total_students'] > 0 ? round($count / $stats['total_students'] * 100, 1) : 0;
                    ?>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.78rem;">
                            <div class="d-flex align-items-center gap-2">
                                <span style="width:9px;height:9px;border-radius:50%;background:<?= $clr ?>;flex-shrink:0;display:inline-block;"></span>
                                <span style="color:var(--text-muted);"><?= h($lbl) ?></span>
                            </div>
                            <strong style="color:var(--text);"><?= $count ?> <span style="color:var(--text-muted);font-weight:400;">(<?= $pct2 ?>%)</span></strong>
                        </div>
                        <div style="height:4px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct2 ?>%;background:<?= $clr ?>;border-radius:4px;transition:width 1.8s ease;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Recent Students & Applications ══════════════════════════════ -->
    <div class="row g-4 mb-4">
        <!-- Recent Students -->
        <div class="col-lg-6" data-aos="fade-right" data-aos-delay="0" data-aos-duration="550">
            <div class="card-premium p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0" style="color:var(--text);">
                            <i class="fas fa-users me-2" style="color:#6366F1;"></i>Recent Registrations
                        </h5>
                        <div style="font-size:0.72rem;color:var(--text-muted);">Latest 8 students</div>
                    </div>
                    <a href="students.php" class="btn-premium" style="font-size:0.78rem;padding:6px 14px;border-radius:9px;">View All</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table-premium w-100">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Branch</th>
                                <th>CGPA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentStudents as $idx => $s):
                                $sColorMap = ['Placed'=>'status-selected','In Process'=>'status-interview','Unplaced'=>'status-rejected','Higher Studies'=>'status-shortlisted'];
                                $sColor = $sColorMap[$s['placement_status']] ?? 'status-applied';
                            ?>
                            <tr style="animation:fadeInUp 0.4s ease both;animation-delay:<?= $idx * 40 ?>ms;">
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#6366F1,#06B6D4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.75rem;flex-shrink:0;">
                                            <?= strtoupper(substr($s['name'],0,1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-size:0.84rem;font-weight:600;color:var(--text);"><?= h($s['name']) ?></div>
                                            <div style="font-size:0.7rem;color:var(--text-muted);"><?= h($s['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:0.8rem;color:var(--text-muted);"><?= h($s['branch']) ?></td>
                                <td style="font-size:0.875rem;font-weight:700;color:var(--text);"><?= h($s['cgpa']) ?></td>
                                <td><span class="badge-premium <?= $sColor ?>" style="padding:4px 10px;font-size:0.67rem;border-radius:7px;"><?= h($s['placement_status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="col-lg-6" data-aos="fade-left" data-aos-delay="80" data-aos-duration="550">
            <div class="card-premium p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0" style="color:var(--text);">
                            <i class="fas fa-file-signature me-2" style="color:#6366F1;"></i>Recent Applications
                        </h5>
                        <div style="font-size:0.72rem;color:var(--text-muted);">Latest 8 applications</div>
                    </div>
                    <a href="applications.php" class="btn-premium" style="font-size:0.78rem;padding:6px 14px;border-radius:9px;">View All</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table-premium w-100">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Job / Company</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApps as $idx => $app):
                                $appColorMap = ['Applied'=>'status-applied','Shortlisted'=>'status-shortlisted','Interview Scheduled'=>'status-interview','Selected'=>'status-selected','Rejected'=>'status-rejected'];
                                $aColor = $appColorMap[$app['status']] ?? 'status-applied';
                            ?>
                            <tr style="animation:fadeInUp 0.4s ease both;animation-delay:<?= $idx * 40 ?>ms;">
                                <td style="font-size:0.84rem;font-weight:600;color:var(--text);"><?= h($app['student_name']) ?></td>
                                <td>
                                    <div style="font-size:0.82rem;font-weight:600;color:var(--text);"><?= h($app['role']) ?></div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);">
                                        <i class="fas fa-building me-1"></i><?= h($app['company_name']) ?>
                                    </div>
                                </td>
                                <td><span class="badge-premium <?= $aColor ?>" style="padding:4px 10px;font-size:0.67rem;border-radius:7px;"><?= h($app['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Activity Logs & Quick Actions ════════════════════════════════ -->
    <div class="row g-4 mb-2">
        <!-- Activity Timeline -->
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="0" data-aos-duration="600">
            <div class="card-premium p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0" style="color:var(--text);">
                            <i class="fas fa-stream me-2" style="color:#6366F1;"></i>Recent Activity
                        </h5>
                        <div style="font-size:0.72rem;color:var(--text-muted);">Latest system events</div>
                    </div>
                    <a href="activity_logs.php" class="btn-premium-outline" style="font-size:0.78rem;padding:6px 14px;border-radius:9px;">
                        View All
                    </a>
                </div>
                <div class="timeline">
                    <?php foreach ($recentLogs as $idx => $log):
                        $logIcons  = ['login'=>'fas fa-sign-in-alt','apply_job'=>'fas fa-paper-plane','register'=>'fas fa-user-plus','logout'=>'fas fa-sign-out-alt','forgot_password'=>'fas fa-key','password_reset'=>'fas fa-lock'];
                        $dotClass  = in_array($log['action'], ['login','apply_job']) ? 'active' : (in_array($log['action'], ['register']) ? 'success' : '');
                        $lIcon     = $logIcons[$log['action']] ?? 'fas fa-circle';
                    ?>
                    <div class="timeline-item" style="animation-delay:<?= $idx * 50 ?>ms;">
                        <div class="timeline-dot <?= $dotClass ?>">
                            <i class="<?= $lIcon ?>" style="font-size:0.5rem;color:#fff;"></i>
                        </div>
                        <div style="flex:1;padding-left:6px;">
                            <div style="font-size:0.855rem;font-weight:600;color:var(--text);">
                                <?= h($log['user_name'] ?? ucfirst($log['user_role'])) ?>
                                <span style="font-weight:400;color:var(--text-muted);"> — <?= h(ucfirst(str_replace('_',' ',$log['action']))) ?></span>
                            </div>
                            <?php if ($log['description']): ?>
                            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;"><?= h($log['description']) ?></div>
                            <?php endif; ?>
                            <div style="font-size:0.68rem;color:var(--text-muted);margin-top:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                <span><i class="fas fa-clock me-1"></i><?= date('M d, g:i A', strtotime($log['created_at'])) ?></span>
                                <?php if ($log['ip_address']): ?>
                                <span style="color:rgba(100,116,139,0.6);"><i class="fas fa-globe me-1"></i><?= h($log['ip_address']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right column: Quick Actions + Announcements -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="120" data-aos-duration="600">

            <!-- Quick Actions -->
            <div class="card-premium p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color:var(--text);">
                    <i class="fas fa-bolt me-2" style="color:#F59E0B;"></i>Quick Actions
                </h5>
                <div class="d-flex flex-column gap-2">
                    <?php
                    $quickActions = [
                        ['href'=>'students.php?action=add',        'icon'=>'fas fa-user-plus',   'label'=>'Add Student',        'style'=>''],
                        ['href'=>'jobs.php?action=add',            'icon'=>'fas fa-plus-circle',  'label'=>'Post New Job',       'style'=>'background:linear-gradient(135deg,#0EA5E9,#06B6D4);'],
                        ['href'=>'notifications.php?action=send',  'icon'=>'fas fa-paper-plane',  'label'=>'Send Notification',  'style'=>'background:linear-gradient(135deg,#7C3AED,#6D28D9);'],
                        ['href'=>'announcements.php?action=add',   'icon'=>'fas fa-bullhorn',     'label'=>'New Announcement',   'style'=>'background:linear-gradient(135deg,#059669,#10B981);'],
                        ['href'=>'reports.php',                    'icon'=>'fas fa-file-export',  'label'=>'Generate Report',    'style'=>'background:linear-gradient(135deg,#B91C1C,#EF4444);'],
                    ];
                    foreach ($quickActions as $qa): ?>
                    <a href="<?= h($qa['href']) ?>" class="btn-premium w-100" style="justify-content:flex-start;border-radius:11px;<?= $qa['style'] ?>">
                        <i class="<?= $qa['icon'] ?> me-2"></i><?= $qa['label'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Announcements -->
            <div class="card-premium p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="color:var(--text);">
                        <i class="fas fa-bullhorn me-2" style="color:#6366F1;"></i>Announcements
                    </h5>
                    <a href="announcements.php" style="font-size:0.78rem;color:#6366F1;text-decoration:none;font-weight:600;">Manage</a>
                </div>
                <?php if (empty($announcements)): ?>
                <div style="text-align:center;padding:24px 0;color:var(--text-muted);font-size:0.82rem;">
                    <i class="fas fa-bell-slash" style="font-size:1.5rem;margin-bottom:8px;display:block;opacity:0.4;"></i>
                    No active announcements
                </div>
                <?php else: ?>
                <?php
                $priColors = ['Urgent'=>'status-rejected','High'=>'status-shortlisted','Normal'=>'status-applied','Low'=>'status-withdrawn'];
                foreach ($announcements as $idx => $ann): ?>
                <div class="announcement-item" style="animation:fadeInUp 0.4s ease both;animation-delay:<?= $idx * 60 ?>ms;">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <div style="font-size:0.82rem;font-weight:600;color:var(--text);line-height:1.3;"><?= h($ann['title']) ?></div>
                        <span class="badge-premium <?= $priColors[$ann['priority']] ?? '' ?>" style="padding:2px 8px;font-size:0.62rem;border-radius:6px;white-space:nowrap;flex-shrink:0;"><?= h($ann['priority']) ?></span>
                    </div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">
                        <i class="fas fa-clock me-1"></i><?= date('M d, Y', strtotime($ann['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<?php
$chartLabels  = array_column($branchStats, 'branch');
$chartTotal   = array_column($branchStats, 'total');
$chartPlaced  = array_column($branchStats, 'placed');
$statusLabels = ['Placed', 'In Process', 'Unplaced', 'Higher Studies'];
$statusVals   = [$stats['placed_students'], $inProcessCount, $stats['unplaced_students'], $higherCount];

$extraScripts = "
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Helper: chart colours by theme ─────────────────────────────────────
    function getTextColor()  { return document.documentElement.getAttribute('data-theme') === 'dark' ? '#94A3B8' : '#64748B'; }
    function getGridColor()  { return document.documentElement.getAttribute('data-theme') === 'dark' ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.06)'; }
    function getBgColor()    { return document.documentElement.getAttribute('data-theme') === 'dark' ? '#111827' : '#ffffff'; }

    // ── Branch Bar Chart ────────────────────────────────────────────────────
    const bCtx = document.getElementById('branchChart').getContext('2d');
    const branchChart = new Chart(bCtx, {
        type: 'bar',
        data: {
            labels: " . json_encode($chartLabels) . ",
            datasets: [
                {
                    label: 'Total',
                    data: " . json_encode($chartTotal) . ",
                    backgroundColor: 'rgba(99,102,241,0.12)',
                    borderColor: '#6366F1',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Placed',
                    data: " . json_encode($chartPlaced) . ",
                    backgroundColor: 'rgba(16,185,129,0.8)',
                    borderColor: '#10B981',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1200,
                easing: 'easeOutQuart',
                delay: (ctx) => ctx.dataIndex * 80,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: getTextColor(),
                        padding: 18,
                        font: { size: 12, family: 'Poppins', weight: '600' },
                        usePointStyle: true,
                        pointStyle: 'circle',
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.9)',
                    borderColor: 'rgba(99,102,241,0.3)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#94A3B8',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: (ctx) => ' ' + ctx.dataset.label + ': ' + ctx.raw
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: getGridColor() },
                    ticks: { color: getTextColor(), font: { size: 11 } },
                    border: { dash: [4, 4] },
                },
                x: {
                    grid: { display: false },
                    ticks: { color: getTextColor(), font: { size: 11 } },
                }
            }
        }
    });

    // ── Status Doughnut ─────────────────────────────────────────────────────
    const sCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(sCtx, {
        type: 'doughnut',
        data: {
            labels: " . json_encode($statusLabels) . ",
            datasets: [{
                data: " . json_encode($statusVals) . ",
                backgroundColor: ['#10B981','#3B82F6','#EF4444','#7C3AED'],
                borderWidth: 3,
                borderColor: getBgColor(),
                hoverBorderWidth: 4,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            animation: {
                animateRotate: true,
                duration: 1400,
                easing: 'easeOutQuart',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.9)',
                    borderColor: 'rgba(99,102,241,0.3)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#94A3B8',
                    padding: 12,
                    cornerRadius: 10,
                }
            }
        }
    });

    // ── Redraw on theme change ──────────────────────────────────────────────
    window.addEventListener('theme-changed', () => {
        branchChart.options.plugins.legend.labels.color = getTextColor();
        branchChart.options.scales.y.grid.color  = getGridColor();
        branchChart.options.scales.y.ticks.color = getTextColor();
        branchChart.options.scales.x.ticks.color = getTextColor();
        statusChart.data.datasets[0].borderColor = getBgColor();
        branchChart.update();
        statusChart.update();
    });

    // ── Sidebar toggle (mobile) ─────────────────────────────────────────────
    const sidebarToggle  = document.getElementById('sidebar-toggle');
    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth <= 991) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
            }
        });
        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });

        // Restore collapsed state on desktop
        if (window.innerWidth > 991 && localStorage.getItem('sidebar-collapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    }

    // ── AOS Init ────────────────────────────────────────────────────────────
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 560, once: true, offset: 50, easing: 'ease-out-cubic' });
    }

    // ── Animated Counters ───────────────────────────────────────────────────
    const counters = document.querySelectorAll('.counter[data-target]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target, 10) || 0;
                const dur = 1400;
                const start = performance.now();
                const update = (now) => {
                    const prog = Math.min((now - start) / dur, 1);
                    const ease = 1 - Math.pow(1 - prog, 3);
                    el.textContent = Math.floor(target * ease);
                    if (prog < 1) requestAnimationFrame(update);
                    else el.textContent = target;
                };
                requestAnimationFrame(update);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.3 });
    counters.forEach(c => observer.observe(c));

    // ── Mini Particles in welcome banner ────────────────────────────────────
    const canvas = document.getElementById('particles-canvas');
    if (canvas) {
        const ctx2 = canvas.getContext('2d');
        function resizeCanvas() {
            canvas.width  = canvas.parentElement.offsetWidth;
            canvas.height = canvas.parentElement.offsetHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        const pts = Array.from({length: 40}, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 0.5,
            vy: (Math.random() - 0.5) * 0.5,
            r: Math.random() * 1.8 + 0.5,
            a: Math.random() * 0.25 + 0.05,
        }));
        function animateParticles() {
            ctx2.clearRect(0, 0, canvas.width, canvas.height);
            pts.forEach(p => {
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > canvas.width)  p.vx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                ctx2.beginPath();
                ctx2.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx2.fillStyle = 'rgba(165,180,252,' + p.a + ')';
                ctx2.fill();
            });
            requestAnimationFrame(animateParticles);
        }
        animateParticles();
    }

    // ── Ripple on quick-action buttons ──────────────────────────────────────
    document.querySelectorAll('.btn-premium').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height) * 2;
            const rip  = document.createElement('span');
            rip.className = 'ripple-wave';
            rip.style.cssText = 'width:'+size+'px;height:'+size+'px;left:'+(e.clientX-rect.left-size/2)+'px;top:'+(e.clientY-rect.top-size/2)+'px';
            this.appendChild(rip);
            rip.addEventListener('animationend', () => rip.remove());
        });
    });

});
</script>";

require_once 'views/footer.php';
?>
