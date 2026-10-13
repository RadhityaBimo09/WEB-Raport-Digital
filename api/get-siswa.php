<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check admin auth
$admin = wajib_login('admin');

$nisn = trim($_GET['nisn'] ?? '');
if ($nisn === '') {
    http_response_code(400);
    echo json_encode(['error' => 'NISN required']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT s.*, w.Nama_ayah, w.Pekerjaan_ayah, w.Nama_ibu, w.Pekerjaan_ibu, w.Alamat_wali, w.Telp_wali
           FROM siswa s
           LEFT JOIN wali_murid w ON w.Kode_wali = s.Kode_wali
          WHERE s.NISN = :nisn'
    );
    $stmt->execute([':nisn' => $nisn]);
    $data = $stmt->fetch();
    
    if (!$data) {
        http_response_code(404);
        echo json_encode(['error' => 'Siswa tidak ditemukan']);
        exit;
    }
    
    echo json_encode($data);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
