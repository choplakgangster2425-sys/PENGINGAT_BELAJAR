# Mading Bulanan

Aplikasi pengingat kegiatan bulanan bertema papan mading dan sticky notes. Dibuat dengan HTML, CSS, PHP, dan MySQL untuk dijalankan secara lokal melalui XAMPP.

## Fitur

- Kalender mengikuti jumlah hari pada bulan dan tahun yang dipilih.
- Kegiatan memiliki judul, kategori, waktu, dan catatan.
- CRUD kegiatan: tambah, lihat, edit, hapus, dan tandai selesai.
- Admin dapat menerbitkan informasi kalender bersama seperti hari libur/tanggal merah, peringatan, dan pengumuman.
- Data disimpan dalam database MySQL.
- Login, pendaftaran pengguna, dan pembagian hak akses admin/pengguna.

## Persyaratan

- XAMPP dengan Apache dan MySQL/MariaDB aktif.
- PHP dengan ekstensi PDO MySQL (`pdo_mysql`) aktif.
- Browser web modern.

## Menjalankan aplikasi

1. Letakkan folder proyek di `C:\xampp\htdocs\UASPEMWEB`.
2. Buka XAMPP Control Panel dan jalankan **Apache** serta **MySQL**.
3. Buka `http://localhost/phpmyadmin`, pilih tab **Import**, pilih file `database.sql` dari folder proyek, lalu klik **Import/Go**. Skrip akan membuat database, tabel, dan akun demo.
4. Periksa konfigurasi MySQL di `config/database.php`. Pengaturan bawaan XAMPP adalah host `127.0.0.1`, database `mading_bulanan`, username `root`, dan kata sandi kosong. Sesuaikan jika konfigurasi lokal Anda berbeda.
5. Buka `http://localhost/UASPEMWEB/login.php`, lalu masuk menggunakan salah satu akun demo di bawah.

Sebagai alternatif, aplikasi dapat membuat database dan tabel secara otomatis saat pertama kali dibuka. Jika memakai cara ini tanpa mengimpor `database.sql`, buka `http://localhost/UASPEMWEB/setup.php` untuk membuat admin pertama, lalu daftar sebagai pengguna melalui `http://localhost/UASPEMWEB/register.php`. Akun demo di bawah hanya disediakan oleh skrip SQL.

## Akun admin

Untuk demo tugas lokal, satu akun admin dan satu akun pengguna sudah tersedia:

| Peran | Username | Kata sandi |
|---|---|---|
| Admin | `admin.demo` | `AdminMading8!` |
| Pengguna | `user.demo` | `UserMading8!` |

Masuk melalui `http://localhost/UASPEMWEB/login.php`. Kedua akun memenuhi batas minimum kata sandi 8 karakter. Data kata sandi disimpan di MySQL sebagai hash.

> **Penting:** kredensial ini sengaja dicantumkan untuk demonstrasi tugas lokal. Jangan gunakan pada server publik. Sebelum deploy, hapus akun `admin.demo` dari tabel `users` melalui phpMyAdmin, kemudian buat admin baru melalui `http://localhost/UASPEMWEB/setup.php`, atau jangan impor akun demo ke server publik.

## Peran dan izin

### Admin

- Melihat semua kegiatan dari semua pengguna.
- Menambah kegiatan dan mengedit, menandai selesai, atau menghapus kegiatan apa pun.
- Menambahkan, mengedit, dan menghapus informasi kalender bersama; informasi terlihat oleh semua pengguna pada tanggalnya.
- Membuka **Kelola pengguna** untuk menonaktifkan atau mengaktifkan kembali akun pengguna.
- Kegiatan pengguna yang dinonaktifkan tetap tersimpan.

### Pengguna

- Mendaftar sendiri melalui `http://localhost/UASPEMWEB/register.php`.
- Melihat dan mengelola kegiatan miliknya sendiri.
- Tidak dapat membuka halaman admin atau mengubah kegiatan pengguna lain.
- Kata sandi pengguna minimal 8 karakter.

## Struktur proyek

```text
UASPEMWEB/
|-- assets/
|   `-- css/
|       `-- style.css          # Gaya kalender, form login, dan halaman lain
|-- database.sql               # Skema MySQL dan akun demo untuk impor
|-- config/
|   `-- database.php           # Konfigurasi koneksi dan inisialisasi tabel
|-- controllers/
|   |-- auth.php               # Login, pendaftaran, dan logout
|   |-- mading.php             # Kalender dan CRUD kegiatan
|   |-- setup.php              # Pembuatan admin pertama
|   `-- users.php              # Pengelolaan akun oleh admin
|-- includes/
|   |-- bootstrap.php          # Inisialisasi aplikasi, sesi, dan database
|   `-- helpers.php            # Helper escaping, CSRF, sesi, dan tanggal
|-- views/
|   |-- login.php              # Form login
|   |-- mading.php             # Tampilan kalender
|   |-- register.php           # Form pendaftaran
|   |-- setup.php              # Form setup admin
|   `-- users.php              # Tampilan kelola pengguna
|-- index.php                  # Entry point kalender
|-- login.php                  # Halaman login
|-- logout.php                 # Aksi logout
|-- register.php               # Halaman pendaftaran
|-- setup.php                  # Setup admin pertama
`-- users.php                  # Halaman kelola pengguna (admin)
```

## Catatan keamanan

- Kata sandi akun disimpan menggunakan `password_hash`, bukan sebagai teks biasa.
- Formulir perubahan data menggunakan token CSRF dan query database berparameter.
- Jangan membagikan folder proyek beserta file konfigurasi berisi kredensial database.
- Jika admin pertama sudah dibuat, jangan menghapus akun admin dari database kecuali Anda memang ingin mengulang proses setup.
