<?php
/**
 * ASTPMS — Forgot Password Page
 * Step 1: User enters email → AJAX call to api/forgot_password.php
 * Step 2: Animated success state shown
 */

require_once 'config/app.php';
require_once 'config/auth.php';
require_once 'config/db.php';

initSession();

// Redirect already-logged-in users
if (isStudentLoggedIn())  { header('Location: index.php');         exit; }
if (isAdminLoggedIn())    { header('Location: admin/index.php');   exit; }
if (isCompanyLoggedIn())  { header('Location: company/index.php'); exit; }

$role      = sanitize($_GET['role'] ?? 'student');
if (!in_array($role, ['student', 'admin', 'company'])) $role = 'student';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — ASTPMS</title>
    <meta name="description" content="Reset your ASTPMS account password securely.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0A0F1E 0%, #0D1B2A 45%, #0A1628 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            overflow-x: hidden;
        }

        /* ── Animated background orbs ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.13;
            pointer-events: none;
            animation: orbFloat 8s ease-in-out infinite alternate;
        }
        .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, #2563EB, #1E3A8A); top: -150px; left: -150px; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, #06B6D4, #0891B2); bottom: -100px; right: -100px; animation-delay: -3s; }
        .orb-3 { width: 250px; height: 250px; background: radial-gradient(circle, #7C3AED, #5B21B6); top: 40%; left: 60%; animation-delay: -6s; opacity: 0.08; }
        @keyframes orbFloat {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(20px, 30px) scale(1.05); }
        }

        /* ── Wrapper ── */
        .wrapper { width: 100%; max-width: 450px; position: relative; z-index: 1; }

        /* ── Back link ── */
        .back-link {
            display: inline-flex; align-items: center; gap: 8px;
            color: rgba(255,255,255,0.45); font-size: 0.83rem;
            text-decoration: none; margin-bottom: 1.5rem;
            transition: all 0.2s ease;
            padding: 6px 12px; border-radius: 8px;
        }
        .back-link:hover { color: #60A5FA; background: rgba(96,165,250,0.08); }
        .back-link i { font-size: 0.75rem; }

        /* ── Card ── */
        .card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255,255,255,0.07);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border-radius: 24px;
            padding: 2.75rem 2.5rem;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5),
                        inset 0 1px 0 rgba(255,255,255,0.06);
            animation: cardIn 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(28px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Icon ── */
        .icon-wrap {
            width: 68px; height: 68px;
            background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(37,99,235,0.4),
                        0 0 0 1px rgba(37,99,235,0.3);
            font-size: 1.6rem;
        }

        /* ── Headings ── */
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem; font-weight: 800;
            color: #F8FAFC; text-align: center; margin-bottom: 0.5rem;
        }
        .subtitle {
            color: rgba(255,255,255,0.42);
            font-size: 0.875rem; line-height: 1.65;
            text-align: center; margin-bottom: 2rem;
        }

        /* ── Role Tabs ── */
        .role-tabs {
            display: flex; gap: 4px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px; padding: 4px; margin-bottom: 1.5rem;
        }
        .role-tab {
            flex: 1; padding: 8px 4px;
            background: none; border: none; border-radius: 9px;
            color: rgba(255,255,255,0.4); font-family: 'Inter', sans-serif;
            font-size: 0.8rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .role-tab.active {
            background: rgba(37,99,235,0.2);
            color: #60A5FA;
            box-shadow: 0 2px 8px rgba(37,99,235,0.2);
        }
        .role-tab:hover:not(.active) { color: rgba(255,255,255,0.65); }

        /* ── Labels ── */
        .label {
            font-size: 0.78rem; font-weight: 600;
            color: rgba(255,255,255,0.5);
            margin-bottom: 7px; display: block;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ── Input ── */
        .input-wrap { position: relative; }
        .input-wrap .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.28); font-size: 0.88rem;
            pointer-events: none;
            transition: color 0.2s;
        }
        input[type="email"] {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1.5px solid rgba(255,255,255,0.09);
            color: #F8FAFC; border-radius: 12px;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            font-size: 0.92rem; font-family: 'Inter', sans-serif;
            transition: all 0.25s;
        }
        input[type="email"]:focus {
            outline: none;
            border-color: #2563EB;
            background: rgba(37,99,235,0.06);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
        }
        input[type="email"]:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: #60A5FA; }
        input[type="email"]::placeholder { color: rgba(255,255,255,0.18); }
        input[type="email"].is-invalid { border-color: #EF4444; box-shadow: 0 0 0 4px rgba(239,68,68,0.1); }

        /* ── Submit Button ── */
        .btn-submit {
            width: 100%; margin-top: 1.25rem;
            background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
            border: none; color: #fff; border-radius: 12px;
            padding: 0.9rem; font-size: 0.95rem; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer;
            transition: all 0.25s; position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        .btn-submit:hover:not(:disabled)::before { transform: translateX(100%); }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(37,99,235,0.5);
        }
        .btn-submit:active:not(:disabled) { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.65; cursor: not-allowed; }

        /* ── Spinner ── */
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .d-none { display: none !important; }

        /* ── Alert ── */
        .alert {
            display: flex; gap: 10px; align-items: flex-start;
            padding: 0.85rem 1rem; border-radius: 12px;
            font-size: 0.855rem; margin-bottom: 1.25rem;
            animation: alertIn 0.3s ease;
        }
        @keyframes alertIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .alert i { margin-top: 1px; flex-shrink: 0; }
        .alert-error   { background: rgba(239,68,68,0.12);  border: 1px solid rgba(239,68,68,0.25);  color: #FCA5A5; }
        .alert-warning { background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.25); color: #FCD34D; }

        /* ── Success State ── */
        #successState {
            display: none;
            text-align: center;
            animation: cardIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .success-ring {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #16A34A, #22C55E);
            border-radius: 50%; margin: 0 auto 1.5rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #fff;
            box-shadow: 0 10px 30px rgba(34,197,94,0.4);
            animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;
        }
        @keyframes popIn {
            from { transform: scale(0) rotate(-90deg); opacity: 0; }
            to   { transform: scale(1) rotate(0deg);   opacity: 1; }
        }
        .success-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem; font-weight: 800;
            color: #F8FAFC; margin-bottom: 0.5rem;
        }
        .success-sub {
            color: rgba(255,255,255,0.45);
            font-size: 0.875rem; line-height: 1.65; margin-bottom: 1.5rem;
        }
        .success-info {
            background: rgba(37,99,235,0.1);
            border: 1px solid rgba(37,99,235,0.2);
            border-radius: 12px; padding: 1rem;
            font-size: 0.82rem; color: rgba(255,255,255,0.6);
            line-height: 1.6; margin-bottom: 1.5rem;
        }
        .success-info strong { color: #93C5FD; }
        .btn-back-login {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.75); border-radius: 12px;
            padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-back-login:hover { background: rgba(255,255,255,0.1); color: #fff; }

        /* ── Footer note ── */
        .note {
            text-align: center; margin-top: 1.5rem;
            font-size: 0.8rem; color: rgba(255,255,255,0.28);
        }
        .note a { color: #60A5FA; text-decoration: none; }
        .note a:hover { color: #93C5FD; }

        /* ── Expiry badge ── */
        .expiry-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2);
            color: #FCD34D; border-radius: 8px;
            padding: 5px 12px; font-size: 0.78rem; font-weight: 600;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="wrapper">
    <a href="login.php?role=<?= h($role) ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Login
    </a>

    <div class="card">
        <!-- Icon -->
        <div class="icon-wrap">🔑</div>

        <!-- ── FORM STATE ── -->
        <div id="formState">
            <h1>Forgot Password?</h1>
            <p class="subtitle">Enter your registered email and we'll send you a secure one-time reset link.</p>

            <!-- Error alert (JS-populated) -->
            <div class="alert alert-error d-none" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorMsg"></span>
            </div>
            <div class="alert alert-warning d-none" id="warnAlert">
                <i class="fas fa-clock"></i>
                <span id="warnMsg"></span>
            </div>

            <!-- Role tabs -->
            <div class="role-tabs" id="roleTabs">
                <button type="button" class="role-tab <?= $role === 'student' ? 'active' : '' ?>" data-role="student">
                    <i class="fas fa-user-graduate"></i> Student
                </button>
                <button type="button" class="role-tab <?= $role === 'admin' ? 'active' : '' ?>" data-role="admin">
                    <i class="fas fa-user-shield"></i> Admin
                </button>
                <button type="button" class="role-tab <?= $role === 'company' ? 'active' : '' ?>" data-role="company">
                    <i class="fas fa-building"></i> Company
                </button>
            </div>

            <form id="forgotForm" novalidate>
                <input type="hidden" name="csrf_token" id="csrfToken" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="role"       id="roleInput"  value="<?= h($role) ?>">

                <label class="label" for="email">Registered Email Address</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email"
                           placeholder="your@email.com"
                           autocomplete="email"
                           required>
                    <i class="fas fa-envelope input-icon"></i>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span id="btnText"><i class="fas fa-paper-plane"></i> Send Reset Link</span>
                    <span id="btnSpinner" class="d-none"><span class="spinner"></span> Sending...</span>
                </button>
            </form>

            <div class="note" style="margin-top:1.5rem;">
                Remembered your password? <a href="login.php?role=<?= h($role) ?>">Sign In</a>
            </div>
        </div>

        <!-- ── SUCCESS STATE ── -->
        <div id="successState">
            <div class="success-ring">✓</div>
            <div class="success-title">Email Sent!</div>
            <p class="success-sub">
                If an account with that email exists, a password reset link has been sent.
                Please check your inbox and spam folder.
            </p>
            <div class="success-info">
                <strong>📋 Next steps:</strong><br>
                1. Open your email inbox<br>
                2. Look for an email from <strong>ASTPMS T&amp;P Portal</strong><br>
                3. Click the <strong>Reset My Password</strong> button<br>
                4. The link expires in <strong><?= TOKEN_EXPIRY_MINUTES ?> minutes</strong>
            </div>
            <div style="text-align:center;">
                <a href="login.php?role=<?= h($role) ?>" class="btn-back-login">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            <div style="text-align:center;">
                <div class="expiry-badge">
                    <i class="fas fa-clock"></i>
                    Link expires in <?= TOKEN_EXPIRY_MINUTES ?> minutes
                </div>
            </div>
            <div class="note" style="margin-top:1.25rem;">
                Didn't receive an email?
                <a href="#" id="tryAgainLink">Try again</a>
            </div>
        </div>

    </div><!-- .card -->
</div><!-- .wrapper -->

<script>
(function () {
    'use strict';

    const form       = document.getElementById('forgotForm');
    const emailInput = document.getElementById('email');
    const roleInput  = document.getElementById('roleInput');
    const csrfInput  = document.getElementById('csrfToken');
    const submitBtn  = document.getElementById('submitBtn');
    const btnText    = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const errorAlert = document.getElementById('errorAlert');
    const warnAlert  = document.getElementById('warnAlert');
    const errorMsg   = document.getElementById('errorMsg');
    const warnMsg    = document.getElementById('warnMsg');
    const formState  = document.getElementById('formState');
    const successState = document.getElementById('successState');

    // ── Role tabs ────────────────────────────────────────────────
    document.querySelectorAll('.role-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            roleInput.value = tab.dataset.role;
        });
    });

    // ── Inline email validation ───────────────────────────────────
    emailInput.addEventListener('input', () => {
        emailInput.classList.remove('is-invalid');
        hideAlerts();
    });

    function isValidEmail(e) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
    }

    function showError(msg) {
        errorMsg.textContent = msg;
        errorAlert.classList.remove('d-none');
        warnAlert.classList.add('d-none');
    }

    function showWarning(msg) {
        warnMsg.textContent = msg;
        warnAlert.classList.remove('d-none');
        errorAlert.classList.add('d-none');
    }

    function hideAlerts() {
        errorAlert.classList.add('d-none');
        warnAlert.classList.add('d-none');
    }

    function setLoading(loading) {
        submitBtn.disabled = loading;
        btnText.classList.toggle('d-none', loading);
        btnSpinner.classList.toggle('d-none', !loading);
    }

    // ── Form submit ───────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlerts();

        const email = emailInput.value.trim();

        if (!email) {
            emailInput.classList.add('is-invalid');
            showError('Please enter your email address.');
            emailInput.focus();
            return;
        }

        if (!isValidEmail(email)) {
            emailInput.classList.add('is-invalid');
            showError('Please enter a valid email address (e.g. name@example.com).');
            emailInput.focus();
            return;
        }

        setLoading(true);

        try {
            const res = await fetch('api/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email:      email,
                    role:       roleInput.value,
                    csrf_token: csrfInput.value,
                }),
            });

            const data = await res.json();

            if (data.success) {
                // Animate transition to success state
                formState.style.transition = 'opacity 0.3s ease';
                formState.style.opacity    = '0';
                setTimeout(() => {
                    formState.style.display   = 'none';
                    successState.style.display = 'block';
                }, 300);
            } else if (res.status === 429) {
                showWarning(data.message || 'Too many requests. Please wait before trying again.');
            } else {
                showError(data.message || 'Something went wrong. Please try again.');
            }
        } catch (err) {
            showError('Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    });

    // ── Try again link ────────────────────────────────────────────
    document.getElementById('tryAgainLink').addEventListener('click', (e) => {
        e.preventDefault();
        successState.style.display = 'none';
        formState.style.display    = 'block';
        formState.style.opacity    = '1';
        emailInput.value = '';
        emailInput.focus();
    });

})();
</script>
</body>
</html>
