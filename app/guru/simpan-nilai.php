<?php
/**
 * =====================================================================
 * PROSES SIMPAN NILAI (PORTAL GURU)
 * ---------------------------------------------------------------------
 * Menerima POST dari app/guru/input-nilai.php lalu melakukan
 * insert/update (upsert) nilai per Standar Kompetensi dalam 1 transaksi.
 *
 * Validasi yang dijalankan:
 *  - session guru aktif        (wajib_login)
 *  - token CSRF                (csrf_verify)
 *  - kelas ajar milik guru ini (guru_kelas_ajar_satu)
 *  - SK benar milik mapel itu
 *  - NISN benar anggota kelas  (guru_simpan_nilai)
 *  - nilai numerik 0-100       (guru_simpan_nilai)
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/guru-repository.php';

$user = wajib_login('guru');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect(base_url('app/guru/input-nilai.php'));
}

if (!csrf_verify($_POST['_token'] ?? null)) {
    set_flash('error', 'Sesi form tidak valid. Silakan muat ulang halaman dan coba lagi.');
    redirect(base_url('app/guru/input-nilai.php'));
}

$idKelas = isset($_POST['kelas']) ? (int) $_POST['kelas'] : 0;
$kodeSK  = trim((string) ($_POST['sk'] ?? ''));
$nilai   = $_POST['nilai'] ?? [];

$kelas = $idKelas > 0 ? guru_kelas_ajar_satu($user['kode'], $idKelas) : null;

if ($kelas === null) {
    set_flash('error', 'Kelas ajar tidak ditemukan atau bukan kelas yang Anda ampu.');
    redirect(base_url('app/guru/input-nilai.php'));
}

// Pastikan SK benar-benar milik mapel + kelas tersebut.
$skValid = false;
foreach (guru_daftar_sk($kelas['Kode_KK'], $kelas['Kode_mata_pelajaran'], $kelas['Tingkat']) as $sk) {
    if ($sk['Kode_SK'] === $kodeSK) {
        $skValid = true;
        break;
    }
}

$kembali = base_url('app/guru/input-nilai.php?kelas=' . $idKelas . '&sk=' . urlencode($kodeSK));

if (!$skValid) {
    set_flash('error', 'Standar Kompetensi tidak valid untuk mata pelajaran ini.');
    redirect(base_url('app/guru/input-nilai.php?kelas=' . $idKelas));
}

if (!is_array($nilai)) {
    set_flash('error', 'Format data nilai tidak valid.');
    redirect($kembali);
}

try {
    $hasil = guru_simpan_nilai($user['kode'], $kelas['Kode_KK'], $kelas['Tingkat'], $kodeSK, $nilai);
} catch (Throwable $e) {
    set_flash('error', 'Gagal menyimpan nilai. Perubahan dibatalkan (rollback).');
    redirect($kembali);
}

$pesan = sprintf(
    'Nilai %s berhasil disimpan: %d nilai tersimpan',
    $kodeSK,
    $hasil['tersimpan']
);

if ($hasil['terhapus'] > 0) {
    $pesan .= sprintf(', %d dikosongkan', $hasil['terhapus']);
}

if ($hasil['dilewati'] > 0) {
    $pesan .= sprintf(', %d dilewati karena tidak valid', $hasil['dilewati']);
}

set_flash($hasil['dilewati'] > 0 ? 'info' : 'success', $pesan . '.');

redirect($kembali);
