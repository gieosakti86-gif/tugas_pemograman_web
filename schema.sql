-- Schema SQL untuk E-DocSmart
-- Nama database: edocsmart

CREATE DATABASE IF NOT EXISTS `edocsmart` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `edocsmart`;

-- Tabel users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(120) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel documents
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `owner` VARCHAR(120) NOT NULL,
  `signature_data_url` LONGTEXT DEFAULT NULL,
  `signature_file_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel document_files (relasi lampiran ke documents)
CREATE TABLE IF NOT EXISTS `document_files` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catatan:
-- 1) File ini hanya membuat struktur database dan tabel.
-- 2) Untuk menambahkan akun admin default dengan hash password aman, jalankan file PHP `migrate.php` yang sudah tersedia di root proyek.
-- 3) Jika ingin menambahkan user melalui SQL, masukkan nilai `password_hash` yang dihasilkan oleh PHP `password_hash()`.
