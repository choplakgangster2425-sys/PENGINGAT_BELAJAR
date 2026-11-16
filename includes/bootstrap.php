<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

$db = null;
$dbError = null;
try {
    $db = connectDatabase();
} catch (Throwable $error) {
    error_log('Mading database error: ' . $error->getMessage());
    $dbError = 'Database belum dapat diakses. Pastikan MySQL XAMPP aktif dan konfigurasi koneksi sudah benar.';
}
