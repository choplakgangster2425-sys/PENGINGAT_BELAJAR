<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUser = $db instanceof PDO && $dbError === null ? refreshUser($db) : null;
$isAdmin = $currentUser !== null && $currentUser['role'] === 'admin';
if ($db instanceof PDO && $dbError === null && !$isAdmin) {
    http_response_code(403);
    exit('Akses ditolak. Halaman ini hanya untuk admin.');
}

$flashMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db instanceof PDO && $dbError === null && $isAdmin) {
    $token = (string) ($_POST['csrf_token'] ?? '');
    $rawId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $id = $rawId !== false && $rawId !== null && $rawId > 0 ? $rawId : null;
    if (!verifyCsrfToken($token)) {
        setFlash('Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.');
    } elseif (!$id) {
        setFlash('ID pengguna tidak valid.');
    } else {
        $statement = $db->prepare('UPDATE users SET status = IF(status = "active", "disabled", "active") WHERE id = ? AND role = "user"');
        $statement->execute([$id]);
        setFlash($statement->rowCount() > 0 ? 'Status akun pengguna diperbarui.' : 'Pengguna tidak ditemukan.');
    }
    header('Location: users.php');
    exit;
}

$users = [];
if ($db instanceof PDO && $dbError === null && $isAdmin) {
    $users = $db->query('SELECT id, name, username, status, created_at FROM users WHERE role = "user" ORDER BY created_at DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
}
$csrf = csrfToken();
$flashMessage = takeFlash();
