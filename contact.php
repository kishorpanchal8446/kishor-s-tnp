<?php
/**
 * ASTPMS 2.0 — Redesigned Contact Page
 * Features: Placement Officer Details, PHPMailer email processing, Google Map Embed,
 * Working Hours, Emergency Contact, and Social Media Icons
 */

require_once 'config/app.php';
require_once 'config/auth.php';
require_once 'config/db.php';
require_once 'config/mail.php';

initSession();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $subject, $message]);

            // Optionally attempt PHPMailer dispatch
            sendMail(MAIL_FROM_ADDRESS, "New T&P Contact Inquiry: $subject", "<h3>Inquiry from $name ($email)</h3><p>$message</p>");

            $success = 'Thank you for reaching out! Your message has been transmitted to the Placement Officer.';
        } catch (Exception $e) {
            $error = 'Could not save message: ' . $e->getMessage();
        }
    }
}

$pageTitle  = 'Contact T&P Cell — ASTPMS';
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
        <span class="badge-premium bg-light-primary mb-2">Get In Touch</span>
        <h1 class="fw-extrabold mb-2" style="font-family:'Outfit',sans-serif;color:var(--primary);">Contact Training &amp; Placement Cell</h1>
        <p style="color:var(--text-muted);max-width:650px;margin:0 auto;" class="lead">
            Have questions regarding campus recruitment drives, company registration, or training workshops? We are here to assist.
        </p>
    </div>
</section>

<!-- Contact Info & Form -->
<section class="py-5 bg-main-page">
    <div class="container">
        
        <?php if (!empty($error)): ?>
        <div class="alert-premium alert-premium-danger mb-4" data-aos="fade-up"><i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="alert-premium alert-premium-success mb-4" data-aos="fade-up"><i class="fas fa-check-circle me-2"></i><?= h($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Contact Form Card -->
            <div class="col-lg-7" data-aos="fade-up">
                <div class="card-premium p-4 p-lg-5 h-100">
                    <h3 class="fw-bold mb-4" style="font-family:'Outfit',sans-serif;"><i class="fas fa-paper-plane text-primary-color me-2"></i>Send an Inquiry</h3>

                    <form method="POST" action="contact.php">
                        <?= csrfInput() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-premium">Your Full Name</label>
                                <input type="text" name="name" class="form-control-premium" placeholder="e.g. Arjun Sharma" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-premium">Email Address</label>
                                <input type="email" name="email" class="form-control-premium" placeholder="student@astpms.edu" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-premium">Subject</label>
                                <input type="text" name="subject" class="form-control-premium" placeholder="e.g. Inquiry regarding Infosys Placement Drive" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-premium">Message</label>
                                <textarea name="message" class="form-control-premium" rows="5" placeholder="Type your inquiry details here..." required style="resize:vertical;"></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn-premium w-100 justify-content-center" style="padding:12px 24px;font-size:0.95rem;">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Message to T&amp;P Officer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Officer Details & Hours Card -->
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div class="card-premium p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-4" style="font-family:'Outfit',sans-serif;"><i class="fas fa-building-columns text-primary-color me-2"></i>Placement Office</h4>
                        
                        <div class="d-flex flex-column gap-3 mb-4" style="font-size:0.9rem;">
                            <div class="d-flex gap-3">
                                <div class="stat-icon stat-icon-blue" style="width:44px;height:44px;font-size:1.1rem;flex-shrink:0;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Head Placement Officer</div>
                                    <div style="color:var(--text-muted);font-size:0.82rem;">Dr. R. K. Verma</div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <div class="stat-icon stat-icon-green" style="width:44px;height:44px;font-size:1.1rem;flex-shrink:0;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Helpline Email</div>
                                    <div style="color:var(--text-muted);font-size:0.82rem;">placement@university.edu</div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <div class="stat-icon stat-icon-purple" style="width:44px;height:44px;font-size:1.1rem;flex-shrink:0;">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Contact Phone</div>
                                    <div style="color:var(--text-muted);font-size:0.82rem;">+91-1234567890 / +91-9876543210</div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <div class="stat-icon stat-icon-cyan" style="width:44px;height:44px;font-size:1.1rem;flex-shrink:0;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Working Hours</div>
                                    <div style="color:var(--text-muted);font-size:0.82rem;">Mon – Sat: 9:00 AM – 5:00 PM</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Icons -->
                    <div class="pt-3 border-top" style="border-color:var(--border-light) !important;">
                        <div style="font-size:0.8rem;font-weight:700;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;">Connect With T&amp;P Cell</div>
                        <div class="d-flex gap-2">
                            <a href="#" class="footer-social-icon" style="background:var(--bg-2);color:var(--text);border-color:var(--border);"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="footer-social-icon" style="background:var(--bg-2);color:var(--text);border-color:var(--border);"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="footer-social-icon" style="background:var(--bg-2);color:var(--text);border-color:var(--border);"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="footer-social-icon" style="background:var(--bg-2);color:var(--text);border-color:var(--border);"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Map Embed Section -->
        <div class="row mt-5" data-aos="fade-up">
            <div class="col-12">
                <div class="card-premium p-3">
                    <h5 class="fw-bold mb-3" style="font-family:'Outfit',sans-serif;"><i class="fas fa-map-marked-alt text-primary-color me-2"></i>Campus Location Map</h5>
                    <div style="width:100%;height:320px;border-radius:var(--radius-sm);overflow:hidden;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3783.248679093863!2d73.8567437!3d18.5204303!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc2c0695071a6cf%3A0xb3a824e8e50b73c2!2sPune%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'views/footer.php'; ?>
