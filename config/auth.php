<?php
/**
 * ASTPMS — Authentication Helpers
 * Session management, CSRF, role guards, and security utilities
 */

require_once __DIR__ . '/app.php';

// ─── Session Initialization ────────────────────────────────────
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// ─── CSRF Token ───────────────────────────────────────────────
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function csrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

// ── Role Guards ──────────────────────────────────────────────
function requireStudentLogin(): void {
    initSession();
    if (empty($_SESSION['student_id'])) {
        $base = getBasePath();
        header('Location: ' . $base . '/login.php?role=student');
        exit;
    }
}

function requireAdminLogin(): void {
    initSession();
    if (empty($_SESSION['admin_id'])) {
        $base = getBasePath();
        header('Location: ' . $base . '/admin/login.php');
        exit;
    }
}

function requireCompanyLogin(): void {
    initSession();
    if (empty($_SESSION['company_id'])) {
        $base = getBasePath();
        header('Location: ' . $base . '/login.php?role=company');
        exit;
    }
}

function isStudentLoggedIn(): bool {
    return !empty($_SESSION['student_id']);
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function isCompanyLoggedIn(): bool {
    return !empty($_SESSION['company_id']);
}

// ── Base URL Helper ──────────────────────────────────────────
function getBasePath(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir    = dirname($script);
    if (strpos($script, '/admin/') !== false || strpos($script, '/company/') !== false) {
        $dir = dirname($dir);
    }
    if ($dir === '/' || $dir === '\\') {
        $dir = '';
    }
    return str_replace(' ', '%20', $dir);
}

// ─── XSS Protection ───────────────────────────────────────────
function h(mixed $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// ─── Sanitize Input ───────────────────────────────────────────
function sanitize(string $input): string {
    return trim(strip_tags($input));
}

// ─── Secure Random Token ─────────────────────────────────────
function generateSecureToken(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}

// ─── Generate OTP ─────────────────────────────────────────────
function generateOTP(int $digits = 6): string {
    return str_pad((string)random_int(0, (int)str_repeat('9', $digits)), $digits, '0', STR_PAD_LEFT);
}

// ─── Password Validation ──────────────────────────────────────
function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8)        $errors[] = 'At least 8 characters required.';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'At least one uppercase letter required.';
    if (!preg_match('/[a-z]/', $password)) $errors[] = 'At least one lowercase letter required.';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'At least one number required.';
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'At least one special character required.';
    return $errors;
}

// ─── Rate Limiting (session-based) ───────────────────────────
function checkLoginAttempts(string $key): bool {
    $attemptsKey = "login_attempts_{$key}";
    $lockKey     = "login_lock_{$key}";
    
    if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
        return false; // Still locked
    }
    return true;
}

function incrementLoginAttempts(string $key): void {
    $attemptsKey = "login_attempts_{$key}";
    $lockKey     = "login_lock_{$key}";
    
    $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
    
    if ($_SESSION[$attemptsKey] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION[$lockKey] = time() + (LOCKOUT_MINUTES * 60);
        $_SESSION[$attemptsKey] = 0;
    }
}

function resetLoginAttempts(string $key): void {
    unset($_SESSION["login_attempts_{$key}"], $_SESSION["login_lock_{$key}"]);
}

function getRemainingLockTime(string $key): int {
    $lockKey = "login_lock_{$key}";
    if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
        return (int)ceil(($_SESSION[$lockKey] - time()) / 60);
    }
    return 0;
}

// ─── Forgot-Password Rate Limiting (DB-backed) ────────────────────────────
// Max 5 requests per IP per 15-minute window.
// Requires the `password_reset_rate_limits` table (see migration_forgot_password.sql).

/**
 * Check whether the given IP (and optionally email) is within rate limits.
 * Returns true if the request is allowed, false if the limit has been hit.
 */
function checkForgotPasswordRateLimit(PDO $pdo, string $ip, string $email = ''): bool {
    $windowMinutes = 15;
    $maxAttempts   = 5;

    try {
        // Count attempts from this IP within the current window
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(attempt_count), 0)
            FROM password_reset_rate_limits
            WHERE ip_address = ?
              AND window_start >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$ip, $windowMinutes]);
        $count = (int)$stmt->fetchColumn();
        return $count < $maxAttempts;
    } catch (PDOException $e) {
        // If the table doesn't exist yet, allow the request (fail open)
        error_log('[ASTPMS] Rate limit check failed (table missing?): ' . $e->getMessage());
        return true;
    }
}

/**
 * Record a forgot-password attempt for the given IP / email.
 * Uses UPSERT-style logic: if a row for this IP exists in the current window,
 * increment the counter; otherwise insert a new row.
 */
function recordForgotPasswordAttempt(PDO $pdo, string $ip, string $email = ''): void {
    $windowMinutes = 15;

    try {
        // Look for an existing row in the current window
        $stmt = $pdo->prepare("
            SELECT id FROM password_reset_rate_limits
            WHERE ip_address = ?
              AND window_start >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$ip, $windowMinutes]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $pdo->prepare("
                UPDATE password_reset_rate_limits
                SET attempt_count = attempt_count + 1
                WHERE id = ?
            ")->execute([$existing]);
        } else {
            $pdo->prepare("
                INSERT INTO password_reset_rate_limits
                    (ip_address, email, attempt_count, window_start)
                VALUES (?, ?, 1, NOW())
            ")->execute([$ip, $email]);
        }
    } catch (PDOException $e) {
        error_log('[ASTPMS] Rate limit record failed: ' . $e->getMessage());
    }
}

/**
 * Return how many minutes remain in the current rate-limit window.
 * Returns 0 if no active block.
 */
function getForgotPasswordRateLimitRemaining(PDO $pdo, string $ip): int {
    $windowMinutes = 15;
    $maxAttempts   = 5;

    try {
        $stmt = $pdo->prepare("
            SELECT window_start
            FROM password_reset_rate_limits
            WHERE ip_address = ?
              AND window_start >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
              AND attempt_count >= ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$ip, $windowMinutes, $maxAttempts]);
        $windowStart = $stmt->fetchColumn();

        if ($windowStart) {
            $expiresAt = strtotime($windowStart) + ($windowMinutes * 60);
            $remaining = $expiresAt - time();
            return $remaining > 0 ? (int)ceil($remaining / 60) : 0;
        }
    } catch (PDOException $e) {
        // Ignore
    }
    return 0;
}

