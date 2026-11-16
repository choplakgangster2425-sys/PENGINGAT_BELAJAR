<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$authError = $dbError;
$authAction = $authAction ?? 'login';

if ($authAction === 'logout') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Permintaan tidak valid.');
    }
    $_SESSION = [];
    session_regenerate_id(true);
    setFlash('Anda telah keluar.');
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db instanceof PDO && $authError === null) {
    if (!verifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $authError = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif ($authAction === 'login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $statement = $db->prepare('SELECT id, name, username, password_hash, role FROM users WHERE username = ? AND status = "active" LIMIT 1');
        $statement->execute([$username]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }
        $authError = 'Nama pengguna atau kata sandi tidak cocok.';
    } elseif ($authAction === 'register') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
        $usernameLength = strlen($username);

        if ($name === '' || $nameLength > 70 || $usernameLength < 3 || $usernameLength > 30 || !preg_match('/^[a-z0-9._-]+$/', $username)) {
            $authError = 'Nama atau nama pengguna tidak valid. Username 3–30 karakter (huruf, angka, titik, garis bawah, atau tanda hubung).';
        } elseif (strlen($password) < 8) {
            $authError = 'Kata sandi harus memiliki minimal 8 karakter.';
        } elseif ($password !== $passwordConfirm) {
            $authError = 'Konfirmasi kata sandi tidak sama.';
        } else {
            try {
                $statement = $db->prepare('INSERT INTO users (name, username, password_hash, role) VALUES (?, ?, ?, "user")');
                $statement->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
                setFlash('Akun berhasil dibuat. Silakan masuk.');
                header('Location: login.php');
                exit;
            } catch (PDOException $error) {
                if ($error->getCode() === '23000') {
                    $authError = 'Nama pengguna tersebut sudah digunakan.';
                } else {
                    error_log('Mading registration error: ' . $error->getMessage());
                    $authError = 'Pendaftaran tidak berhasil. Silakan coba kembali.';
                }
            }
        }
    }
}

if (!empty($_SESSION['user_id']) && $authAction !== 'setup') {
    header('Location: index.php');
    exit;
}

$flashMessage = takeFlash();
