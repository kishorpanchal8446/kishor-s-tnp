<?php
/**
 * ASTPMS — API: Reset Password
 * POST endpoint — validates token and updates the user's password.
 *
 * Returns JSON:
 *   { "success": true|false, "message": "...", "redirect": "login.php?reset=success" }
 *
 * Security:
 *  - CSRF validation
 *  - Token lookup is done on the SHA-256 hash (raw token never stored)
 *  - Token must not be used and must not be expired
 *  - Password strength validated server-side (validatePassword())
 *  - Password hashed with bcrypt (cost 12)
 *  - Token is marked used=1 after a successful reset
 *  - All queries use PDO prepared statements
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

// ── Helper ───────────────────────────────────────────────────────────────────
function jsonOut(bool $success, string $message, array $extra = [], int $httpCode = 200): never {
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// ── Only accept POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'Method not allowed.', [], 405);
}

initSession();

// ── Parse request body ────────────────────────────────────────────────────────
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $body            = json_decode(file_get_contents('php://input'), true) ?? [];
    $rawToken        = $body['token']            ?? '';
    $role            = sanitize($body['role']    ?? 'student');
    $newPassword     = $body['new_password']     ?? '';
    $confirmPassword = $body['confirm_password'] ?? '';
    $csrf            = $body['csrf_token']       ?? '';
} else {
    $rawToken        = $_POST['token']            ?? '';
    $role            = sanitize($_POST['role']    ?? 'student');
    $newPassword     = $_POST['new_password']     ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrf            = $_POST['csrf_token']       ?? '';
}

// ── CSRF ─────────────────────────────────────────────────────────────────────
if (!validateCsrfToken($csrf)) {
    jsonOut(false, 'Security token mismatch. Please refresh the page.', [], 403);
}

// ── Validate role ─────────────────────────────────────────────────────────────
$allowedRoles = ['student', 'admin', 'company'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'student';
}

// ── Validate token is present ─────────────────────────────────────────────────
$rawToken = trim($rawToken);
if (empty($rawToken)) {
    jsonOut(false, 'Invalid or missing reset token.');
}

// ── Validate password strength ────────────────────────────────────────────────
$pwErrors = validatePassword($newPassword);
if (!empty($pwErrors)) {
    jsonOut(false, implode(' ', $pwErrors));
}

// ── Validate passwords match ──────────────────────────────────────────────────
if ($newPassword !== $confirmPassword) {
    jsonOut(false, 'Passwords do not match. Please try again.');
}

// ── Look up the token in DB ───────────────────────────────────────────────────
$hashedToken = hash('sha256', $rawToken);

try {
    $stmt = $pdo->prepare("
        SELECT * FROM password_reset_tokens
        WHERE token     = ?
          AND user_role = ?
          AND used      = 0
          AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$hashedToken, $role]);
    $tokenData = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[ASTPMS ResetPwd] DB error fetching token: ' . $e->getMessage());
    jsonOut(false, 'A server error occurred. Please try again.', [], 500);
}

if (!$tokenData) {
    // Check if it exists but is expired or used (for a more specific message)
    try {
        $stmt2 = $pdo->prepare("
            SELECT used, expires_at FROM password_reset_tokens
            WHERE token = ? AND user_role = ?
            LIMIT 1
        ");
        $stmt2->execute([$hashedToken, $role]);
        $existingToken = $stmt2->fetch();

        if ($existingToken) {
            if ((int)$existingToken['used'] === 1) {
                jsonOut(false, 'This reset link has already been used. Please request a new one.');
            }
            if (strtotime($existingToken['expires_at']) <= time()) {
                jsonOut(false, 'This reset link has expired (valid for 15 minutes). Please request a new one.');
            }
        }
    } catch (Throwable) {
        // Ignore secondary check errors
    }

    jsonOut(false, 'This password reset link is invalid or has expired. Please request a new one.');
}

// ── Hash and update the password ──────────────────────────────────────────────
$tables = ['student' => 'students', 'admin' => 'admins', 'company' => 'companies'];
$table  = $tables[$role];
$hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

try {
    $updated = $pdo->prepare("
        UPDATE `{$table}`
        SET password   = ?,
            updated_at = NOW()
        WHERE email = ?
    ")->execute([$hashed, $tokenData['email']]);

    if (!$updated) {
        jsonOut(false, 'Failed to update password. Please try again.', [], 500);
    }
} catch (PDOException $e) {
    error_log('[ASTPMS ResetPwd] Failed to update password: ' . $e->getMessage());
    jsonOut(false, 'A server error occurred. Please try again.', [], 500);
}

// ── Mark token as used ────────────────────────────────────────────────────────
try {
    $pdo->prepare("
        UPDATE password_reset_tokens
        SET used = 1
        WHERE id = ?
    ")->execute([$tokenData['id']]);
} catch (PDOException $e) {
    error_log('[ASTPMS ResetPwd] Failed to mark token used: ' . $e->getMessage());
    // Non-fatal — password was already updated; don't fail the request
}

// ── Invalidate any other unused tokens for this email ─────────────────────────
try {
    $pdo->prepare("
        UPDATE password_reset_tokens
        SET used = 1
        WHERE email     = ?
          AND user_role = ?
          AND used      = 0
    ")->execute([$tokenData['email'], $role]);
} catch (Throwable) {
    // Non-fatal
}

// ── Log the activity ──────────────────────────────────────────────────────────
try {
    logActivity($pdo, null, $role, 'password_reset',
        'Password successfully reset via email link for: ' . $tokenData['email'], 'auth');
} catch (Throwable) {
    // Never break the flow because of logging
}

jsonOut(true, 'Password reset successfully! You can now sign in with your new password.', [
    'redirect' => 'login.php?reset=success&role=' . urlencode($role),
]);
