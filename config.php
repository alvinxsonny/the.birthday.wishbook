<?php
/**
 * The Birthday Wishbook — Configuration
 * Dual-environment: localhost + InfinityFree
 */

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Session config (only set before session starts)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
}

// App constants
define('APP_NAME', 'The Birthday Wishbook');
define('APP_TAGLINE', 'Your Birthday, Your Wishlist, One Link');

// Environment detection
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = in_array($host, ['localhost', '127.0.0.1']) || str_starts_with($host, 'localhost:') || str_starts_with($host, '127.0.0.1:');

// Load environment credentials from env.php (ignored by Git)
$env = [];
if (file_exists(__DIR__ . '/env.php')) {
    $env = include __DIR__ . '/env.php';
}

if ($isLocal) {
    // ─── LOCAL DEVELOPMENT ───
    define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
    define('DB_NAME', $env['DB_NAME'] ?? 'birthday_wishbook');
    define('DB_USER', $env['DB_USER'] ?? 'root');
    define('DB_PASS', $env['DB_PASS'] ?? '19241203');
    define('ENVIRONMENT', 'development');
} else {
    // ─── INFINITYFREE PRODUCTION ───
    define('DB_HOST', $env['DB_HOST'] ?? 'sql309.infinityfree.com');
    define('DB_NAME', $env['DB_NAME'] ?? 'if0_42302691_birthday_wishbook');
    define('DB_USER', $env['DB_USER'] ?? 'if0_42302691');
    define('DB_PASS', $env['DB_PASS'] ?? 'OdVpyWSGqNr7v');
    define('ENVIRONMENT', 'production');
}

// Base URL detection
// Note: HTTP_HOST already includes the port (e.g. localhost:8000), so don't append it again
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $protocol . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
