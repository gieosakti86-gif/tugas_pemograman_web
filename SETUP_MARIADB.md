# Setup E-DocSmart dengan Maria DB

## Prasyarat

- Laragon atau server dengan Maria DB 10.5+ terpasang
- PHP 8.1+
- Browser modern

## Langkah Setup

### 1. Import Schema Database

#### Option A: Via MySQL CLI (Terminal)
```bash
mysql -u root -p < schema_mariadb.sql
```

#### Option B: Via PhpMyAdmin / Adminer
1. Buka PhpMyAdmin: `http://localhost/phpmyadmin`
2. Login dengan credentials default Laragon
3. Buka tab "SQL"
4. Salin isi file `schema_mariadb.sql` dan paste ke SQL editor
5. Klik tombol Execute

#### Option C: Jalankan PHP Migration Script
```bash
php migrate.php
```

### 2. Verifikasi Koneksi Database

Jalankan test script:
```bash
php test_db.php
```

Buka di browser: `http://localhost:8001/test_db.php`

Respons yang diharapkan:
```json
{"status":"ok","db":"edocsmart","users_count":1}
```

### 3. Login Default

Setelah migrasi berhasil, login dengan:
- Username: `admin`
- Password: `123`

### 4. Ubah Password Admin (Opsional)

Untuk mengubah password admin, jalankan:
```bash
php change_admin_password.php
```

Kemudian edit file `change_admin_password.php` baris:
```php
$newPassword = '123'; // Ganti dengan password baru
```

## Catatan Kompatibilitas

- **Driver PDO**: Tetap menggunakan `mysql` (kompatibel penuh dengan Maria DB)
- **Charset**: `utf8mb4` untuk dukungan emoji dan karakter khusus
- **Collation**: `utf8mb4_unicode_ci` untuk sorting yang benar
- **Engine**: InnoDB untuk transactions dan foreign keys

## Troubleshooting

### Error: "Access denied for user"
- Pastikan credentials di `db_config.php` sesuai dengan Maria DB Anda
- Default Laragon: `root` dengan password kosong

### Error: "SQLSTATE[HY000]: General error"
- Pastikan schema sudah di-import
- Periksa koneksi database via `test_db.php`

### Error: "Terjadi kesalahan server"
- Cek logs:
  - PHP error log: `php error_log` di root folder
  - atau lihat browser DevTools → Network tab

## File Penting

| File | Fungsi |
|------|--------|
| `schema_mariadb.sql` | Schema database untuk Maria DB |
| `schema.sql` | Schema database untuk MySQL |
| `db_config.php` | Konfigurasi koneksi database |
| `migrate.php` | Jalankan migrasi & buat user default |
| `change_admin_password.php` | Ubah password admin |
| `test_db.php` | Test koneksi database |
| `auth.php` | Endpoint login |
| `api_documents.php` | CRUD dokumen API |

## Akses Aplikasi

- Development (Laragon): `http://localhost/tugas_pemograman_web/`
- Temporary server: `http://localhost:8001/`
