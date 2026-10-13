<?php
/**
 * =====================================================================
 * EXPORT REKAPITULASI NILAI — CETAK / PDF
 * ---------------------------------------------------------------------
 * ?kelas=all|<id> -> tampilan cetak bersih (Ctrl+P / Simpan sebagai PDF)
 * ?tingkat=all|X|XI|XII -> saring kelas fisik saat mode gabungan
 * Font: Montserrat Regular (isi) + Montserrat Bold (judul & header)
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/guru-repository.php';

$user = wajib_login('guru');

$daftarKelas = guru_kelas_ajar($user['kode']);

$paramTingkat = isset($_GET['tingkat']) ? strtoupper(trim((string) $_GET['tingkat'])) : 'all';
if (!in_array($paramTingkat, ['X', 'XI', 'XII'], true)) {
    $paramTingkat = 'all';
}

$paramKelas = isset($_GET['kelas']) ? trim((string) $_GET['kelas']) : 'all';

$kelasAktif = null;
if ($paramKelas !== 'all' && ctype_digit($paramKelas)) {
    $kelasAktif = guru_kelas_ajar_satu($user['kode'], (int) $paramKelas);
}

if ($kelasAktif === null) {
    $paramKelas = 'all';
}

// Jika mapel terpilih beda Tingkat dengan filter kelas, kembali ke mode gabungan.
if ($kelasAktif !== null && $paramTingkat !== 'all' && ($kelasAktif['Tingkat'] ?? '') !== $paramTingkat) {
    $kelasAktif = null;
    $paramKelas = 'all';
}

$daftarKelasTampil = $daftarKelas;
if ($kelasAktif === null && $paramTingkat !== 'all') {
    $daftarKelasTampil = array_values(array_filter(
        $daftarKelas,
        static fn ($k) => ($k['Tingkat'] ?? '') === $paramTingkat
    ));
}

$rekap = $kelasAktif
    ? guru_rekap_kelas($kelasAktif)
    : guru_rekap_semua($daftarKelasTampil);

if ($kelasAktif) {
    $namaScope = $kelasAktif['nama_kelas'] . ' — ' . $kelasAktif['Nama_mata_pelajaran'];
} elseif ($paramTingkat !== 'all') {
    $namaKK = $daftarKelasTampil[0]['Nama_kompetensi_keahlian'] ?? ($daftarKelas[0]['Nama_kompetensi_keahlian'] ?? '');
    $namaScope = trim($paramTingkat . ' ' . $namaKK) . ' — Semua Mata Pelajaran Diampu';
} else {
    $namaScope = 'Semua Mata Pelajaran Diampu';
}

$judul = 'Rekapitulasi Nilai - ' . $namaScope;

/* ---------------------------------------------------------------------
 * PRINT / PDF (Montserrat Regular + Bold)
 * ------------------------------------------------------------------ */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Montserrat", "Segoe UI", system-ui, -apple-system, sans-serif;
            font-weight: 400;
            margin: 0; padding: 24px; color: #0f172a; background: #eef2f7;
            font-size: 15px; line-height: 1.6;
        }
        .sheet {
            max-width: 1120px; margin: 0 auto; background: #fff;
            padding: 44px 48px 40px; border-radius: 14px; box-shadow: 0 8px 30px rgba(15,23,42,.1);
        }
        .kop { text-align: center; margin-bottom: 8px; }
        .kop-school { font-size: 22px; font-weight: 700; letter-spacing: .02em; margin: 0; }
        .kop-sub { font-size: 13px; font-weight: 600; color: #475569; margin: 4px 0 0; letter-spacing: .06em; text-transform: uppercase; }
        .kop-line { border: none; border-top: 3px solid #0f172a; border-bottom: 1px solid #0f172a; height: 6px; margin: 16px 0 20px; }
        h1 { font-size: 21px; line-height: 1.4; margin: 0 0 8px; font-weight: 700; font-family: "Montserrat", sans-serif; }
        .meta {
            font-size: 13.5px; color: #334155; margin-bottom: 20px; line-height: 1.8; font-weight: 500;
            background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;
        }
        .meta strong { font-weight: 700; color: #0f172a; }
        .table-wrap { overflow-x: auto; border: 1px solid #94a3b8; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; font-weight: 400; line-height: 1.5; }
        th, td { border: 1px solid #94a3b8; padding: 11px 12px; font-family: "Montserrat", sans-serif; }
        th {
            background: #1e293b; color: #fff; text-align: left; font-size: 12.5px;
            font-weight: 700; text-transform: uppercase; letter-spacing: .05em; line-height: 1.4;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        td.c, th.c { text-align: center; }
        td.nama { font-weight: 600; color: #0f172a; }
        td.nisn { font-weight: 600; letter-spacing: .02em; }
        td.rata { background: #fef9c3; font-weight: 700; font-size: 14.5px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        td strong { font-weight: 700; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(even) td.rata { background: #fef08a; }
        tbody td { color: #1e293b; }
        .toolbar { max-width: 1120px; margin: 0 auto 18px; display: flex; gap: 10px; justify-content: flex-end; }
        button, a.btn {
            font: 700 13.5px/1 "Montserrat", sans-serif; padding: 13px 22px; border-radius: 10px;
            border: 1px solid #cbd5e1; background: #fff; color: #0f172a;
            cursor: pointer; text-decoration: none;
        }
        button { background: #2B76B9; border-color: #2B76B9; color: #fff; box-shadow: 0 4px 12px rgba(43,118,185,.35); }
        .foot { margin-top: 18px; font-size: 12.5px; font-weight: 600; color: #475569; display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
        .ttd { margin-top: 36px; display: flex; justify-content: space-between; gap: 24px; font-size: 13.5px; line-height: 1.7; }
        .ttd > div { width: 260px; text-align: center; }
        .ttd .nama { margin-top: 72px; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
        @page { size: A4 landscape; margin: 12mm; }
        @media print {
            body { background: #fff; padding: 0; font-size: 11pt; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; padding: 0; max-width: none; border-radius: 0; }
            .kop-school { font-size: 18pt; }
            h1 { font-size: 15pt; }
            .meta { font-size: 10pt; }
            table { font-size: 10.5pt; }
            th { font-size: 9.5pt; padding: 8px 9px; }
            td { padding: 8px 9px; }
            .foot, .ttd { font-size: 10pt; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <a class="btn" href="rekap-nilai.php?kelas=<?= e($paramKelas) ?><?= $paramTingkat !== 'all' ? '&tingkat=' . e($paramTingkat) : '' ?>">Kembali</a>
    <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<div class="sheet">
    <div class="kop">
        <p class="kop-school">SMK BINA INFORMATIKA</p>
        <p class="kop-sub">E-Rapor Digital &bull; Tahun Ajaran <?= e(TAHUN_AJARAN_AKTIF . ' — ' . SEMESTER_AKTIF) ?></p>
    </div>
    <hr class="kop-line">
    <h1><?= e($judul) ?></h1>
    <div class="meta">
        Guru Pengampu: <strong><?= e($user['nama']) ?></strong> &nbsp;&bull;&nbsp; NIP: <strong><?= e($user['nip'] ?: '-') ?></strong><br>
        Kelas / Mapel: <strong><?= e($namaScope) ?></strong> &nbsp;&bull;&nbsp;
        Dicetak: <?= e(date('d-m-Y H:i')) ?> WIB
    </div>

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th class="c" style="width:48px">No</th>
                <th style="width:130px">NISN</th>
                <th>Nama Siswa</th>
                <?php foreach ($rekap['kolom'] as $kolom): ?>
                    <th class="c"><?= e($kolom['label']) ?></th>
                <?php endforeach; ?>
                <th class="c" style="width:100px">Rata-rata</th>
                <th class="c" style="width:90px">Predikat</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rekap['baris']): ?>
                <tr><td class="c" colspan="<?= count($rekap['kolom']) + 5 ?>">Belum ada data nilai.</td></tr>
            <?php endif; ?>

            <?php foreach ($rekap['baris'] as $i => $row): ?>
                <tr>
                    <td class="c"><?= $i + 1 ?></td>
                    <td class="nisn"><?= e($row['nisn']) ?></td>
                    <td class="nama"><?= e($row['nama']) ?></td>
                    <?php foreach ($rekap['kolom'] as $kolom): ?>
                        <?php $val = $row['nilai'][$kolom['key']] ?? null; ?>
                        <td class="c"><?= $val === null ? '-' : e((string) (0 + $val)) ?></td>
                    <?php endforeach; ?>
                    <td class="c rata"><?= $row['rata'] === null ? '-' : e((string) (0 + $row['rata'])) ?></td>
                    <td class="c"><strong><?= e($row['huruf']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <div class="foot">
        <span>Total <?= count($rekap['baris']) ?> siswa</span>
        <span>E-Rapor Digital &mdash; SMK Bina Informatika</span>
    </div>

</div>

</body>
</html>
