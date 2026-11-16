<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Pengguna — Mading Bulanan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="auth-page">
  <main class="users-panel">
    <header class="users-header">
      <div>
        <p class="eyebrow auth-eyebrow">Area admin</p>
        <h1 class="hand auth-title">Kelola pengguna</h1>
      </div>
      <a class="form-button button-muted" href="index.php">Kembali ke kalender</a>
    </header>
    <p class="auth-copy">Admin dapat menonaktifkan atau mengaktifkan kembali akun pengguna. Kegiatan pengguna tetap tersimpan.</p>
    <?php if ($flashMessage !== ''): ?><p class="flash-message" role="status"><?= h($flashMessage) ?></p><?php endif; ?>
    <?php if ($dbError !== null): ?><p class="auth-error" role="alert"><?= h($dbError) ?></p><?php endif; ?>
    <?php if ($users): ?>
      <div class="users-list">
        <?php foreach ($users as $user): ?>
          <article class="user-row">
            <div class="user-info">
              <strong><?= h($user['name']) ?></strong>
              <span>@<?= h($user['username']) ?></span>
              <span class="user-status <?= $user['status'] === 'active' ? 'status-active' : 'status-disabled' ?>"><?= $user['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span>
            </div>
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
              <input type="hidden" name="id" value="<?= h($user['id']) ?>">
              <button class="form-button <?= $user['status'] === 'active' ? 'button-muted' : 'button-primary' ?>" type="submit"><?= $user['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?></button>
            </form>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty-users">Belum ada akun pengguna terdaftar.</p>
    <?php endif; ?>
  </main>
</body>
</html>
