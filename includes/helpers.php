<?php
declare(strict_types=1);

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirectToMonth(int $year, int $month, string $extra = ''): void
{
    $query = http_build_query(['year' => $year, 'month' => $month]);
    header('Location: index.php?' . $query . ($extra !== '' ? '&' . $extra : ''));
    exit;
}

function validIntegerInput(string $key, int $minimum, int $maximum, int $fallback): int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    return $value !== false && $value !== null && $value >= $minimum && $value <= $maximum
        ? $value
        : $fallback;
}

function daysInMonth(int $year, int $month): int
{
    return (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
}

function parseIsoDate(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
        return null;
    }
    return $date;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function takeFlash(): string
{
    $message = (string) ($_SESSION['flash'] ?? '');
    unset($_SESSION['flash']);
    return $message;
}

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        setFlash('Silakan masuk terlebih dahulu.');
        header('Location: login.php');
        exit;
    }
}

function refreshUser(PDO $db): array
{
    $statement = $db->prepare('SELECT id, name, username, role, status FROM users WHERE id = ? LIMIT 1');
    $statement->execute([(int) $_SESSION['user_id']]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['status'] !== 'active') {
        $_SESSION = [];
        session_regenerate_id(true);
        setFlash('Akun tidak aktif atau sesi Anda telah berakhir.');
        header('Location: login.php');
        exit;
    }
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    return $user;
}
