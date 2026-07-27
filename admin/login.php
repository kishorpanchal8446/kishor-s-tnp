<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

initSession();
if (isAdminLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
$csrfToken = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch.';
    } else {
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!checkLoginAttempts($email)) {
            $error = 'Too many attempts. Locked for ' . getRemainingLockTime($email) . ' minute(s).';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']    = $admin['id'];
                $_SESSION['admin_name']  = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role']  = $admin['role'];
                $_SESSION['user_role']   = 'admin';
                $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
                logActivity($pdo, $admin['id'], 'admin', 'login', 'Admin logged in', 'auth');
                resetLoginAttempts($email);
                header('Location: index.php');
                exit;
            } else {
                incrementLoginAttempts($email);
                $error = 'Invalid admin credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — ASTPMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,#030712,#0F172A,#1E1B4B); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; overflow-y:auto; overflow-x:hidden; }
        .orb { position:fixed; border-radius:50%; filter:blur(80px); opacity:0.12; pointer-events:none; }
        .orb-1 { width:500px; height:500px; background:#7C3AED; top:-150px; left:-150px; }
        .orb-2 { width:400px; height:400px; background:#2563EB; bottom:-100px; right:-100px; }
        .wrapper { width:100%; max-width:420px; position:relative; z-index:10; }
        .brand { text-align:center; margin-bottom:2rem; }
        .brand-logo { width:68px; height:68px; background:linear-gradient(135deg,#4C1D95,#7C3AED); border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; box-shadow:0 0 0 4px rgba(124,58,237,0.2),0 16px 32px rgba(124,58,237,0.3); }
        .brand h1 { font-family:'Outfit',sans-serif; font-size:1.6rem; font-weight:800; color:#F8FAFC; letter-spacing:-0.5px; }
        .brand p { color:rgba(255,255,255,0.4); font-size:0.78rem; margin-top:4px; letter-spacing:1px; text-transform:uppercase; }
        .card { background:rgba(15,23,42,0.8); border:1px solid rgba(255,255,255,0.07); backdrop-filter:blur(24px); border-radius:24px; padding:2.5rem; box-shadow:0 32px 64px rgba(0,0,0,0.5); animation:fadeIn 0.4s ease; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .label { font-size:0.8rem; font-weight:500; color:rgba(255,255,255,0.55); margin-bottom:6px; display:block; }
        .input-wrap { position:relative; margin-bottom:1rem; }
        .input-wrap i.icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.3); font-size:0.85rem; pointer-events:none; }
        input { width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#F8FAFC; border-radius:12px; padding:0.75rem 1rem 0.75rem 2.75rem; font-size:0.9rem; font-family:'Inter',sans-serif; transition:all 0.25s; }
        input:focus { outline:none; border-color:#7C3AED; box-shadow:0 0 0 4px rgba(124,58,237,0.15); background:rgba(124,58,237,0.06); }
        input::placeholder { color:rgba(255,255,255,0.2); }
        .eye-btn { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; color:rgba(255,255,255,0.35); cursor:pointer; }
        .error { background:rgba(220,38,38,0.15); border:1px solid rgba(220,38,38,0.3); color:#FCA5A5; border-radius:10px; padding:0.75rem 1rem; font-size:0.85rem; margin-bottom:1.25rem; display:flex; gap:8px; align-items:center; }
        .btn-login { width:100%; background:linear-gradient(135deg,#4C1D95,#7C3AED); border:none; color:#fff; border-radius:12px; padding:0.85rem; font-size:0.95rem; font-weight:700; font-family:'Inter',sans-serif; cursor:pointer; transition:all 0.25s; margin-top:1rem; }
        .btn-login:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(124,58,237,0.45); }
        .back-link { display:block; text-align:center; margin-top:1.25rem; color:rgba(255,255,255,0.35); font-size:0.82rem; text-decoration:none; }
        .back-link:hover { color:#A78BFA; }
        .security-badge { display:flex; align-items:center; gap:8px; background:rgba(22,163,74,0.12); border:1px solid rgba(22,163,74,0.2); border-radius:8px; padding:8px 12px; font-size:0.75rem; color:#86EFAC; margin-bottom:1.25rem; }
    </style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="wrapper">
    <div class="brand">
        <div class="brand-logo"><i class="fas fa-shield-alt text-white" style="font-size:1.75rem;"></i></div>
        <h1>Admin Portal</h1>
        <p>Restricted Access · ASTPMS</p>
    </div>

    <div class="card">
        <div class="security-badge"><i class="fas fa-lock"></i> Secure Admin Authentication — CSRF &amp; Rate Limited</div>

        <?php if (!empty($error)): ?>
        <div class="error"><i class="fas fa-exclamation-circle"></i><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfInput() ?>
            <label class="label" for="email">Admin Email</label>
            <div class="input-wrap">
                <i class="fas fa-envelope icon"></i>
                <input type="email" id="email" name="email" placeholder="admin@astpms.edu" required autocomplete="email" value="<?= h($_POST['email']??'') ?>">
            </div>
            <label class="label" for="password">Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock icon"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" required style="padding-right:2.75rem;">
                <button type="button" class="eye-btn" onclick="togglePwd()"><i class="fas fa-eye" id="eyeIcon"></i></button>
            </div>
            <div style="text-align:right;margin:-0.5rem 0 1rem;">
                <a href="../forgot_password.php?role=admin" style="color:#A78BFA;font-size:0.78rem;text-decoration:none;">Forgot Password?</a>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-shield-alt me-2"></i>Sign In to Admin Panel</button>
        </form>
        <a href="../login.php" class="back-link"><i class="fas fa-arrow-left me-1"></i> Back to Main Login</a>
    </div>
</div>
<script>
function togglePwd() {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    pwd.type   = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
