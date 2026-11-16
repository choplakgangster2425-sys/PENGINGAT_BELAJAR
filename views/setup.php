<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup Admin — Mading Bulanan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="auth-page">
  <main class="auth-card">
    <a class="auth-back hand" href="login.php">Mading Bulanan</a>
    <p class="eyebrow auth-eyebrow">Setup satu kali</p>
    <h1 class="hand auth-title">Buat admin pertama</h1>
    <?php if ($flashMessage !== ''): ?><p class="flash-message" role="status"><?= h($flashMessage) ?></p><?php endif; ?>
    <?php if ($setupError !== null): ?><p class="auth-error" role="alert"><?= h($setupError) ?></p><?php endif; ?>
    <?php if ($setupComplete): ?>
      <p class="auth-copy">Admin sudah dibuat. Halaman setup ini tidak dapat digunakan lagi.</p>
      <a class="form-button button-primary auth-submit" href="login.php">Lanjut ke login</a>
    <?php else: ?>
      <p class="auth-copy">Pilih username dan kata sandi yang kuat. Setelah admin dibuat, setup ini otomatis ditutup.</p>
      <form class="event-form auth-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
        <label><span class="field-label">Nama admin</span><input class="field" name="name" type="text" maxlength="70" autocomplete="name" required></label>
        <label><span class="field-label">Username</span><input class="field" name="username" type="text" minlength="3" maxlength="30" autocomplete="username" required></label>
        <label><span class="field-label">Kata sandi</span><input class="field" name="password" type="password" minlength="8" autocomplete="new-password" required><span class="auth-hint">Minimal 8 karakter</span></label>
        <label><span class="field-label">Ulangi kata sandi</span><input class="field" name="password_confirm" type="password" minlength="8" autocomplete="new-password" required></label>
        <button class="form-button button-primary auth-submit" type="submit">Buat admin</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
