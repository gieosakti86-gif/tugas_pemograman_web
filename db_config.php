<?php
// Database configuration untuk Maria DB (Laragon)
// PDO Driver: mysql (kompatibel penuh dengan Maria DB 10.5+)
// Pastikan Maria DB service sudah berjalan di Laragon

define('DB_HOST', '127.0.0.1');      // Localhost
define('DB_PORT', 3306);              // Port default Maria DB
define('DB_NAME', 'edocsmart');       // Nama database
define('DB_USER', 'root');            // User default Laragon
define('DB_PASS', '');                // Password kosong (default Laragon)

define('UPLOAD_BASE', __DIR__ . '/uploads');
define('DOC_UPLOAD_DIR', UPLOAD_BASE . '/documents');
define('SIGN_UPLOAD_DIR', UPLOAD_BASE . '/signatures');

foreach ([UPLOAD_BASE, DOC_UPLOAD_DIR, SIGN_UPLOAD_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function getDbConnection()
{
    static $pdo;
    if (!$pdo) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

function ensureUserAuthenticated()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login.']);
        exit;
    }
}

function sanitize($value)
{
    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function logError($message, $context = [])
{
    $logFile = __DIR__ . '/logs/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
    $entry = "[$timestamp] $message$contextStr\n";
    
    file_put_contents($logFile, $entry, FILE_APPEND);
}

