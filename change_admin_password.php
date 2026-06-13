<?php
require_once __DIR__ . '/db_config.php';

try {
    $pdo = getDbConnection();
    $newPassword = '123';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE username = :username');
    $stmt->execute([':hash' => $hash, ':username' => 'admin']);
    echo "Password admin berhasil diubah. Username: admin, Password baru: 123\n";
} catch (PDOException $e) {
    echo "Gagal mengubah password: " . $e->getMessage() . "\n";
    exit(1);
}
