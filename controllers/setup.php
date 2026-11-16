<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$setupError = $dbError;
$setupComplete = false;

if ($db instanceof PDO && $setupError === null) {
    $adminCount = (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
    $setupComplete = $adminCount > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$setupComplete && $db instanceof PDO && $setupError === null) {
    if (!verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $setupError = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);

        if ($name === '' || $nameLength > 70 || !preg_match('/^[a-z0-9._-]{3,30}$/', $username)) {
            $setupError = 'Nama atau username tidak valid. Username 3–30 karakter.';
        } elseif (strlen($password) < 8) {
            $setupError = 'Kata sandi admin harus memiliki minimal 8 karakter.';
        } elseif ($password !== $passwordConfirm) {
            $setupError = 'Konfirmasi kata sandi tidak sama.';
        } else {
            try {
                $lock = (int) $db->query('SELECT GET_LOCK("mading_bulanan_admin_setup", 5)')->fetchColumn();
                if ($lock !== 1) {
                    throw new RuntimeException('Setup admin sedang digunakan. Coba kembali.');
                }
                $adminCount = (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
                if ($adminCount > 0) {
                    $setupComplete = true;
                } else {
                    $statement = $db->prepare('INSERT INTO users (name, username, password_hash, role) VALUES (?, ?, ?, "admin")');
                    $statement->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
                    setFlash('Admin pertama berhasil dibuat. Silakan masuk.');
                    header('Location: login.php');
                    exit;
                }
            } catch (Throwable $error) {
                error_log('Mading admin setup error: ' . $error->getMessage());
                $setupError = $error instanceof RuntimeException
                    ? $error->getMessage()
                    : 'Setup admin gagal. Periksa apakah username sudah digunakan.';
            } finally {
                if (isset($lock) && $lock === 1) {
                    $db->query('SELECT RELEASE_LOCK("mading_bulanan_admin_setup")');
                }
            }
        }
    }
}

$csrf = csrfToken();
$flashMessage = takeFlash();
