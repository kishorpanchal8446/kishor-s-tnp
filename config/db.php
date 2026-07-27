<?php
/**
 * ASTPMS — Database Configuration
 * Updated to connect to the new 'astpms' database on port 3307
 */

if (!defined('DB_CONNECTED')) {
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3307');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'astpms');


    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]
        );
        define('DB_CONNECTED', true);
    } catch (PDOException $e) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
        die('<div style="font-family:sans-serif;text-align:center;padding:3rem;color:#dc2626;">
            <h2>⚠️ Database Connection Error</h2>
            <p>Please ensure MySQL is running on port 3307 and the <strong>astpms</strong> database exists.</p>
            <p><small>Import <code>astpms.sql</code> via phpMyAdmin to set up the database.</small></p>
        </div>');
    }
}

/**
 * Helper: Log activity to activity_logs table
 */
function logActivity(PDO $pdo, ?int $userId, string $role, string $action, ?string $description = null, ?string $module = null): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, user_role, action, description, ip_address, user_agent, module)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $role,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            $module,
        ]);
    } catch (PDOException $e) {
        // Silently fail — activity logging should never break the app
    }
}

/**
 * Helper: Get a setting value from the settings table
 */
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        $cache[$key] = $val !== false ? $val : $default;
    } catch (PDOException $e) {
        $cache[$key] = $default;
    }
    return $cache[$key];
}
