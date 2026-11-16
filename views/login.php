<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk — Mading Bulanan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="auth-page">
  <main class="auth-card">
    <a class="auth-back hand" href="index.php">Mading Bulanan</a>
    <p class="eyebrow auth-eyebrow">Senang bertemu lagi</p>
    <h1 class="hand auth-title">Masuk ke mading</h1>
    <p class="auth-copy">Login untuk melihat dan mengatur kegiatanmu.</p>
    <?php if ($flashMessage !== ''): ?><p class="flash-message" role="status"><?= h($flashMessage) ?></p><?php endif; ?>
    <?php if ($authError !== null): ?><p class="auth-error" role="alert"><?= h($authError) ?></p><?php endif; ?>
    <form class="event-form auth-form" method="post">
      <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
      <label><span class="field-label">Username</span><input class="field" name="username" type="text" maxlength="30" autocomplete="username" required autofocus></label>
      <label><span class="field-label">Kata sandi</span><input class="field" name="password" type="password" autocomplete="current-password" required></label>
      <button class="form-button button-primary auth-submit" type="submit">Masuk</button>
    </form>
    <p class="auth-footnote">Belum punya akun? <a href="register.php">Daftar sebagai pengguna</a></p>
  </main>
</body>
</html>
