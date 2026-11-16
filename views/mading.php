<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#183c37">
  <title>Mading Bulanan — Catatan Kecil, Rencana Besar</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=4">
</head>
<body>
  <main class="page-shell">
    <section class="board">
      <span class="top-pin" aria-hidden="true"></span>
      <header class="header-row">
        <div class="brand">
          <p class="eyebrow">Ruang kecil untuk rencana besar</p>
          <h1 class="hand brand-title">Mading Bulanan</h1>
          <p class="brand-copy">Tempelkan rencanamu, satu sticky note setiap langkah.</p>
        </div>
        <nav class="month-navigation" aria-label="Navigasi bulan">
          <a class="nav-link" href="?year=<?= h($previousYear) ?>&amp;month=<?= h($previousMonth) ?>" aria-label="Bulan sebelumnya">←</a>
          <div>
            <div class="hand month-name"><?= h($monthNames[$month - 1]) ?></div>
            <div class="month-year"><?= h($year) ?></div>
          </div>
          <a class="nav-link" href="?year=<?= h($nextYear) ?>&amp;month=<?= h($nextMonth) ?>" aria-label="Bulan berikutnya">→</a>
          <a class="today-link" href="?<?= h(http_build_query($todayQuery)) ?>">Hari ini</a>
        </nav>
      </header>

      <?php if ($flashMessage !== ''): ?>
        <p class="flash-message" role="status"><?= h($flashMessage) ?></p>
      <?php endif; ?>
      <?php if ($dbError !== null): ?>
        <div class="database-error" role="alert">
          <strong>Koneksi database bermasalah.</strong>
          <p><?= h($dbError) ?> Pengaturan koneksi: DB_HOST, DB_NAME, DB_USER, dan DB_PASS di config/database.php.</p>
        </div>
      <?php endif; ?>

      <div class="account-bar">
        <div class="account-details">
          <span>Masuk sebagai <strong><?= h($currentUser['name']) ?></strong></span>
          <span class="role-badge"><?= $isAdmin ? 'Admin' : 'Pengguna' ?></span>
        </div>
        <div class="account-links">
          <?php if ($isAdmin): ?>
            <a class="account-link" href="?<?= h(http_build_query($monthQuery + ['modal' => 'info', 'date' => $defaultInfoDate])) ?>">Tambah info kalender</a>
            <a class="account-link" href="users.php">Kelola pengguna</a>
          <?php endif; ?>
          <form class="logout-form" action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <button class="logout-button" type="submit">Keluar</button>
          </form>
        </div>
      </div>

      <div class="overview-row">
        <div class="legend" aria-label="Kategori kegiatan">
          <?php foreach ($categoryStyles as $category => $style): ?>
            <span class="legend-item"><span class="legend-dot" style="background:<?= h($style[0]) ?>"></span><?= h($category) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="summary">
          <span><?= count($monthEvents) ?> kegiatan</span><span class="summary-dot" aria-hidden="true"></span><span><?= $doneCount ?> selesai</span>
        </div>
      </div>

      <div class="weekday-row" aria-hidden="true">
        <?php foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $weekday): ?>
          <div class="weekday"><?= h($weekday) ?></div>
        <?php endforeach; ?>
      </div>
      <div class="calendar-grid" aria-label="Kalender kegiatan bulan <?= h($monthNames[$month - 1]) ?>">
        <?php for ($dayNumber = 1; $dayNumber <= $daysInMonth; $dayNumber++): ?>
          <?php
          $dayWeekday = $weekdays[(int) gmdate('w', gmmktime(0, 0, 0, $month, $dayNumber, $year))];
          $isToday = $year === (int) $now->format('Y') && $month === (int) $now->format('n') && $dayNumber === (int) $now->format('j');
          $isHoliday = count(array_filter($calendarInfoByDay[$dayNumber] ?? [], static function ($item) {
              return $item['info_type'] === 'Hari Libur / Tanggal Merah';
          })) > 0;
          $dayEvents = array_filter($monthEvents, static function ($item) use ($dayNumber) {
              return (int) $item['event_day'] === $dayNumber;
          });
          $addUrl = '?' . http_build_query($monthQuery + ['modal' => 'add', 'day' => $dayNumber]);
          ?>
          <article class="day-cell<?= $isToday ? ' today' : '' ?><?= $isHoliday ? ' holiday-day' : '' ?>">
            <a class="day-open" href="<?= h($addUrl) ?>" aria-label="Tambah kegiatan tanggal <?= $dayNumber ?>"></a>
            <div class="day-number" data-day-name="<?= h($dayWeekday) ?>">
              <span><?= str_pad((string) $dayNumber, 2, '0', STR_PAD_LEFT) ?></span>
              <?php if ($isToday): ?><span class="today-label">Hari ini</span><?php endif; ?>
            </div>
            <span class="pin" aria-hidden="true"></span>
            <div class="notes-list">
              <?php foreach ($calendarInfoByDay[$dayNumber] ?? [] as $calendarInfo): ?>
                <?php
                $infoStyle = $infoTypeStyles[$calendarInfo['info_type']] ?? $infoTypeStyles['Lainnya'];
                $infoEditUrl = '?' . http_build_query($monthQuery + ['modal' => 'info_edit', 'id' => (int) $calendarInfo['id']]);
                ?>
                <div class="calendar-info">
                  <div class="calendar-info-heading">
                    <?php if ($isAdmin): ?>
                      <a class="calendar-info-title" href="<?= h($infoEditUrl) ?>"><?= h($calendarInfo['title']) ?></a>
                    <?php else: ?>
                      <strong class="calendar-info-title"><?= h($calendarInfo['title']) ?></strong>
                    <?php endif; ?>
                    <span class="calendar-info-type" style="background:<?= h($infoStyle[0]) ?>;color:<?= h($infoStyle[1]) ?>"><?= h($calendarInfo['info_type']) ?></span>
                  </div>
                  <?php if ($calendarInfo['details'] !== ''): ?><div class="calendar-info-details"><?= h($calendarInfo['details']) ?></div><?php endif; ?>
                  <?php if ($isAdmin): ?>
                    <form class="info-action-form" method="post">
                      <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                      <input type="hidden" name="year" value="<?= h($year) ?>">
                      <input type="hidden" name="month" value="<?= h($month) ?>">
                      <input type="hidden" name="info_id" value="<?= h($calendarInfo['id']) ?>">
                      <button class="info-delete" type="submit" name="action" value="info_delete">Hapus info</button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              <?php foreach ($dayEvents as $index => $event): ?>
                <?php
                $style = $categoryStyles[$event['category']] ?? $categoryStyles['Lainnya'];
                $editUrl = '?' . http_build_query($monthQuery + ['modal' => 'edit', 'id' => (int) $event['id']]);
                $tilt = (((int) $index % 3) - 1) * 1.1;
                ?>
                <div class="sticky<?= (int) $event['is_done'] === 1 ? ' done' : '' ?>" style="--tilt:<?= h($tilt) ?>deg;background:<?= h($style[0]) ?>;color:<?= h($style[1]) ?>">
                  <a class="sticky-title-link" href="<?= h($editUrl) ?>"><span class="sticky-title"><?= h($event['title']) ?></span></a>
                  <div class="sticky-meta"><?= h($event['event_time'] ? substr($event['event_time'], 0, 5) : 'Tanpa jam') ?> · <?= h($event['category']) ?><?= $isAdmin && $event['owner_name'] ? ' · ' . h($event['owner_name']) : '' ?></div>
                  <?php if ($event['note'] !== ''): ?><div class="sticky-note"><?= h($event['note']) ?></div><?php endif; ?>
                  <div class="sticky-actions">
                    <form class="sticky-action-form" method="post">
                      <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                      <input type="hidden" name="year" value="<?= h($year) ?>">
                      <input type="hidden" name="month" value="<?= h($month) ?>">
                      <input type="hidden" name="id" value="<?= h($event['id']) ?>">
                      <button class="sticky-action" type="submit" name="action" value="toggle" aria-label="<?= (int) $event['is_done'] === 1 ? 'Tandai belum selesai' : 'Tandai selesai' ?>" title="<?= (int) $event['is_done'] === 1 ? 'Tandai belum selesai' : 'Tandai selesai' ?>"><?= (int) $event['is_done'] === 1 ? '↶' : '✓' ?></button>
                    </form>
                    <form class="sticky-action-form" method="post">
                      <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                      <input type="hidden" name="year" value="<?= h($year) ?>">
                      <input type="hidden" name="month" value="<?= h($month) ?>">
                      <input type="hidden" name="id" value="<?= h($event['id']) ?>">
                      <button class="sticky-action" type="submit" name="action" value="delete" aria-label="Hapus catatan" title="Hapus catatan">×</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <a class="add-day" href="<?= h($addUrl) ?>"><span aria-hidden="true">＋</span><span>Tambah</span></a>
            <?php if ($isAdmin): ?>
              <?php $infoAddUrl = '?' . http_build_query($monthQuery + ['modal' => 'info', 'date' => sprintf('%04d-%02d-%02d', $year, $month, $dayNumber)]); ?>
              <a class="add-info" href="<?= h($infoAddUrl) ?>">＋ Info</a>
            <?php endif; ?>
          </article>
        <?php endfor; ?>
      </div>

      <footer class="site-footer">
        <span>Tersimpan di database MySQL <span aria-hidden="true">·</span> bisa dibuka dari perangkat yang sama</span>
        <span class="hand footer-note">Pelan-pelan, yang penting jalan ✨</span>
      </footer>
    </section>
  </main>

  <div class="modal-backdrop<?= $isModalOpen ? '' : ' closed' ?>" role="dialog" aria-modal="true" aria-labelledby="modal-heading">
    <section class="modal-paper">
      <a class="modal-close" href="?<?= h(http_build_query($monthQuery)) ?>" aria-label="Tutup">×</a>
      <p class="modal-eyebrow"><?= $eventById ? 'Edit sticky note' : 'Tempel sticky note baru' ?></p>
      <h2 id="modal-heading" class="hand modal-title"><?= $eventById ? 'Ubah rencanamu' : 'Apa rencanamu?' ?></h2>
      <p class="modal-date"><?= h($dialogDate) ?></p>
      <form class="event-form" method="post" action="index.php?<?= h(http_build_query($monthQuery)) ?>">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= h($eventById['id'] ?? '') ?>">
        <input type="hidden" name="year" value="<?= h($year) ?>">
        <input type="hidden" name="month" value="<?= h($month) ?>">
        <input type="hidden" name="day" value="<?= h($day) ?>">
        <label>
          <span class="field-label">Judul kegiatan / tugas <span class="required">*</span></span>
          <input class="field" name="title" type="text" maxlength="70" required placeholder="Contoh: Kumpulkan laporan praktikum" value="<?= h($eventById['title'] ?? '') ?>">
        </label>
        <div class="field-row">
          <label>
            <span class="field-label">Kategori</span>
            <select class="field" name="category">
              <?php foreach ($categories as $category): ?>
                <option value="<?= h($category) ?>"<?= ($eventById['category'] ?? 'Kuliah') === $category ? ' selected' : '' ?>><?= h($category) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            <span class="field-label">Jam kegiatan</span>
            <input class="field" name="time" type="time" value="<?= h(isset($eventById['event_time']) && $eventById['event_time'] ? substr($eventById['event_time'], 0, 5) : '') ?>">
          </label>
        </div>
        <label>
          <span class="field-label">Catatan singkat</span>
          <textarea class="field" name="note" rows="3" maxlength="180" placeholder="Detail kecil yang perlu diingat..."><?= h($eventById['note'] ?? '') ?></textarea>
        </label>
        <div class="form-actions">
          <a class="form-button button-muted" href="?<?= h(http_build_query($monthQuery)) ?>">Batal</a>
          <button class="form-button button-primary" type="submit"><?= $eventById ? 'Simpan perubahan' : 'Tempel catatan' ?></button>
        </div>
      </form>
    </section>
  </div>

  <div class="modal-backdrop<?= $isInfoModalOpen ? '' : ' closed' ?>" role="dialog" aria-modal="true" aria-labelledby="info-modal-heading">
    <section class="modal-paper">
      <a class="modal-close" href="?<?= h(http_build_query($monthQuery)) ?>" aria-label="Tutup">×</a>
      <p class="modal-eyebrow"><?= $infoById ? 'Edit informasi kalender' : 'Info untuk semua pengguna' ?></p>
      <h2 id="info-modal-heading" class="hand modal-title"><?= $infoById ? 'Ubah informasi' : 'Tambah info kalender' ?></h2>
      <p class="modal-date">Tanggal merah, hari peringatan, atau pengumuman</p>
      <form class="event-form" method="post" action="index.php?<?= h(http_build_query($monthQuery)) ?>">
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
        <input type="hidden" name="action" value="info_save">
        <input type="hidden" name="info_id" value="<?= h($infoById['id'] ?? '') ?>">
        <input type="hidden" name="year" value="<?= h($year) ?>">
        <input type="hidden" name="month" value="<?= h($month) ?>">
        <label>
          <span class="field-label">Tanggal</span>
          <input class="field" name="info_date" type="date" required value="<?= h($infoDateValue) ?>">
        </label>
        <label>
          <span class="field-label">Judul informasi</span>
          <input class="field" name="info_title" type="text" maxlength="100" required placeholder="Contoh: Hari Lahir Pancasila" value="<?= h($infoById['title'] ?? '') ?>">
        </label>
        <label>
          <span class="field-label">Jenis informasi</span>
          <select class="field" name="info_type" required>
            <?php foreach ($infoTypes as $infoType): ?>
              <option value="<?= h($infoType) ?>"<?= ($infoById['info_type'] ?? 'Hari Libur / Tanggal Merah') === $infoType ? ' selected' : '' ?>><?= h($infoType) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span class="field-label">Keterangan (opsional)</span>
          <textarea class="field" name="info_details" rows="3" maxlength="255" placeholder="Contoh: Libur nasional"><?= h($infoById['details'] ?? '') ?></textarea>
        </label>
        <div class="form-actions">
          <a class="form-button button-muted" href="?<?= h(http_build_query($monthQuery)) ?>">Batal</a>
          <button class="form-button button-primary" type="submit"><?= $infoById ? 'Simpan perubahan' : 'Tambah informasi' ?></button>
        </div>
      </form>
    </section>
  </div>
</body>
</html>
