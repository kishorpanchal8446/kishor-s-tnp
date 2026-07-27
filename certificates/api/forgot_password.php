<?php
/**
 * ASTPMS — API: Forgot Password
 * POST endpoint — generates a secure reset token and sends the email.
 *
 * Returns JSON:
 *   { "success": true|false, "message": "..." }
 *
 * Security:
 *  - CSRF validation
 *  - Email format validation
 *  - DB-backed IP rate limiting (5 requests per 15 min)
 *  - Email enumeration prevention (always same success message)
 *  - Token is SHA-256 hashed before DB storage
 *  - Old unused tokens are deleted before inserting a new one
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/mail.php';

// ── Helper ───────────────────────────────────────────────────────────────────
function jsonOut(bool $success, string $message, int $httpCode = 200): never {
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// ── Only accept POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'Method not allowed.', 405);
}

initSession();

// ── Parse request body (supports both form-data and JSON) ────────────────────
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $body  = json_decode(file_get_contents('php://input'), true) ?? [];
    $email = sanitize($body['email']       ?? '');
    $role  = sanitize($body['role']        ?? 'student');
    $csrf  = $body['csrf_token']           ?? '';
} else {
    $email = sanitize($_POST['email']      ?? '');
    $role  = sanitize($_POST['role']       ?? 'student');
    $csrf  = $_POST['csrf_token']          ?? '';
}

// ── CSRF ─────────────────────────────────────────────────────────────────────
if (!validateCsrfToken($csrf)) {
    jsonOut(false, 'Security token mismatch. Please refresh the page.', 403);
}

// ── Validate role ─────────────────────────────────────────────────────────────
$allowedRoles = ['student', 'admin', 'company'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'student';
}

// ── Validate email format ─────────────────────────────────────────────────────
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonOut(false, 'Please enter a valid email address.');
}

// ── Rate limiting ─────────────────────────────────────────────────────────────
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR']
          ?? $_SERVER['REMOTE_ADDR']
          ?? '0.0.0.0';
$clientIp = trim(explode(',', $clientIp)[0]); // take first IP if proxied

if (!checkForgotPasswordRateLimit($pdo, $clientIp, $email)) {
    $remaining = getForgotPasswordRateLimitRemaining($pdo, $clientIp);
    $msg = $remaining > 0
        ? "Too many requests. Please try again in {$remaining} minute(s)."
        : 'Too many requests. Please try again in a few minutes.';
    jsonOut(false, $msg, 429);
}

// ── Record this attempt BEFORE doing any DB work ──────────────────────────────
recordForgotPasswordAttempt($pdo, $clientIp, $email);

// ── Generic success message (always shown — prevents email enumeration) ───────
$genericSuccess = 'If an account exists with this email address, a password reset link has been sent. Please check your inbox (and spam folder).';

// ── Look up the user ──────────────────────────────────────────────────────────
$tables = ['student' => 'students', 'admin' => 'admins', 'company' => 'companies'];
$table  = $tables[$role];

try {
    $stmt = $pdo->prepare("SELECT id, name FROM `{$table}` WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[ASTPMS ForgotPwd] DB error looking up user: ' . $e->getMessage());
    jsonOut(true, $genericSuccess); // fail silently to the user
}

if (!$user) {
    // User not found — return generic success to prevent enumeration
    jsonOut(true, $genericSuccess);
}

// ── Delete old unused tokens for this email + role ────────────────────────────
try {
    $pdo->prepare("
        DELETE FROM password_reset_tokens
        WHERE email = ? AND user_role = ? AND used = 0
    ")->execute([$email, $role]);
} catch (PDOException $e) {
    error_log('[ASTPMS ForgotPwd] Failed to purge old tokens: ' . $e->getMessage());
}

// ── Generate a secure token ───────────────────────────────────────────────────
$rawToken    = bin2hex(random_bytes(32)); // 256-bit entropy → 64 hex chars
$hashedToken = hash('sha256', $rawToken); // stored in DB
$expiresAt   = date('Y-m-d H:i:s', time() + (TOKEN_EXPIRY_MINUTES * 60));

try {
    $pdo->prepare("
        INSERT INTO password_reset_tokens (email, user_role, token, expires_at, used)
        VALUES (?, ?, ?, ?, 0)
    ")->execute([$email, $role, $hashedToken, $expiresAt]);
} catch (PDOException $e) {
    error_log('[ASTPMS ForgotPwd] Failed to insert token: ' . $e->getMessage());
    jsonOut(true, $genericSuccess); // fail silently
}

// ── Build reset link ──────────────────────────────────────────────────────────
$resetLink = APP_URL . '/reset_password.php'
           . '?token=' . urlencode($rawToken)
           . '&role='  . urlencode($role);

// ── Build email body ──────────────────────────────────────────────────────────
$userName    = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
$expiresMins = TOKEN_EXPIRY_MINUTES;
$roleName    = ucfirst($role);

$emailBody = emailTemplate(
    'Reset Your Password',
    "<p>Hello, <strong>{$userName}</strong></p>
    <p>We received a request to reset the password for your ASTPMS <strong>{$roleName}</strong> account
       associated with <strong>{$email}</strong>.</p>
    <p>Click the button below to set a new password.
       This link will expire in <strong>{$expiresMins} minutes</strong>.</p>
    <p style='background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;
              padding:12px 16px;font-size:13px;color:#92400E;'>
        🔒 <strong>Security Notice:</strong> This link can only be used once and will expire at
        " . date('h:i A', time() + ($expiresMins * 60)) . " (server time). If you did not request
        a password reset, you can safely ignore this email — your password has not been changed.
    </p>",
    '🔑 Reset My Password',
    $resetLink
);

// ── Send the email ────────────────────────────────────────────────────────────
$mailResult = sendMail($email, $user['name'], 'Reset Your ASTPMS Password', $emailBody);

if ($mailResult !== true) {
    error_log('[ASTPMS ForgotPwd] Mail failed for ' . $email . ': ' . $mailResult);
    // Don't expose the SMTP error to the user, but log it
}

// ── Log the activity ──────────────────────────────────────────────────────────
try {
    logActivity($pdo, (int)$user['id'], $role, 'forgot_password',
        'Password reset email requested', 'auth');
} catch (Throwable) {
    // Never break the flow because of logging
}

jsonOut(true, $genericSuccess);
