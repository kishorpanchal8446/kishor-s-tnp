<?php
/**
 * ASTPMS 2.0 — Dedicated Company / Recruiter Login Page
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Company.php';

initSession();

// If already logged in as company, redirect to company dashboard
if (isCompanyLoggedIn()) {
    redirect('company/index.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $companyModel = new Company($pdo);
        $company = $companyModel->getByEmail($email);

        if ($company && password_verify($password, $company['password'])) {
            if (empty($company['is_active'])) {
                $error = 'Your company account is pending administrative verification.';
            } else {
                $_SESSION['company_id']   = $company['id'];
                $_SESSION['company_name'] = $company['name'];
                $_SESSION['user_role']    = 'company';
                logActivity($pdo, $company['id'], 'company', 'login', 'Company logged in', 'login');
                redirect('company/index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Company Sign In — ASTPMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
</head>
<body style="background:linear-gradient(135deg, #0F172A 0%, #1E3A8A 50%, #06B6D4 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">

<div style="width:100%;max-width:440px;" data-aos="zoom-in">
    <div class="text-center mb-4">
        <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#06B6D4,#2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;margin:0 auto 1rem;box-shadow:0 10px 25px rgba(6,182,212,0.4);">
            <i class="fas fa-building"></i>
        </div>
        <h3 class="fw-bold text-white mb-1" style="font-family:'Outfit',sans-serif;">Company Recruiter Portal</h3>
        <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;">ASTPMS Campus Hiring Dashboard</p>
    </div>

    <div class="card-glassmorphism p-4 p-lg-5">
        <?php if (!empty($error)): ?>
        <div class="alert-premium alert-premium-danger mb-3">
            <i class="fas fa-exclamation-circle me-1"></i><?= h($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrfInput() ?>
            <div class="mb-3">
                <label class="form-label-premium text-dark">Recruiter Work Email</label>
                <input type="email" name="email" id="email" class="form-control-premium" placeholder="hr@company.com" required value="hr@wipro.com">
            </div>
            <div class="mb-4">
                <label class="form-label-premium text-dark">Password</label>
                <input type="password" name="password" id="password" class="form-control-premium" placeholder="••••••••" required value="Company@123">
            </div>

            <button type="submit" class="btn-premium w-100 justify-content-center py-3 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In to Recruiter Portal
            </button>
        </form>

        <div class="text-center pt-3 border-top" style="border-color:rgba(0,0,0,0.08) !important;">
            <span style="font-size:0.85rem;color:var(--text-muted);">New Recruiting Partner?</span>
            <a href="register.php" class="fw-bold text-primary-color ms-1" style="text-decoration:none;">Register Company Profile</a>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <a href="../login.php" class="text-white-50 text-decoration-none" style="font-size:0.85rem;">
            <i class="fas fa-arrow-left me-1"></i>Return to Student Portal
        </a>
    </div>
</div>

<script src="../assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
