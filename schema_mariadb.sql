-- Schema Maria DB untuk E-DocSmart
-- Database: edocsmart
-- Kompatibel dengan Maria DB 10.5+

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabel document_files (relasi lampiran ke documents)
CREATE TABLE IF NOT EXISTS `document_files` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
  KEY `idx_document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create indexes untuk performa query
CREATE INDEX `idx_username` ON `users`(`username`);
CREATE INDEX `idx_email` ON `users`(`email`);
CREATE INDEX `idx_category` ON `documents`(`category`);
CREATE INDEX `idx_created_at` ON `documents`(`created_at`);

-- Catatan:
-- 1) File ini untuk Maria DB 10.5+
-- 2) Driver PDO yang digunakan tetap 'mysql' (kompatibel)
-- 3) Jalankan migrate.php untuk menambah user default admin
-- 4) Atau import file schema ini via mysql CLI atau phpMyAdmin
