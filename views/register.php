<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar — Mading Bulanan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="auth-page">
  <main class="auth-card">
    <a class="auth-back hand" href="login.php">Kembali ke login</a>
    <p class="eyebrow auth-eyebrow">Buat ruang rencanamu</p>
    <h1 class="hand auth-title">Daftar pengguna</h1>
    <p class="auth-copy">Kegiatan yang kamu buat hanya terlihat dan dapat dikelola oleh akunmu.</p>
    <?php if ($authError !== null): ?><p class="auth-error" role="alert"><?= h($authError) ?></p><?php endif; ?>
    <form class="event-form auth-form" method="post">
      <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
      <label><span class="field-label">Nama</span><input class="field" name="name" type="text" maxlength="70" autocomplete="name" required></label>
      <label><span class="field-label">Username</span><input class="field" name="username" type="text" minlength="3" maxlength="30" autocomplete="username" required><span class="auth-hint">3–30 karakter: huruf, angka, titik, _ atau -</span></label>
      <label><span class="field-label">Kata sandi</span><input class="field" name="password" type="password" minlength="8" autocomplete="new-password" required><span class="auth-hint">Minimal 8 karakter</span></label>
      <label><span class="field-label">Ulangi kata sandi</span><input class="field" name="password_confirm" type="password" minlength="8" autocomplete="new-password" required></label>
      <button class="form-button button-primary auth-submit" type="submit">Buat akun</button>
    </form>
    <p class="auth-footnote">Sudah punya akun? <a href="login.php">Masuk</a></p>
  </main>
</body>
</html>
