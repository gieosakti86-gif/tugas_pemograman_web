<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_config.php';
ensureUserAuthenticated();

$action = $_REQUEST['action'] ?? 'list';

switch ($action) {
    case 'list':
        handleList();
        break;
    case 'get':
        handleGet();
        break;
    case 'save':
        handleSave();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
        break;
}

function handleList()
{
    $pdo = getDbConnection();
    $draw = intval($_GET['draw'] ?? 1);
    $start = intval($_GET['start'] ?? 0);
    $length = intval($_GET['length'] ?? 10);
    $searchValue = '%'.($_GET['search']['value'] ?? '').'%';
    $orderColumnIndex = intval($_GET['order'][0]['column'] ?? 0);
    $orderDir = strtoupper($_GET['order'][0]['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

    $columnMap = [
        0 => 'd.id',
        1 => 'd.title',
        2 => 'd.category',
        3 => 'd.owner',
        4 => 'd.created_at',
        5 => 'd.signature_data_url'
    ];
    $orderColumn = $columnMap[$orderColumnIndex] ?? 'd.id';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documents d");
    $countStmt->execute();
    $recordsTotal = $countStmt->fetchColumn();

    $where = '';
    $params = [];
    if (!empty($_GET['search']['value'])) {
        $where = "WHERE d.title LIKE :search OR d.category LIKE :search OR d.owner LIKE :search";
        $params[':search'] = $searchValue;
    }

    $stmt = $pdo->prepare(
        "SELECT d.id, d.title, d.category, d.owner, d.signature_data_url, d.signature_file_path, d.created_at,
            GROUP_CONCAT(f.original_name SEPARATOR '||') AS attachment_names,
            GROUP_CONCAT(f.file_path SEPARATOR '||') AS attachment_paths
        FROM documents d
        LEFT JOIN document_files f ON f.document_id = d.id
        $where
        GROUP BY d.id
        ORDER BY $orderColumn $orderDir
        LIMIT :start, :length"
    );
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $filteredCount = $recordsTotal;
    if (!empty($_GET['search']['value'])) {
        $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT d.id) FROM documents d $where");
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $filteredCount = $countStmt->fetchColumn();
    }

    $data = [];
    foreach ($rows as $index => $row) {
        $attachmentsHtml = '';
        if (!empty($row['attachment_names'])) {
            $names = explode('||', $row['attachment_names']);
            $paths = explode('||', $row['attachment_paths']);
            foreach ($names as $key => $name) {
                $path = $paths[$key] ?? '#';
                $attachmentsHtml .= sprintf('<a href="%s" target="_blank" class="badge bg-info text-white me-1 mb-1">%s</a>', htmlspecialchars($path), htmlspecialchars($name));
            }
        } else {
            $attachmentsHtml = '<span class="text-muted">Tidak ada lampiran</span>';
        }

        $statusLabel = !empty($row['signature_data_url']) ? '<span class="badge bg-success">Sudah TTD</span>' : '<span class="badge bg-secondary">Belum TTD</span>';

        $actionHtml = sprintf(
            '<button class="btn btn-sm btn-warning btn-edit" data-id="%s"><i class="fa-solid fa-edit"></i></button> <button class="btn btn-sm btn-danger btn-delete" data-id="%s"><i class="fa-solid fa-trash"></i></button>',
            $row['id'], $row['id']
        );

        $data[] = [
            $row['id'],
            htmlspecialchars($row['title']),
            htmlspecialchars($row['category']),
            htmlspecialchars($row['owner']),
            $attachmentsHtml,
            $statusLabel,
            $actionHtml
        ];
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => intval($recordsTotal),
        'recordsFiltered' => intval($filteredCount),
        'data' => $data,
    ]);
}

function handleGet()
{
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID dokumen tidak valid.']);
        return;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT * FROM documents WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $document = $stmt->fetch();

    if (!$document) {
        echo json_encode(['status' => 'error', 'message' => 'Dokumen tidak ditemukan.']);
        return;
    }

    $stmt = $pdo->prepare('SELECT id, original_name, file_path FROM document_files WHERE document_id = :id ORDER BY uploaded_at ASC');
    $stmt->execute([':id' => $id]);
    $files = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => ['document' => $document, 'files' => $files]]);
}

function handleSave()
{
    $title = sanitize($_POST['title'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $owner = sanitize($_POST['owner'] ?? '');
    $signatureDataUrl = $_POST['signature_data_url'] ?? '';
    $documentId = intval($_POST['document_id'] ?? 0);

    if ($title === '' || $category === '' || $owner === '') {
        echo json_encode(['status' => 'error', 'message' => 'Lengkapi semua field yang ditandai.']);
        return;
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    try {
        if ($documentId > 0) {
            $stmt = $pdo->prepare('UPDATE documents SET title = :title, category = :category, owner = :owner, updated_at = NOW() WHERE id = :id');
            $stmt->execute([':title' => $title, ':category' => $category, ':owner' => $owner, ':id' => $documentId]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO documents (title, category, owner) VALUES (:title, :category, :owner)');
            $stmt->execute([':title' => $title, ':category' => $category, ':owner' => $owner]);
            $documentId = intval($pdo->lastInsertId());
        }

        if (!empty($signatureDataUrl) && strpos($signatureDataUrl, 'data:image') === 0) {
            list($meta, $content) = explode(',', $signatureDataUrl, 2);
            $decoded = base64_decode($content);
            if ($decoded !== false) {
                $signatureFileName = 'sig_' . $documentId . '_' . time() . '.png';
                $signaturePath = SIGN_UPLOAD_DIR . '/' . $signatureFileName;
                file_put_contents($signaturePath, $decoded);
                $relativePath = 'uploads/signatures/' . $signatureFileName;
                $stmt = $pdo->prepare('UPDATE documents SET signature_data_url = :signature_data_url, signature_file_path = :file_path WHERE id = :id');
                $stmt->execute([
                    ':signature_data_url' => $signatureDataUrl,
                    ':file_path' => $relativePath,
                    ':id' => $documentId,
                ]);
            }
        }

        if (!empty($_FILES['attachments']['name'][0])) {
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            $maxSize = 2 * 1024 * 1024;
            foreach ($_FILES['attachments']['name'] as $index => $originalName) {
                if (empty($originalName)) {
                    continue;
                }

                $tmpName = $_FILES['attachments']['tmp_name'][$index];
                $size = $_FILES['attachments']['size'][$index];
                $error = $_FILES['attachments']['error'][$index];
                if ($error !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('Gagal mengunggah file: ' . $originalName);
                }

                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions, true)) {
                    throw new RuntimeException('Format file tidak diizinkan: ' . $originalName);
                }
                if ($size > $maxSize) {
                    throw new RuntimeException('Ukuran file maksimum 2MB: ' . $originalName);
                }

                $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($originalName));
                $newFileName = 'doc_' . $documentId . '_' . time() . '_' . $index . '.' . $extension;
                $targetPath = DOC_UPLOAD_DIR . '/' . $newFileName;
                if (!move_uploaded_file($tmpName, $targetPath)) {
                    throw new RuntimeException('Gagal memindahkan file: ' . $originalName);
                }

                $relativePath = 'uploads/documents/' . $newFileName;
                $insert = $pdo->prepare('INSERT INTO document_files (document_id, file_path, original_name) VALUES (:document_id, :file_path, :original_name)');
                $insert->execute([
                    ':document_id' => $documentId,
                    ':file_path' => $relativePath,
                    ':original_name' => $originalName,
                ]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data dokumen berhasil disimpan.']);
    } catch (Throwable $ex) {
        $pdo->rollBack();
        logError('handleSave error', ['message' => $ex->getMessage(), 'file' => $ex->getFile(), 'line' => $ex->getLine()]);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $ex->getMessage()]);
    }
}

function handleDelete()
{
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID dokumen tidak valid.']);
        return;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT signature_file_path FROM documents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $document = $stmt->fetch();
    if (!$document) {
        echo json_encode(['status' => 'error', 'message' => 'Dokumen tidak ditemukan.']);
        return;
    }

    $stmt = $pdo->prepare('SELECT file_path FROM document_files WHERE document_id = :id');
    $stmt->execute([':id' => $id]);
    $files = $stmt->fetchAll();

    foreach ($files as $file) {
        $physicalPath = __DIR__ . '/' . $file['file_path'];
        if (is_file($physicalPath)) {
            unlink($physicalPath);
        }
    }

    if (!empty($document['signature_file_path'])) {
        $signaturePhysical = __DIR__ . '/' . $document['signature_file_path'];
        if (is_file($signaturePhysical)) {
            unlink($signaturePhysical);
        }
    }

    $delete = $pdo->prepare('DELETE FROM documents WHERE id = :id');
    $delete->execute([':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Dokumen beserta lampiran berhasil dihapus.']);
}
