<?php if (!empty($isLoggedIn) && empty($isPublicPage)): ?>
</div><!-- /.main-content -->
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     REDESIGNED ENTERPRISE FOOTER
     Gradient: linear-gradient(135deg, #020617, #0F172A)
     Text: #CBD5E1 | Headings: #FFFFFF | Hover: #38BDF8
══════════════════════════════════════════════════════════════ -->
<footer class="footer-redesign">
    <div class="container-fluid px-4 px-lg-5">
        <div class="row g-4 pb-4">
            
            <!-- Column 1: About TPMS & Newsletter -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="nav-logo-icon" style="width:36px;height:36px;">
                        <i class="fas fa-graduation-cap text-white" style="font-size:1.1rem;"></i>
                    </div>
                    <span class="brand-text-title" style="font-size:1.2rem;">ASTPMS Portal</span>
                </div>
                <p style="font-size:0.85rem;color:var(--footer-text);line-height:1.6;margin-bottom:1.25rem;">
                    Advanced Student Training &amp; Placement Management System (ASTPMS) is the enterprise recruitment engine connecting students, global corporations, and university placement cells.
                </p>

                <!-- Newsletter Subscription -->
                <div class="mb-3">
                    <div class="footer-heading" style="font-size:0.9rem;margin-bottom:0.75rem;">Placement Newsletter</div>
                    <form onsubmit="event.preventDefault(); showToast('Subscribed to placement alerts!','success');" class="d-flex gap-2">
                        <input type="email" class="form-control-premium" placeholder="Enter your email" required style="font-size:0.8rem;padding:8px 12px;background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.15);color:#fff;">
                        <button type="submit" class="btn-premium" style="padding:8px 16px;font-size:0.8rem;white-space:nowrap;background:linear-gradient(135deg, #06B6D4, #2563EB);">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Column 2: Quick Links & Modules -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-heading">Quick Links</div>
                <a href="index.php"          class="footer-link"><i class="fas fa-angle-right"></i>Home</a>
                <a href="about.php"          class="footer-link"><i class="fas fa-angle-right"></i>About Us</a>
                <a href="placements.php"     class="footer-link"><i class="fas fa-angle-right"></i>Placements</a>
                <a href="companies.php"      class="footer-link"><i class="fas fa-angle-right"></i>Recruiters</a>
                <a href="training.php"       class="footer-link"><i class="fas fa-angle-right"></i>Training Programs</a>
                <a href="higher_studies.php" class="footer-link"><i class="fas fa-angle-right"></i>Higher Studies</a>
            </div>

            <!-- Column 3: Portals & Support -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-heading">Portals &amp; Support</div>
                <a href="jobs.php"           class="footer-link"><i class="fas fa-angle-right"></i>Placement Jobs</a>
                <a href="login.php"          class="footer-link"><i class="fas fa-angle-right"></i>Student Login</a>
                <a href="register.php"       class="footer-link"><i class="fas fa-angle-right"></i>Student Registration</a>
                <a href="company/index.php"  class="footer-link"><i class="fas fa-angle-right"></i>Company Portal</a>
                <a href="admin/login.php"    class="footer-link"><i class="fas fa-angle-right"></i>Admin Portal</a>
                <a href="help.php"           class="footer-link"><i class="fas fa-angle-right"></i>Help Center</a>
                <a href="faq.php"            class="footer-link"><i class="fas fa-angle-right"></i>FAQs</a>
            </div>

            <!-- Column 4: Contact Us & Social -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-heading">Contact T&amp;P Cell</div>
                <div class="d-flex flex-column gap-2" style="font-size:0.85rem;color:var(--footer-text);">
                    <div><i class="fas fa-user-tie me-2" style="color:var(--accent-hover);"></i>Placement Officer: Dr. R. K. Verma</div>
                    <div><i class="fas fa-envelope me-2" style="color:var(--accent-hover);"></i>placement@university.edu</div>
                    <div><i class="fas fa-phone-alt me-2" style="color:var(--accent-hover);"></i>+91-1234567890 / +91-9876543210</div>
                    <div><i class="fas fa-map-marker-alt me-2" style="color:var(--accent-hover);"></i>T&amp;P Cell, Admin Block, Main Campus</div>
                </div>

                <!-- Social Icons -->
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="footer-social-icon" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="footer-social-icon" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="footer-social-icon" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="footer-social-icon" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="footer-social-icon" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

        </div>

        <!-- Divider -->
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">

        <!-- Bottom Copyright & Legal Links -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2" style="font-size:0.78rem;color:var(--footer-text);">
            <div>
                &copy; <?= date('Y') ?> <strong style="color:#fff;">ASTPMS</strong> — Training &amp; Placement Management System. All Rights Reserved.
            </div>
            <div class="d-flex gap-3 flex-wrap">
                <a href="privacy.php" class="footer-link mb-0" style="font-size:0.78rem;">Privacy Policy</a>
                <a href="terms.php"   class="footer-link mb-0" style="font-size:0.78rem;">Terms &amp; Conditions</a>
                <a href="sitemap.php" class="footer-link mb-0" style="font-size:0.78rem;">Site Map</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back To Top Button -->
<button id="backToTopBtn" title="Back to Top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Toast Container -->
<div class="toast-container-custom" id="toastContainer"></div>

<!-- ═══════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/js/app.js?v=<?= time() ?>"></script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>

</body>
</html>
