<?php
/**
 * ASTPMS — Application Configuration
 * Core constants and settings for the entire system
 */

// ─── Application ─────────────────────────────────────────────
define('APP_NAME',       'ASTPMS');
define('APP_FULL_NAME',  'Advanced Student Training & Placement Management System');
define('APP_VERSION',    '2.0.0');
define('APP_URL',        'http://localhost/project/internship%20project');

// ─── Environment ─────────────────────────────────────────────
define('APP_ENV', 'development'); // 'development' | 'production'
define('DEBUG_MODE', APP_ENV === 'development');

// ─── Paths ────────────────────────────────────────────────────
define('BASE_PATH',       dirname(__DIR__));
define('UPLOAD_PATH',     BASE_PATH . '/uploads');
define('RESUME_PATH',     UPLOAD_PATH . '/resumes');
define('PHOTO_PATH',      UPLOAD_PATH . '/photos');
define('CERTIFICATE_PATH', BASE_PATH . '/certificates');
define('REPORT_PATH',     UPLOAD_PATH . '/reports');

// ─── Upload Limits ────────────────────────────────────────────
define('MAX_RESUME_SIZE',  5 * 1024 * 1024);  // 5 MB
define('MAX_PHOTO_SIZE',   2 * 1024 * 1024);  // 2 MB
define('ALLOWED_RESUME_TYPES', ['application/pdf']);
define('ALLOWED_PHOTO_TYPES',  ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);

// ─── Session ─────────────────────────────────────────────────
define('SESSION_LIFETIME',   7200);   // 2 hours
define('REMEMBER_ME_DAYS',   30);     // 30 days

// ─── Security ────────────────────────────────────────────────
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES',    15);
define('OTP_EXPIRY_MINUTES', 10);
define('TOKEN_EXPIRY_MINUTES', 15);
define('BCRYPT_COST',        12);

// ─── Error Reporting ─────────────────────────────────────────
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ─── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Asia/Kolkata');
