<?php
require_once __DIR__ . '/db_config.php';

try {
    $pdo = new PDO(sprintf('mysql:host=%s;charset=utf8mb4', DB_HOST), DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_NAME));
    $pdo->exec(sprintf('USE `%s`', DB_NAME));

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(120) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(120) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        owner VARCHAR(120) NOT NULL,
        signature_data_url LONGTEXT DEFAULT NULL,
        signature_file_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS document_files (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        document_id INT UNSIGNED NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $defaultPassword = password_hash('Password123!', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT IGNORE INTO users (username, email, password_hash, full_name) VALUES (:username, :email, :hash, :full_name)');
    $stmt->execute([
        ':username' => 'admin',
        ':email' => 'admin@example.com',
        ':hash' => $defaultPassword,
        ':full_name' => 'Administrator',
    ]);

    echo "Migrasi selesai. Database '" . DB_NAME . "' sudah terpasang.\n";
    echo "Login default: username=admin, password=Password123!\n";
} catch (PDOException $ex) {
    echo "Gagal menjalankan migrasi: " . $ex->getMessage();
    exit;
}
