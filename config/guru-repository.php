<?php
/**
 =====================================================================
 * REPOSITORY PORTAL GURU (UPDATED)
 * ---------------------------------------------------------------------
 * Seluruh query yang dipakai modul guru dikumpulkan di sini supaya
 * halaman view tetap bersih dan logika mudah diuji ulang.
 *
 * Konsep data:
 *  - "Kelas Ajar" = 1 baris tabel `pengajaran`
 *    (Kode_guru x Kode_KK/kelas x Kode_mata_pelajaran x periode)
 *  - SK diambil dari `standar_kompetensi` milik mapel + KK tersebut
 *  - Siswa diambil dari `siswa` pada KK yang sama
 *  - Nilai disimpan di `nilai` (unik per NISN + Kode_SK)
 =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Daftar kelas ajar (mapel yang diampu) milik seorang guru.
 */
function guru_kelas_ajar(string $kodeGuru): array
{
    $sql = "
        SELECT
            p.id_pengajaran,
            p.Kode_KK,
            p.Kode_mata_pelajaran,
            p.Tingkat,
            mp.Nama_mata_pelajaran,
            kk.Nama_kompetensi_keahlian,
            (SELECT COUNT(*) FROM standar_kompetensi sk
              WHERE sk.Kode_KK = p.Kode_KK
                AND sk.Kode_mata_pelajaran = p.Kode_mata_pelajaran
                AND sk.Kelas_SK = p.Tingkat) AS total_sk,
            (SELECT MIN(sk.Nilai_kkm) FROM standar_kompetensi sk
              WHERE sk.Kode_KK = p.Kode_KK
                AND sk.Kode_mata_pelajaran = p.Kode_mata_pelajaran
                AND sk.Kelas_SK = p.Tingkat) AS kkm,
            (SELECT MAX(sk.Kelas_SK) FROM standar_kompetensi sk
              WHERE sk.Kode_KK = p.Kode_KK
                AND sk.Kode_mata_pelajaran = p.Kode_mata_pelajaran
                AND sk.Kelas_SK = p.Tingkat) AS tingkat_kelas,
            (SELECT COUNT(*) FROM siswa s WHERE s.Kode_KK = p.Kode_KK AND s.Tingkat = p.Tingkat) AS total_siswa
        FROM pengajaran p
        JOIN mata_pelajaran mp ON mp.Kode_mata_pelajaran = p.Kode_mata_pelajaran
        JOIN kompetensi_keahlian kk ON kk.Kode_KK = p.Kode_KK
        WHERE p.Kode_guru = :guru
          AND (p.Tahun_ajaran IS NULL OR p.Tahun_ajaran = :ta)
          AND (p.Semester IS NULL OR p.Semester = :sm)
        ORDER BY FIELD(p.Tingkat, 'X', 'XI', 'XII') ASC,
                 mp.Nama_mata_pelajaran ASC,
                 kk.Nama_kompetensi_keahlian ASC
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':guru' => $kodeGuru,
        ':ta'   => TAHUN_AJARAN_AKTIF,
        ':sm'   => SEMESTER_AKTIF,
    ]);

    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id_pengajaran'] = (int) $row['id_pengajaran'];
        $row['total_sk']      = (int) $row['total_sk'];
        $row['total_siswa']   = (int) $row['total_siswa'];
        $row['kkm']           = $row['kkm'] !== null ? (float) $row['kkm'] : 75.0;

        // --- PERBAIKAN NAMA KELAS DI SINI ---
        // Gunakan Tingkat dari pengajaran, fallback ke tingkat_kelas dari SK
        $tingkat = !empty($row['Tingkat']) ? $row['Tingkat'] : (!empty($row['tingkat_kelas']) ? $row['tingkat_kelas'] : 'XII');
        $row['nama_kelas'] = $tingkat . ' ' . $row['Nama_kompetensi_keahlian'];
    }
    unset($row);

    return $rows;
}
/**
 * Ambil satu kelas ajar milik guru tertentu.
 * Mengembalikan null bila id tidak valid ATAU bukan milik guru tersebut
 * (pencegahan akses data kelas guru lain lewat manipulasi URL).
 */
function guru_kelas_ajar_satu(string $kodeGuru, int$idPengajaran): ?array
{
    foreach (guru_kelas_ajar($kodeGuru) as$kelas) {
        if ($kelas['id_pengajaran'] ===$idPengajaran) {
            return $kelas;
        }
    }

    return null;
}

/** Daftar Standar Kompetensi untuk sebuah kelas ajar. */
function guru_daftar_sk(string $kodeKK, string $kodeMapel, string $tingkat): array
{
    $stmt = db()->prepare(
        'SELECT Kode_SK, Nama_SK, Kelas_SK, Nilai_kkm
           FROM standar_kompetensi
          WHERE Kode_KK = :kk 
            AND Kode_mata_pelajaran = :mp
            AND Kelas_SK = :tingkat
          ORDER BY Kode_SK ASC'
    );
    $stmt->execute([':kk' => $kodeKK, ':mp' => $kodeMapel, ':tingkat' => $tingkat]);

    return $stmt->fetchAll();
}

/** 
 * Daftar siswa pada sebuah kompetensi keahlian / kelas.
 * Tambahan: dapat difilter berdasarkan nama kelas jika kolom kelas ada di tabel siswa.
 */
function guru_daftar_siswa(string $kodeKK, string $tingkat): array
{
    $stmt = db()->prepare(
        'SELECT NISN, Nama_siswa, Tingkat, Jurusan
           FROM siswa
          WHERE Kode_KK = :kk AND Tingkat = :tingkat
          ORDER BY Nama_siswa ASC'
    );
    $stmt->execute([':kk' => $kodeKK, ':tingkat' => $tingkat]);

    return $stmt->fetchAll();
}

/**
 * Peta nilai satu SK: [NISN => ['angka' => float, 'huruf' => string]]
 */
function guru_nilai_per_sk(string $kodeSK, string $kelasSK = ''): array
{
    if ($kelasSK !== '') {
        $stmt = db()->prepare(
            'SELECT NISN, Nilai_angka, Nilai_huruf
               FROM nilai
              WHERE Kode_SK = :sk AND Kelas_SK = :kelas'
        );
        $stmt->execute([':sk' => $kodeSK, ':kelas' => $kelasSK]);
    } else {
        $stmt = db()->prepare(
            'SELECT NISN, Nilai_angka, Nilai_huruf
               FROM nilai
              WHERE Kode_SK = :sk'
        );
        $stmt->execute([':sk' => $kodeSK]);
    }

    $map = [];
    foreach ($stmt->fetchAll() as$row) {
        $map[$row['NISN']] = [
            'angka' => $row['Nilai_angka'] !== null ? (float) $row['Nilai_angka'] : null,
            'huruf' => $row['Nilai_huruf'],
        ];
    }

    return $map;
}

/**
 * Simpan / perbarui nilai satu Standar Kompetensi untuk banyak siswa.
 *
 * @param array $nilaiInput [NISN => nilai angka (string|int|float|'')]
 * @return array{tersimpan:int, terhapus:int, dilewati:int}
 */
function guru_simpan_nilai(
    string $kodeGuru,
    string $kodeKK,
    string $tingkat,
    string $kodeSK,
    array $nilaiInput
): array {
    $pdo = db();

    // Whitelist NISN yang benar-benar berada di kelas tersebut.
    $siswaValid = [];
    foreach (guru_daftar_siswa($kodeKK, $tingkat) as$s) {
        $siswaValid[$s['NISN']] = true;
    }

    $stmtUpsert =$pdo->prepare(
        'INSERT INTO nilai (NISN, Kode_guru, Kode_SK, Kelas_SK, Nilai_angka, Nilai_huruf)
         VALUES (:nisn, :guru, :sk, :kelas, :angka, :huruf)
         ON DUPLICATE KEY UPDATE
            Kode_guru   = VALUES(Kode_guru),
            Nilai_angka = VALUES(Nilai_angka),
            Nilai_huruf = VALUES(Nilai_huruf)'
    );

    $stmtHapus =$pdo->prepare(
        'DELETE FROM nilai WHERE NISN = :nisn AND Kode_SK = :sk AND Kelas_SK = :kelas'
    );

    $hasil = ['tersimpan' => 0, 'terhapus' => 0, 'dilewati' => 0];

    $pdo->beginTransaction();

    try {
        foreach ($nilaiInput as $nisn =>$angka) {
            $nisn = trim((string)$nisn);

            if (!isset($siswaValid[$nisn])) {$hasil['dilewati']++;
                continue;
            }

            $angka = trim((string)$angka);

            // Nilai dikosongkan -> hapus baris nilai (status belum dinilai).
            if ($angka === '') {
                $stmtHapus->execute([':nisn' =>$nisn, ':sk' => $kodeSK, ':kelas' => $tingkat]);$hasil['terhapus']++;
                continue;
            }

            if (!is_numeric($angka)) {$hasil['dilewati']++;
                continue;
            }

            $angka = (float)$angka;

            if ($angka < 0 || $angka > 100) {$hasil['dilewati']++;
                continue;
            }

            $stmtUpsert->execute([
                ':nisn'  => $nisn,
                ':guru'  => $kodeGuru,
                ':sk'    => $kodeSK,
                ':kelas' => $tingkat,
                ':angka' => $angka,
                ':huruf' => nilai_ke_huruf($angka),
            ]);

            $hasil['tersimpan']++;
        }

        $pdo->commit();
    } catch (Throwable $e) {$pdo->rollBack();
        throw $e;
    }

    return $hasil;
}

/**
 * Progres pengisian nilai per kelas ajar (dipakai panel kanan).
 * Persentase = jumlah sel nilai terisi / (jumlah SK x jumlah siswa).
 */
function guru_progres_input(string $kodeGuru): array
{
    $progres = [];

    foreach (guru_kelas_ajar($kodeGuru) as $kelas) {$target = $kelas['total_sk'] *$kelas['total_siswa'];

        $stmt = db()->prepare(             'SELECT COUNT(*) AS terisi                FROM nilai n                JOIN standar_kompetensi sk ON sk.Kode_SK = n.Kode_SK AND sk.Kelas_SK = n.Kelas_SK                JOIN siswa s ON s.NISN = n.NISN               WHERE sk.Kode_KK = :kk                 AND sk.Kode_mata_pelajaran = :mp                 AND sk.Kelas_SK = :tingkat                 AND s.Kode_KK = :kk2                 AND s.Tingkat = :tingkat2                 AND n.Nilai_angka IS NOT NULL'         );$stmt->execute([
            ':kk'  => $kelas['Kode_KK'],
            ':mp'  => $kelas['Kode_mata_pelajaran'],
            ':tingkat' => $kelas['Tingkat'],
            ':kk2' => $kelas['Kode_KK'],
            ':tingkat2' => $kelas['Tingkat'],
        ]);

        $terisi  = (int) ($stmt->fetch()['terisi'] ?? 0);
        $persen  =$target > 0 ? (int) round($terisi / $target * 100) : 0;

        $progres[] = [
            'id_pengajaran' => $kelas['id_pengajaran'],
            'label'         => $kelas['nama_kelas'] . ' — ' .$kelas['Nama_mata_pelajaran'],
            'persen'        => min(100, $persen),
            'terisi'        => $terisi,
            'target'        => $target,
        ];
    }

    return $progres;
}

/** Rata-rata persentase seluruh kelas ajar. */
function guru_progres_total(array $progres): int
{
    if (!$progres) {
        return 0;
    }

    $terisi = array_sum(array_column($progres, 'terisi'));
    $target = array_sum(array_column($progres, 'target'));

    return $target > 0 ? (int) round($terisi / $target * 100) : 0;
}

/**
 * Rekap nilai satu kelas ajar: kolom = daftar SK.
 *
 * @return array{kolom:array, baris:array}
 */
function guru_rekap_kelas(array $kelas): array
{
    $skList = guru_daftar_sk($kelas['Kode_KK'], $kelas['Kode_mata_pelajaran'], $kelas['Tingkat']);
    $siswa  = guru_daftar_siswa($kelas['Kode_KK'], $kelas['Tingkat']);

    $kolom = [];
    foreach ($skList as$i => $sk) {$kolom[] = [
            'key'   => $sk['Kode_SK'],
            'label' => 'SK ' . ($i + 1),             'judul' =>$sk['Nama_SK'],
        ];
    }

    $nilaiPerSK = [];
    foreach ($skList as $sk) {$nilaiPerSK[$sk['Kode_SK']] = guru_nilai_per_sk($sk['Kode_SK'], $sk['Kelas_SK']);
    }

    $baris = [];
    foreach ($siswa as$s) {
        $nilai = [];$valid = [];

        foreach ($skList as$sk) {
            $angka =$nilaiPerSK[$sk['Kode_SK']][$s['NISN']]['angka'] ?? null;
            $nilai[$sk['Kode_SK']] =$angka;

            if ($angka !== null) {
                $valid[] =$angka;
            }
        }

        $rata =$valid ? round(array_sum($valid) / count($valid), 1) : null;

        $baris[] = [
            'nisn'  => $s['NISN'],
            'nama'  => $s['Nama_siswa'],
            'nilai' => $nilai,
            'rata'  => $rata,
            'huruf' => $rata !== null ? nilai_ke_huruf($rata) : '-',
        ];
    }

    return ['kolom' => $kolom, 'baris' =>$baris];
}

/**
 * Rekap gabungan seluruh kelas ajar: kolom = mata pelajaran (rata-rata SK).
 *
 * @return array{kolom:array, baris:array}
 */
function guru_rekap_semua(array $daftarKelas): array
{
    $kolom    = [];$perKelas = [];

    foreach ($daftarKelas as $kelas) {$kolom[] = [
            'key'   => 'MP-' . $kelas['id_pengajaran'],
            'label' => $kelas['Nama_mata_pelajaran'],
            'judul' => $kelas['nama_kelas'] . ' — ' .$kelas['Nama_mata_pelajaran'],
        ];

        $perKelas['MP-' . $kelas['id_pengajaran']] = guru_rekap_kelas($kelas);
    }

    // Gabungkan berdasarkan NISN.
    $baris = [];
    foreach ($perKelas as $key =>$rekap) {
        foreach ($rekap['baris'] as$row) {
            if (!isset($baris[$row['nisn']])) {
                $baris[$row['nisn']] = [
                    'nisn'  => $row['nisn'],
                    'nama'  => $row['nama'],
                    'nilai' => [],
                    'rata'  => null,
                    'huruf' => '-',
                ];
            }

            $baris[$row['nisn']]['nilai'][$key] =$row['rata'];
        }
    }

    foreach ($baris as &$row) {
        $valid = array_filter($row['nilai'], static fn ($v) =>$v !== null);
        $row['rata']  =$valid ? round(array_sum($valid) / count($valid), 1) : null;
        $row['huruf'] = $row['rata'] !== null ? nilai_ke_huruf($row['rata']) : '-';
    }
    unset($row);

    $baris = array_values($baris);
    usort($baris, static fn ($a,$b) => strcmp($a['nama'],$b['nama']));

    return ['kolom' => $kolom, 'baris' =>$baris];
}