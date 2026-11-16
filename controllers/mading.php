<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUser = $db instanceof PDO && $dbError === null
    ? refreshUser($db)
    : [
        'id' => (int) $_SESSION['user_id'],
        'name' => (string) ($_SESSION['user_name'] ?? 'Pengguna'),
        'username' => (string) ($_SESSION['username'] ?? ''),
        'role' => (string) ($_SESSION['role'] ?? 'user'),
        'status' => 'active',
    ];
$isAdmin = $currentUser['role'] === 'admin';
$csrf = csrfToken();

$now = new DateTimeImmutable();
$year = validIntegerInput('year', 1, 9999, (int) $now->format('Y'));
$month = validIntegerInput('month', 1, 12, (int) $now->format('n'));
$categories = ['Kuliah', 'Organisasi', 'Pribadi', 'Deadline', 'Lainnya'];
$infoTypes = ['Hari Libur / Tanggal Merah', 'Hari Peringatan', 'Pengumuman', 'Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db instanceof PDO && $dbError === null) {
    $postYear = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $postMonth = filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT);
    $year = $postYear !== false && $postYear !== null && $postYear >= 1 && $postYear <= 9999 ? $postYear : $year;
    $month = $postMonth !== false && $postMonth !== null && $postMonth >= 1 && $postMonth <= 12 ? $postMonth : $month;
    $daysInMonth = daysInMonth($year, $month);
    $day = max(1, min($daysInMonth, (int) ($_POST['day'] ?? 1)));
    $token = (string) ($_POST['csrf_token'] ?? '');
    $action = (string) ($_POST['action'] ?? '');

    if (!verifyCsrfToken($token)) {
        $_SESSION['flash'] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.';
        redirectToMonth($year, $month);
    }
    if (in_array($action, ['info_save', 'info_delete'], true) && !$isAdmin) {
        http_response_code(403);
        exit('Akses ditolak. Hanya admin yang dapat mengelola informasi kalender.');
    }

    try {
        if ($action === 'info_save') {
            $rawId = filter_input(INPUT_POST, 'info_id', FILTER_VALIDATE_INT);
            $infoId = $rawId !== false && $rawId !== null && $rawId > 0 ? $rawId : null;
            $dateValue = trim((string) ($_POST['info_date'] ?? ''));
            $infoDate = parseIsoDate($dateValue);
            $title = trim((string) ($_POST['info_title'] ?? ''));
            $type = (string) ($_POST['info_type'] ?? '');
            $details = trim((string) ($_POST['info_details'] ?? ''));
            $titleLength = function_exists('mb_strlen') ? mb_strlen($title, 'UTF-8') : strlen($title);
            $detailsLength = function_exists('mb_strlen') ? mb_strlen($details, 'UTF-8') : strlen($details);

            if (!$infoDate) {
                $_SESSION['flash'] = 'Tanggal informasi tidak valid.';
                redirectToMonth($year, $month, 'modal=' . ($infoId ? 'info_edit&id=' . $infoId : 'info') . '&date=' . urlencode($dateValue));
            }
            if ($title === '' || $titleLength > 100 || $detailsLength > 255 || !in_array($type, $infoTypes, true)) {
                $_SESSION['flash'] = 'Data informasi belum valid. Periksa judul, kategori, dan panjang keterangan.';
                redirectToMonth($year, $month, 'modal=' . ($infoId ? 'info_edit&id=' . $infoId : 'info') . '&date=' . $infoDate->format('Y-m-d'));
            }

            if ($infoId) {
                $statement = $db->prepare('UPDATE calendar_info SET event_date = ?, title = ?, info_type = ?, details = ? WHERE id = ?');
                $statement->execute([$infoDate->format('Y-m-d'), $title, $type, $details, $infoId]);
                if ($statement->rowCount() === 0) {
                    $check = $db->prepare('SELECT id FROM calendar_info WHERE id = ?');
                    $check->execute([$infoId]);
                    if (!$check->fetchColumn()) {
                        throw new RuntimeException('Informasi kalender tidak ditemukan untuk diperbarui.');
                    }
                }
                $_SESSION['flash'] = 'Informasi kalender berhasil diperbarui.';
            } else {
                $statement = $db->prepare(
                    'INSERT INTO calendar_info (created_by, event_date, title, info_type, details) VALUES (?, ?, ?, ?, ?)'
                );
                $statement->execute([$currentUser['id'], $infoDate->format('Y-m-d'), $title, $type, $details]);
                $_SESSION['flash'] = 'Informasi kalender berhasil ditambahkan.';
            }
            $year = (int) $infoDate->format('Y');
            $month = (int) $infoDate->format('n');
        } elseif ($action === 'info_delete') {
            $rawId = filter_input(INPUT_POST, 'info_id', FILTER_VALIDATE_INT);
            $infoId = $rawId !== false && $rawId !== null && $rawId > 0 ? $rawId : null;
            if (!$infoId) {
                throw new RuntimeException('ID informasi kalender tidak valid.');
            }
            $statement = $db->prepare('DELETE FROM calendar_info WHERE id = ?');
            $statement->execute([$infoId]);
            if ($statement->rowCount() === 0) {
                throw new RuntimeException('Informasi kalender tidak ditemukan.');
            }
            $_SESSION['flash'] = 'Informasi kalender berhasil dihapus.';
        } elseif ($action === 'save') {
            $rawId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id = $rawId !== false && $rawId !== null && $rawId > 0 ? $rawId : null;
            $title = trim((string) ($_POST['title'] ?? ''));
            $category = (string) ($_POST['category'] ?? 'Lainnya');
            $time = trim((string) ($_POST['time'] ?? ''));
            $note = trim((string) ($_POST['note'] ?? ''));
            $titleLength = function_exists('mb_strlen') ? mb_strlen($title, 'UTF-8') : strlen($title);
            $noteLength = function_exists('mb_strlen') ? mb_strlen($note, 'UTF-8') : strlen($note);
            $validTime = $time === '' || preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time);

            if ($title === '' || $titleLength > 70 || $noteLength > 180 || !in_array($category, $categories, true) || !$validTime) {
                $_SESSION['flash'] = 'Data belum valid. Periksa judul, kategori, jam, dan panjang catatan.';
                redirectToMonth($year, $month, 'modal=' . ($id ? 'edit' : 'add') . '&day=' . $day . ($id ? '&id=' . $id : ''));
            }

            $timeValue = $time === '' ? null : $time . ':00';
            if ($id) {
                $ownerCondition = $isAdmin ? '' : ' AND user_id = ?';
                $statement = $db->prepare(
                    'UPDATE mading_events SET event_day = ?, title = ?, category = ?, event_time = ?, note = ?
                     WHERE id = ? AND event_year = ? AND event_month = ?' . $ownerCondition
                );
                $parameters = [$day, $title, $category, $timeValue, $note, $id, $year, $month];
                if (!$isAdmin) {
                    $parameters[] = $currentUser['id'];
                }
                $statement->execute($parameters);
                if ($statement->rowCount() === 0) {
                    $check = $db->prepare(
                        'SELECT id FROM mading_events WHERE id = ? AND event_year = ? AND event_month = ?' . $ownerCondition
                    );
                    $checkParameters = [$id, $year, $month];
                    if (!$isAdmin) {
                        $checkParameters[] = $currentUser['id'];
                    }
                    $check->execute($checkParameters);
                    if (!$check->fetchColumn()) {
                        throw new RuntimeException('Kegiatan tidak ditemukan untuk diperbarui.');
                    }
                }
                $_SESSION['flash'] = 'Perubahan kegiatan berhasil disimpan.';
            } else {
                $statement = $db->prepare(
                    'INSERT INTO mading_events (user_id, event_year, event_month, event_day, title, category, event_time, note)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $statement->execute([$currentUser['id'], $year, $month, $day, $title, $category, $timeValue, $note]);
                $_SESSION['flash'] = 'Kegiatan berhasil ditambahkan.';
            }
        } elseif ($action === 'toggle' || $action === 'delete') {
            $rawId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $id = $rawId !== false && $rawId !== null && $rawId > 0 ? $rawId : null;
            if (!$id) {
                throw new RuntimeException('ID kegiatan tidak valid.');
            }
            if ($action === 'toggle') {
                $ownerCondition = $isAdmin ? '' : ' AND user_id = ?';
                $statement = $db->prepare(
                    'UPDATE mading_events SET is_done = IF(is_done = 1, 0, 1)
                     WHERE id = ? AND event_year = ? AND event_month = ?' . $ownerCondition
                );
                $parameters = [$id, $year, $month];
                if (!$isAdmin) {
                    $parameters[] = $currentUser['id'];
                }
                $statement->execute($parameters);
                $_SESSION['flash'] = 'Status kegiatan berhasil diperbarui.';
            } else {
                $ownerCondition = $isAdmin ? '' : ' AND user_id = ?';
                $statement = $db->prepare('DELETE FROM mading_events WHERE id = ? AND event_year = ? AND event_month = ?' . $ownerCondition);
                $parameters = [$id, $year, $month];
                if (!$isAdmin) {
                    $parameters[] = $currentUser['id'];
                }
                $statement->execute($parameters);
                $_SESSION['flash'] = 'Sticky note berhasil dihapus.';
            }
            if ($statement->rowCount() === 0) {
                throw new RuntimeException('Kegiatan tidak ditemukan.');
            }
        } else {
            throw new RuntimeException('Aksi formulir tidak dikenal.');
        }
    } catch (Throwable $error) {
        error_log('Mading action error: ' . $error->getMessage());
        $_SESSION['flash'] = 'Perubahan tidak berhasil diproses. Silakan coba kembali.';
    }
    redirectToMonth($year, $month);
}

$monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$weekdays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$daysInMonth = daysInMonth($year, $month);
$categoryStyles = [
    'Kuliah' => ['#f7df83', '#51441c'],
    'Organisasi' => ['#f4b5c2', '#5b3340'],
    'Pribadi' => ['#b9dfb1', '#315039'],
    'Deadline' => ['#f2a487', '#5b3427'],
    'Lainnya' => ['#c8b9e8', '#44375e'],
];
$infoTypeStyles = [
    'Hari Libur / Tanggal Merah' => ['#f5d6d1', '#9d342c'],
    'Hari Peringatan' => ['#e7dff5', '#594481'],
    'Pengumuman' => ['#dcebf1', '#315e73'],
    'Lainnya' => ['#e9ead8', '#53603e'],
];
$monthEvents = [];
$calendarInfoByDay = [];
$eventById = null;
if ($db instanceof PDO && $dbError === null) {
    $ownerCondition = $isAdmin ? '' : ' AND e.user_id = ?';
    $statement = $db->prepare(
        'SELECT e.*, u.name AS owner_name
         FROM mading_events e LEFT JOIN users u ON u.id = e.user_id
         WHERE e.event_year = ? AND e.event_month = ?' . $ownerCondition .
        ' ORDER BY e.event_day, e.event_time, e.id'
    );
    $parameters = [$year, $month];
    if (!$isAdmin) {
        $parameters[] = $currentUser['id'];
    }
    $statement->execute($parameters);
    $monthEvents = $statement->fetchAll(PDO::FETCH_ASSOC);

    $monthStart = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    $nextMonthStart = $monthStart->modify('first day of next month');
    $infoStatement = $db->prepare('SELECT * FROM calendar_info WHERE event_date >= ? AND event_date < ? ORDER BY event_date, id');
    $infoStatement->execute([$monthStart->format('Y-m-d'), $nextMonthStart->format('Y-m-d')]);
    foreach ($infoStatement->fetchAll(PDO::FETCH_ASSOC) as $calendarInfo) {
        $infoDay = (int) substr($calendarInfo['event_date'], 8, 2);
        $calendarInfoByDay[$infoDay][] = $calendarInfo;
    }
}

$modal = (string) ($_GET['modal'] ?? '');
$infoById = null;
$dayFallback = min((int) $now->format('j'), $daysInMonth);
$day = validIntegerInput('day', 1, $daysInMonth, $dayFallback);
if ($modal === 'info_edit' && $isAdmin && isset($_GET['id']) && $db instanceof PDO && $dbError === null) {
    $infoStatement = $db->prepare('SELECT * FROM calendar_info WHERE id = ?');
    $infoStatement->execute([(int) $_GET['id']]);
    $infoById = $infoStatement->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($infoById) {
        $infoDateValue = $infoById['event_date'];
    } else {
        $modal = '';
        $_SESSION['flash'] = 'Informasi kalender yang dipilih tidak ditemukan.';
    }
}
if ($modal === 'edit' && isset($_GET['id']) && $db instanceof PDO && $dbError === null) {
    $ownerCondition = $isAdmin ? '' : ' AND user_id = ?';
    $editStatement = $db->prepare(
        'SELECT * FROM mading_events WHERE id = ? AND event_year = ? AND event_month = ?' . $ownerCondition
    );
    $parameters = [(int) $_GET['id'], $year, $month];
    if (!$isAdmin) {
        $parameters[] = $currentUser['id'];
    }
    $editStatement->execute($parameters);
    $eventById = $editStatement->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($eventById) {
        $day = (int) $eventById['event_day'];
    } else {
        $modal = '';
        $_SESSION['flash'] = 'Kegiatan yang dipilih tidak ditemukan.';
    }
}

$isModalOpen = in_array($modal, ['add', 'edit'], true) && $dbError === null;
$defaultInfoDate = $year === (int) $now->format('Y') && $month === (int) $now->format('n')
    ? $now->format('Y-m-d')
    : sprintf('%04d-%02d-01', $year, $month);
$requestedInfoDate = parseIsoDate((string) ($_GET['date'] ?? ''));
if (!isset($infoDateValue)) {
    $infoDateValue = $requestedInfoDate
        && (int) $requestedInfoDate->format('Y') === $year
        && (int) $requestedInfoDate->format('n') === $month
        ? $requestedInfoDate->format('Y-m-d')
        : $defaultInfoDate;
}
$isInfoModalOpen = in_array($modal, ['info', 'info_edit'], true) && $isAdmin && $dbError === null;
$flashMessage = (string) ($_SESSION['flash'] ?? '');
unset($_SESSION['flash']);

$previousMonth = $month === 1 ? 12 : $month - 1;
$previousYear = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear = $month === 12 ? $year + 1 : $year;
$doneCount = count(array_filter($monthEvents, static function ($item) {
    return (int) $item['is_done'] === 1;
}));
$monthQuery = ['year' => $year, 'month' => $month];
$todayQuery = ['year' => (int) $now->format('Y'), 'month' => (int) $now->format('n')];
$dialogDate = $weekdays[(int) gmdate('w', gmmktime(0, 0, 0, $month, $day, $year))] . ', ' . $day . ' ' . $monthNames[$month - 1] . ' ' . $year;
