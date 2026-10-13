<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$admin = wajib_login('admin');

$kode = trim($_GET['kode'] ?? $_GET['kode_guru'] ?? '');
if ($kode === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Kode guru required']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT g.*, kk.Nama_kompetensi_keahlian
           FROM guru g
           LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = g.Kode_KK
          WHERE g.Kode_guru = :k
          LIMIT 1'
    );
    $stmt->execute([':k' => $kode]);
    $data = $stmt->fetch();

    if (!$data) {
        http_response_code(404);
        echo json_encode(['error' => 'Guru tidak ditemukan']);
        exit;
    }

    unset($data['Password']);

    echo json_encode($data);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
