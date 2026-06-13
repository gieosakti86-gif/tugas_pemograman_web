# E-DocSmart

Sistem manajemen dokumen berbasis web dengan fitur upload dokumen, multi-lampiran, dan tanda tangan digital. Dibangun dengan PHP, Bootstrap, DataTables, dan MariaDB/MySQL.

## Fitur Utama

- Autentikasi pengguna dengan session PHP
- Dashboard dokumen dengan DataTables (search, sorting, pagination)
- CRUD dokumen: tambah, edit, hapus
- Upload multiple attachment (PDF, JPG, PNG)
- Tampilan lampiran dengan badge dan link unduh
- Tanda tangan digital menggunakan HTML5 Canvas
- Modal Bootstrap untuk form dan konfirmasi
- Logging error sederhana ke `logs/error.log`

## Prasyarat

- PHP 8.1+ dengan ekstensi PDO MySQL
- MariaDB / MySQL
- Laragon atau server web lokal lain
- Browser modern

## Instalasi & Setup

1. Salin proyek ke folder web server, misalnya:
   - `d:\laragon\www\tugas_pemograman_web`

2. Pastikan direktori `uploads/`, `uploads/documents/`, dan `uploads/signatures/` dapat ditulis.

3. Sesuaikan koneksi database di `db_config.php` jika diperlukan:
   - `DB_HOST`
   - `DB_PORT`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`

4. Jalankan migrasi database:
   ```bash
   php migrate.php
   ```

   Script ini akan membuat database, tabel, dan akun admin default.

5. Verifikasi koneksi dengan `test_db.php` (opsional):
   - Buka `http://localhost/tugas_pemograman_web/test_db.php`

## Default Akun

- Username: `admin`
- Password: `Password123!`

> Jika login gagal, pastikan database sudah dibuat dan `db_config.php` sudah sesuai.

## Cara Menjalankan

- Jika menggunakan Laragon, jalankan dari `http://localhost/tugas_pemograman_web/`
- Jika menggunakan server PHP built-in:
  ```bash
  php -S localhost:8001
  ```
  lalu buka `http://localhost:8001/`

## Struktur Folder

- `index.php` — Halaman utama aplikasi
- `auth.php` — Endpoint login
- `logout.php` — Logout session
- `db_config.php` — Konfigurasi database dan helper
- `api_documents.php` — API CRUD dokumen
- `migrate.php` — Script migrasi database
- `schema_mariadb.sql` — Schema MariaDB
- `schema.sql` — Schema MySQL
- `test_db.php` — Skrip untuk cek koneksi database
- `assets/css/style.css` — CSS kustom
- `assets/js/app.js` — JavaScript interaksi UI
- `uploads/` — Penyimpanan file dokumen dan tanda tangan
- `logs/` — Error log

## Konfigurasi Database

Default `db_config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'edocsmart');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Jika menggunakan Laragon, biasanya user `root` tanpa password.

## Catatan Penting

- File upload dibatasi maksimum 2MB per file.
- Format lampiran yang diizinkan: `pdf`, `jpg`, `jpeg`, `png`.
- Tanda tangan digital disimpan sebagai file PNG di `uploads/signatures/`.
- Jika ada masalah akses file, periksa izin folder `uploads/` dan `logs/`.

## Troubleshooting

- `Access denied for user`: periksa kredensial database di `db_config.php`
- `Gagal menjalankan migrasi`: pastikan MariaDB/MySQL aktif dan user punya izin membuat database
- `Terjadi kesalahan server`: cek `logs/error.log` untuk detail

## Catatan Tambahan

Gunakan `migrate.php` untuk membuat database awal dan user default. Untuk mengubah password admin, edit file `change_admin_password.php` jika tersedia.
