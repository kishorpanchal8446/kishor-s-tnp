<?php
/**
 * ASTPMS — PHPMailer Configuration
 *
 * Credentials are loaded from the project-root .env file so they are never
 * hard-coded in source code.
 *
 * HOW TO SETUP (Gmail):
 * 1. Copy .env.example → .env  (in the project root)
 * 2. Go to https://myaccount.google.com/security
 * 3. Enable 2-Step Verification
 * 4. Search "App Passwords" → generate one for "Mail"
 * 5. Paste the 16-character App Password into MAIL_PASS in your .env
 *
 * For local testing without a real SMTP server, use Mailtrap:
 *   https://mailtrap.io — free, emails are captured in a sandbox inbox.
 */

// ─── Load .env ────────────────────────────────────────────────────────────────
if (!function_exists('loadDotEnv')) {
    function loadDotEnv(string $path): void {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue; // skip comments
            if (!str_contains($line, '='))         continue;
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if ($key === '') continue;
            // Only set if not already defined in the environment
            if (!isset($_ENV[$key]) && !getenv($key)) {
                $_ENV[$key]  = $val;
                putenv("{$key}={$val}");
            }
        }
    }
}

// Load the .env file from the project root (one directory above /config)
loadDotEnv(dirname(__DIR__) . '/.env');

// ─── SMTP Settings (from .env with safe fallbacks) ────────────────────────────
if (!defined('MAIL_HOST')) {
    define('MAIL_HOST',       getenv('MAIL_HOST')       ?: 'smtp.gmail.com');
    define('MAIL_PORT',  (int)(getenv('MAIL_PORT')       ?: 587));
    define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');
    define('MAIL_USER',       getenv('MAIL_USER')       ?: '');
    define('MAIL_PASS',       getenv('MAIL_PASS')       ?: '');
    define('MAIL_FROM_NAME',  getenv('MAIL_FROM_NAME')  ?: 'ASTPMS T&P Portal');
    define('MAIL_FROM_ADDR',  getenv('MAIL_FROM_ADDR')  ?: (getenv('MAIL_USER') ?: ''));
}

// ─── PHPMailer Bootstrap Helper ───────────────────────────────────────────────
function getMailer(): object|false {
    $srcPath = dirname(__DIR__) . '/vendor/phpmailer/src/';

    if (!file_exists($srcPath . 'PHPMailer.php')) {
        error_log('[ASTPMS] PHPMailer not found at: ' . $srcPath);
        return false;
    }

    require_once $srcPath . 'Exception.php';
    require_once $srcPath . 'PHPMailer.php';
    require_once $srcPath . 'SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port       = MAIL_PORT;
    $mail->setFrom(MAIL_FROM_ADDR, MAIL_FROM_NAME);
    $mail->isHTML(true);
    $mail->CharSet    = 'UTF-8';

    // Disable SSL cert verification in development (remove in production)
    if (defined('APP_ENV') && APP_ENV === 'development') {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];
    }

    return $mail;
}

/**
 * Send an HTML email using PHPMailer.
 *
 * @param string $toEmail  Recipient email address
 * @param string $toName   Recipient display name
 * @param string $subject  Email subject line
 * @param string $htmlBody HTML email body
 * @return true|string     true on success, error message string on failure
 */
function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
    if (empty(MAIL_USER) || empty(MAIL_PASS)) {
        $msg = 'SMTP credentials are not configured. Please set MAIL_USER and MAIL_PASS in your .env file.';
        error_log('[ASTPMS Mail] ' . $msg);
        return $msg;
    }

    try {
        $mail = getMailer();
        if (!$mail) return 'PHPMailer library not installed. Check vendor/phpmailer/src/.';

        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $htmlBody));
        $mail->send();
        error_log('[ASTPMS Mail] Sent to: ' . $toEmail . ' | Subject: ' . $subject);
        return true;
    } catch (\Exception $e) {
        error_log('[ASTPMS Mail] Error sending to ' . $toEmail . ': ' . $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Professional HTML email template wrapper.
 *
 * @param string $title   Heading shown inside the email card
 * @param string $body    HTML body content
 * @param string $btnText CTA button text (optional)
 * @param string $btnUrl  CTA button URL (optional)
 */
function emailTemplate(string $title, string $body, string $btnText = '', string $btnUrl = ''): string {
    $btn = '';
    if ($btnText && $btnUrl) {
        $safeUrl = htmlspecialchars($btnUrl, ENT_QUOTES, 'UTF-8');
        $btn = "
        <div style='text-align:center;margin:32px 0;'>
            <a href='{$safeUrl}'
               style='background:linear-gradient(135deg,#1E3A8A,#2563EB);
                      color:#ffffff;
                      padding:14px 36px;
                      border-radius:10px;
                      text-decoration:none;
                      font-weight:700;
                      font-size:15px;
                      display:inline-block;
                      letter-spacing:0.3px;
                      box-shadow:0 4px 16px rgba(37,99,235,0.45);'>
                {$btnText}
            </a>
        </div>
        <p style='text-align:center;font-size:12px;color:#94A3B8;margin:0;'>
            Button not working? Copy and paste this URL into your browser:<br>
            <a href='{$safeUrl}' style='color:#60A5FA;word-break:break-all;'>{$safeUrl}</a>
        </p>";
    }

    $year = date('Y');
    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1.0'>
    <title>{$title}</title>
</head>
<body style='margin:0;padding:0;background:#F1F5F9;font-family:Arial,Helvetica,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#F1F5F9;padding:40px 0;'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0'
       style='background:#ffffff;border-radius:16px;overflow:hidden;
              box-shadow:0 4px 24px rgba(0,0,0,0.10);max-width:600px;'>

    <!-- Header -->
    <tr><td style='background:linear-gradient(135deg,#1E3A8A 0%,#2563EB 100%);
                   padding:32px 40px;text-align:center;'>
        <table width='100%'><tr>
            <td align='center'>
                <div style='display:inline-block;background:rgba(255,255,255,0.15);
                            border-radius:14px;padding:10px 18px;margin-bottom:12px;'>
                    <span style='font-size:28px;'>🎓</span>
                </div>
                <h1 style='color:#ffffff;margin:0;font-size:22px;font-weight:800;
                           letter-spacing:-0.5px;'>ASTPMS Portal</h1>
                <p style='color:rgba(255,255,255,0.75);margin:6px 0 0;font-size:13px;
                          letter-spacing:0.5px;'>TRAINING &amp; PLACEMENT MANAGEMENT SYSTEM</p>
            </td>
        </tr></table>
    </td></tr>

    <!-- Body -->
    <tr><td style='padding:40px;'>
        <h2 style='color:#1E3A8A;font-size:20px;margin:0 0 16px;font-weight:700;'>{$title}</h2>
        <div style='color:#475569;font-size:15px;line-height:1.75;'>{$body}</div>
        {$btn}
        <hr style='border:none;border-top:1px solid #E2E8F0;margin:32px 0;'>
        <p style='color:#94A3B8;font-size:12px;margin:0;line-height:1.6;'>
            This is an automated email from ASTPMS. Please do not reply directly to this message.
            If you have questions, contact your T&amp;P Cell at
            <a href='mailto:placement@astpms.edu' style='color:#60A5FA;'>placement@astpms.edu</a>.
        </p>
    </td></tr>

    <!-- Footer -->
    <tr><td style='background:#F8FAFC;padding:20px 40px;text-align:center;
                   border-top:1px solid #E2E8F0;'>
        <p style='color:#94A3B8;font-size:12px;margin:0;'>
            &copy; {$year} ASTPMS &mdash; Training &amp; Placement Management System.
            All rights reserved.
        </p>
    </td></tr>

</table>
</td></tr>
</table>
</body>
</html>";
}
