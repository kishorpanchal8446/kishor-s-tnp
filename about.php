<?php
/**
 * ASTPMS 2.0 — Redesigned About Page
 * Vision, Mission, Objectives, Placement Process Timeline, Faculty Details, and Achievements
 */

require_once 'config/app.php';
require_once 'config/auth.php';
require_once 'config/db.php';

initSession();

$pageTitle = 'About Us — Training & Placement Cell | ASTPMS';
$isLoggedIn = isStudentLoggedIn();

if (!$isLoggedIn) {
    require_once 'views/public_header.php';
} else {
    require_once 'views/header.php';
    require_once 'views/sidebar.php';
}
?>

<!-- Header Banner -->
<section class="py-5 bg-section-page text-center" style="margin-top:var(--header-height);">
    <div class="container" data-aos="fade-up">
        <span class="badge-premium bg-light-primary mb-2">Training &amp; Placement Cell</span>
        <h1 class="fw-extrabold mb-2" style="font-family:'Outfit',sans-serif;color:var(--primary);">About ASTPMS &amp; T&amp;P Cell</h1>
        <p style="color:var(--text-muted);max-width:700px;margin:0 auto;" class="lead">
            Building world-class tech leaders and facilitating career transformations through industry-academic partnerships.
        </p>
    </div>
</section>

<!-- Vision, Mission & Objectives -->
<section class="py-5 bg-main-page">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="50">
                <div class="card-premium p-4 h-100 text-center">
                    <div class="stat-icon stat-icon-blue mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="font-family:'Outfit',sans-serif;">Our Vision</h4>
                    <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.7;">
                        To be a premier university placement ecosystem recognized globally for nurturing industry-ready professionals and achieving 100% placement excellence.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-premium p-4 h-100 text-center">
                    <div class="stat-icon stat-icon-cyan mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="font-family:'Outfit',sans-serif;">Our Mission</h4>
                    <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.7;">
                        To deliver continuous technical and soft-skill workshops, forge corporate recruitment tie-ups, and offer transparent career guidance for every student.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                <div class="card-premium p-4 h-100 text-center">
                    <div class="stat-icon stat-icon-purple mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="font-family:'Outfit',sans-serif;">Our Objectives</h4>
                    <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.7;">
                        Automate placement workflows, conduct mock interviews, maximize Fortune 500 company drives, and provide higher education assistance (GATE/GRE).
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Placement Process Timeline -->
<section class="py-5 bg-alt-section">
    <div class="container" data-aos="fade-up">
        <div class="text-center mb-5">
            <span class="badge-premium bg-light-primary mb-2">Workflow</span>
            <h2 class="fw-bold" style="font-family:'Outfit',sans-serif;">5-Stage Placement Drive Process</h2>
            <p style="color:var(--text-muted);">How students transition from registration to landing job offer letters.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="process-timeline">
                    <div class="process-item" data-aos="fade-up">
                        <div class="process-badge">1</div>
                        <div class="card-premium p-4">
                            <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Student Registration &amp; Profile Verification</h5>
                            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0;">Students register on ASTPMS, verify email via OTP, input CGPA, and upload PDF resumes.</p>
                        </div>
                    </div>

                    <div class="process-item" data-aos="fade-up">
                        <div class="process-badge">2</div>
                        <div class="card-premium p-4">
                            <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Skill Bootcamps &amp; Pre-Placement Training</h5>
                            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0;">Attending technical workshops, coding assessments, resume building, and mock interviews.</p>
                        </div>
                    </div>

                    <div class="process-item" data-aos="fade-up">
                        <div class="process-badge">3</div>
                        <div class="card-premium p-4">
                            <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Company Job Drive Postings &amp; Shortlisting</h5>
                            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0;">Recruiters post job openings. Students matching CGPA &amp; branch criteria apply in one click.</p>
                        </div>
                    </div>

                    <div class="process-item" data-aos="fade-up">
                        <div class="process-badge">4</div>
                        <div class="card-premium p-4">
                            <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Aptitude Tests &amp; Technical Interviews</h5>
                            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0;">Conducting online aptitude, technical rounds, and HR interviews with real-time schedule tracking.</p>
                        </div>
                    </div>

                    <div class="process-item" data-aos="fade-up">
                        <div class="process-badge">5</div>
                        <div class="card-premium p-4">
                            <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Job Offer Letter Generation</h5>
                            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0;">Final selections announced. Offer letters issued and logged into university records.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Placement Officer & Faculty Details -->
<section class="py-5 bg-main-page">
    <div class="container" data-aos="fade-up">
        <div class="text-center mb-5">
            <span class="badge-premium bg-light-cyan mb-2">T&amp;P Leadership</span>
            <h2 class="fw-bold" style="font-family:'Outfit',sans-serif;">Placement Cell Faculty &amp; Officers</h2>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-5 col-lg-4" data-aos="fade-up">
                <div class="card-premium p-4 text-center">
                    <div style="width:90px;height:90px;border-radius:50%;background:var(--btn-gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.5rem;margin:0 auto 1rem;box-shadow:var(--shadow-primary);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Dr. R. K. Verma</h5>
                    <div style="font-size:0.82rem;color:var(--primary-mid);font-weight:700;margin-bottom:0.75rem;">Head Training &amp; Placement Officer</div>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">18+ years of industry &amp; academic experience in corporate relations and student placement leadership.</p>
                    <div style="font-size:0.78rem;color:var(--text-muted);">
                        <i class="fas fa-envelope me-1"></i>placement@university.edu
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-premium p-4 text-center">
                    <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#06B6D4,#10B981);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.5rem;margin:0 auto 1rem;box-shadow:var(--shadow-sm);">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;">Prof. Sunita Sharma</h5>
                    <div style="font-size:0.82rem;color:var(--accent-emerald);font-weight:700;margin-bottom:0.75rem;">Training Coordinator</div>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">Specialist in technical curriculum design, competitive programming, and soft skills workshops.</p>
                    <div style="font-size:0.78rem;color:var(--text-muted);">
                        <i class="fas fa-envelope me-1"></i>training@university.edu
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'views/footer.php'; ?>
