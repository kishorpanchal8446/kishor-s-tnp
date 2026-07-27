<?php
/**
 * ASTPMS 2.0 — Dedicated Company / Recruiter Registration Page
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Company.php';

initSession();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $name        = sanitize($_POST['name'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $phone       = sanitize($_POST['phone'] ?? '');
    $industry    = sanitize($_POST['industry'] ?? '');
    $website     = sanitize($_POST['website'] ?? '');
    $hrName      = sanitize($_POST['hr_name'] ?? '');
    $address     = sanitize($_POST['address'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Company Name, HR Email, and Password are required.';
    } else {
        $companyModel = new Company($pdo);
        if ($companyModel->getByEmail($email)) {
            $error = 'A company account with this HR email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO companies (name, email, password, phone, industry, website, contact_person, address, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([$name, $email, $hash, $phone, $industry, $website, $hrName, $address]);
                $success = 'Company registration submitted successfully! You can now log in.';
            } catch (PDOException $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Company Registration — ASTPMS';
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

<div style="width:100%;max-width:540px;" data-aos="zoom-in">
    <div class="text-center mb-4">
        <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#06B6D4,#2563EB);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;margin:0 auto 1rem;box-shadow:0 10px 25px rgba(6,182,212,0.4);">
            <i class="fas fa-building-user"></i>
        </div>
        <h3 class="fw-bold text-white mb-1" style="font-family:'Outfit',sans-serif;">Register Company Profile</h3>
        <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;">Join as an official campus recruitment partner</p>
    </div>

    <div class="card-glassmorphism p-4 p-lg-5">
        <?php if (!empty($error)): ?>
        <div class="alert-premium alert-premium-danger mb-3">
            <i class="fas fa-exclamation-circle me-1"></i><?= h($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="alert-premium alert-premium-success mb-3">
            <i class="fas fa-check-circle me-1"></i><?= h($success) ?>
            <div class="mt-2"><a href="login.php" class="btn btn-sm btn-success fw-bold text-white">Go to Login</a></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?= csrfInput() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-premium text-dark">Company Name</label>
                    <input type="text" name="name" class="form-control-premium" placeholder="e.g. Acme Tech Solutions" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium text-dark">HR / Work Email</label>
                    <input type="email" name="email" class="form-control-premium" placeholder="hr@company.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium text-dark">Password</label>
                    <input type="password" name="password" class="form-control-premium" placeholder="••••••••" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium text-dark">HR Contact Person Name</label>
                    <input type="text" name="hr_name" class="form-control-premium" placeholder="e.g. Ms. Neha Gupta">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium text-dark">Phone Number</label>
                    <input type="tel" name="phone" class="form-control-premium" placeholder="+91-9876543210">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium text-dark">Industry Sector</label>
                    <input type="text" name="industry" class="form-control-premium" placeholder="e.g. Software & IT Services">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium text-dark">Company Website</label>
                    <input type="url" name="website" class="form-control-premium" placeholder="https://company.com">
                </div>
                <div class="col-12">
                    <label class="form-label-premium text-dark">Office Address</label>
                    <textarea name="address" class="form-control-premium" rows="2" style="resize:vertical;"></textarea>
                </div>
            </div>

            <button type="submit" class="btn-premium w-100 justify-content-center py-3 mt-4 mb-3">
                <i class="fas fa-building me-2"></i>Register Company Partner Profile
            </button>
        </form>

        <div class="text-center pt-3 border-top" style="border-color:rgba(0,0,0,0.08) !important;">
            <span style="font-size:0.85rem;color:var(--text-muted);">Already registered?</span>
            <a href="login.php" class="fw-bold text-primary-color ms-1" style="text-decoration:none;">Sign In Here</a>
        </div>
    </div>
</div>

<script src="../assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
