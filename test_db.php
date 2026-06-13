<?php
require_once __DIR__ . '/db_config.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM users');
    $count = $stmt->fetchColumn();
    echo json_encode(['status' => 'ok', 'db' => DB_NAME, 'users_count' => (int)$count]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
