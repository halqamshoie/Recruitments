<?php

// Determine if we are running in a subfolder (e.g. on production) or root (local)
// You can manually set this or try to auto-detect.
// For the specific deployment requested:
// Local: /  (or empty)
// Production: /recruitments/

// Auto-detection attempt (can be overridden)
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /recruitments/index.php or /index.php
$dirName = dirname($scriptName);     // e.g., /recruitments or /

// Ensure trailing slash for BASE_URL if it's not root
if ($dirName === '/' || $dirName === '\\') {
    define('BASE_URL', '');
} else {
    define('BASE_URL', rtrim($dirName, '/\\'));
}

// Full URL for emails (e.g., http://your-domain.com/recruitments)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
define('APP_URL', $protocol . $host . BASE_URL);

// Helper to check if we are in production (simple check)
define('IS_PRODUCTION', strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false);
