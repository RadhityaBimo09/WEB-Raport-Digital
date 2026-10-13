<?php
/**
 * =====================================================================
 * REPOSITORY PORTAL SISWA
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

// 1. PROFIL SISWA
function siswa_profil(string $nisn): ?array
{
    $stmt = db()->prepare(
        'SELECT s.NISN, s.Kode_KK, s.Tingkat, s.Jurusan,
                s.Nama_siswa, s.Alamat_siswa, s.Tgl_lahir,
                kk.Nama_kompetensi_keahlian
           FROM siswa s
           LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = s.Kode_KK
          WHERE s.NISN = :nisn
          LIMIT 1'
    );
    $stmt->execute([':nisn' => $nisn]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $row['nama_kelas'] = $row['Tingkat'] . ' ' . $row['Jurusan'];
    return $row;
}

// 2. DATA WALI MURID
function siswa_wali(string $nisn): ?array
{
    $stmt = db()->prepare(
        'SELECT w.Kode_wali, w.Nama_ayah, w.Pekerjaan_ayah,
                w.Nama_ibu, w.Pekerjaan_ibu,
                w.Alamat_wali, w.Telp_wali
           FROM siswa s
           JOIN wali_murid w ON w.Kode_wali = s.Kode_wali
          WHERE s.NISN = :nisn
          LIMIT 1'
    );
    $stmt->execute([':nisn' => $nisn]);
    return $stmt->fetch() ?: null;
}

// 3. RAPOR — DAFTAR MAPEL + NILAI PER SK
function siswa_rapor(string $nisn, string $kodeKK): array
{
    // Ambil Tingkat siswa untuk filter SK yang sesuai kelasnya
    $stSiswa = db()->prepare('SELECT Tingkat FROM siswa WHERE NISN = :nisn');
    $stSiswa->execute([':nisn' => $nisn]);
    $tingkatSiswa = (string) ($stSiswa->fetchColumn() ?: 'XII');

    $stmtMapel = db()->prepare(
        'SELECT mp.Kode_mata_pelajaran, mp.Nama_mata_pelajaran
           FROM mata_pelajaran mp
          WHERE mp.Kode_KK = :kk
          ORDER BY mp.Nama_mata_pelajaran ASC'
    );
    $stmtMapel->execute([':kk' => $kodeKK]);
    $daftarMapel = $stmtMapel->fetchAll();

    if (!$daftarMapel) {
        return [];
    }

    $stmtSK = db()->prepare(
        'SELECT sk.Kode_SK, sk.Kode_mata_pelajaran, sk.Nama_SK,
                sk.Kelas_SK, sk.Nilai_kkm
           FROM standar_kompetensi sk
          WHERE sk.Kode_KK = :kk AND sk.Kelas_SK = :tingkat
          ORDER BY sk.Kode_SK ASC'
    );
    $stmtSK->execute([':kk' => $kodeKK, ':tingkat' => $tingkatSiswa]);
    $daftarSK = $stmtSK->fetchAll();

    $stmtNilai = db()->prepare(
        'SELECT n.Kode_SK, n.Kelas_SK, n.Nilai_angka, n.Nilai_huruf
           FROM nilai n
          WHERE n.NISN = :nisn AND n.Kelas_SK = :tingkat'
    );
    $stmtNilai->execute([':nisn' => $nisn, ':tingkat' => $tingkatSiswa]);
    $nilaiRows = $stmtNilai->fetchAll();

    $nilaiMap = [];
    foreach ($nilaiRows as $nr) {
        $nilaiMap[$nr['Kode_SK']] = [
            'angka' => $nr['Nilai_angka'] !== null ? (float) $nr['Nilai_angka'] : null,
            'huruf' => $nr['Nilai_huruf'] ?? '-',
        ];
    }

    $skPerMapel = [];
    foreach ($daftarSK as $sk) {
        $skPerMapel[$sk['Kode_mata_pelajaran']][] = $sk;
    }

    $hasil = [];
    foreach ($daftarMapel as $mp) {
        $kodeMP = $mp['Kode_mata_pelajaran'];
        $skList = $skPerMapel[$kodeMP] ?? [];

        $kkm = 75.0;
        if (!empty($skList)) {
            $kkm = (float) ($skList[0]['Nilai_kkm'] ?? 75);
        }

        $skHasil    = [];
        $nilaiValid = [];
        $totalSK    = count($skList);
        $skTuntas   = 0;

        foreach ($skList as $sk) {
            $kodeSK = $sk['Kode_SK'];
            $angka  = $nilaiMap[$kodeSK]['angka'] ?? null;
            $huruf  = '-';

            if ($angka !== null) {
                $huruf = nilai_ke_huruf($angka);
                $nilaiValid[] = $angka;
                if ($angka >= ($sk['Nilai_kkm'] ?? 75)) {
                    $skTuntas++;
                }
            }

            $skHasil[] = [
                'kode_sk' => $kodeSK,
                'nama'    => $sk['Nama_SK'],
                'kkm'     => (float) ($sk['Nilai_kkm'] ?? 75),
                'nilai'   => $angka,
                'huruf'   => $huruf,
            ];
        }

        $rerata   = null;
        $predikat = '-';
        if (!empty($nilaiValid)) {
            $rerata   = round(array_sum($nilaiValid) / count($nilaiValid), 1);
            $predikat = nilai_ke_huruf($rerata);
        }

        $hasil[] = [
            'kode_mapel' => $kodeMP,
            'nama_mapel' => $mp['Nama_mata_pelajaran'],
            'kkm'        => $kkm,
            'total_sk'   => $totalSK,
            'sk_tuntas'  => $skTuntas,
            'sk_dinilai' => count($nilaiValid),
            'rerata'     => $rerata,
            'predikat'   => $predikat,
            'sk_list'    => $skHasil,
        ];
    }

    return $hasil;
}

// 4. STATISTIK DASHBOARD
function siswa_statistik(string $nisn, string $kodeKK): array
{
    $rapor = siswa_rapor($nisn, $kodeKK);

    $totalMapel       = count($rapor);
    $totalSK          = 0;
    $skTuntas         = 0;
    $skDinilai        = 0;
    $rerataMapel      = [];
    $mapelBelumTuntas = 0;

    foreach ($rapor as $mp) {
        $totalSK   += $mp['total_sk'];
        $skTuntas  += $mp['sk_tuntas'];
        $skDinilai += $mp['sk_dinilai'];

        if ($mp['rerata'] !== null) {
            $rerataMapel[] = $mp['rerata'];
            if ($mp['rerata'] < $mp['kkm']) {
                $mapelBelumTuntas++;
            }
        }
    }

    $rataRata = null;
    $predikat = '-';
    if (!empty($rerataMapel)) {
        $rataRata = round(array_sum($rerataMapel) / count($rerataMapel), 1);
        $predikat = nilai_ke_huruf($rataRata);
    }

    $persenTuntas = $totalSK > 0 ? (int) round($skTuntas / $totalSK * 100) : 0;
    $isTuntas     = ($mapelBelumTuntas === 0 && !empty($rerataMapel));

    $nilaiTerbaru = siswa_nilai_terbaru($nisn, 3);

    return [
        'rata_rata'          => $rataRata,
        'predikat'           => $predikat,
        'total_mapel'        => $totalMapel,
        'total_sk'           => $totalSK,
        'sk_tuntas'          => $skTuntas,
        'sk_dinilai'         => $skDinilai,
        'persen_tuntas'      => $persenTuntas,
        'is_tuntas'          => $isTuntas,
        'mapel_belum_tuntas' => $mapelBelumTuntas,
        'nilai_terbaru'      => $nilaiTerbaru,
    ];
}

// 5. NILAI TERBARU
function siswa_nilai_terbaru(string $nisn, int $limit = 3): array
{
    $stmt = db()->prepare(
        'SELECT n.Nilai_angka, n.Nilai_huruf, n.Kode_SK,
                sk.Nama_SK, sk.Nilai_kkm,
                mp.Nama_mata_pelajaran
           FROM nilai n
           JOIN standar_kompetensi sk ON sk.Kode_SK = n.Kode_SK AND sk.Kelas_SK = n.Kelas_SK
           JOIN mata_pelajaran mp ON mp.Kode_mata_pelajaran = sk.Kode_mata_pelajaran
          WHERE n.NISN = :nisn
          ORDER BY n.id_nilai DESC
          LIMIT :lim'
    );
    $stmt->bindValue(':nisn', $nisn, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['Nilai_angka'] = $row['Nilai_angka'] !== null ? (float) $row['Nilai_angka'] : null;
        $row['Nilai_kkm']   = $row['Nilai_kkm'] !== null ? (float) $row['Nilai_kkm'] : 75.0;
        if ($row['Nilai_angka'] !== null) {
            $row['Nilai_huruf'] = nilai_ke_huruf($row['Nilai_angka']);
        }
    }
    unset($row);

    return $rows;
}