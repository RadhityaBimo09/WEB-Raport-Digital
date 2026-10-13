<?php
/**
 * =====================================================================
 * REPOSITORY PORTAL ADMIN
 * ---------------------------------------------------------------------
 * Kumpulan query database untuk statistik admin dan manajemen master data.
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Ringkasan statistik untuk dashboard admin.
 */
function admin_statistik(): array
{
    $pdo = db();

    $totalGuru  = (int) $pdo->query('SELECT COUNT(*) FROM guru')->fetchColumn();
    $totalSiswa = (int) $pdo->query('SELECT COUNT(*) FROM siswa')->fetchColumn();
    $totalMapel = (int) $pdo->query('SELECT COUNT(*) FROM mata_pelajaran')->fetchColumn();
    $totalKK    = (int) $pdo->query('SELECT COUNT(*) FROM kompetensi_keahlian')->fetchColumn();
    $totalSK    = (int) $pdo->query('SELECT COUNT(*) FROM standar_kompetensi')->fetchColumn();
    $totalKelasAjar = (int) $pdo->query('SELECT COUNT(*) FROM pengajaran')->fetchColumn();

    return [
        'total_guru'       => $totalGuru,
        'total_siswa'      => $totalSiswa,
        'total_mapel'      => $totalMapel,
        'total_kk'         => $totalKK,
        'total_sk'         => $totalSK,
        'total_kelas_ajar' => $totalKelasAjar,
    ];
}

// --- GURU ---
function admin_semua_guru(): array
{
    $stmt = db()->query(
        'SELECT g.*, kk.Nama_kompetensi_keahlian
           FROM guru g
           LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = g.Kode_KK
          ORDER BY g.Nama_guru ASC'
    );
    return $stmt->fetchAll();
}

function admin_tambah_guru(array $d): bool
{
    $stmt = db()->prepare(
        'INSERT INTO guru (Kode_guru, Kode_KK, Nama_guru, NIP, Telp_guru, Email_guru, Alamat_guru, Password)
         VALUES (:kode, :kk, :nama, :nip, :telp, :email, :alamat, :pass)'
    );
    return $stmt->execute([
        ':kode'   => trim($d['Kode_guru']),
        ':kk'     => trim($d['Kode_KK']),
        ':nama'   => trim($d['Nama_guru']),
        ':nip'    => trim($d['NIP']),
        ':telp'   => trim($d['Telp_guru'] ?? ''),
        ':email'  => trim($d['Email_guru'] ?? ''),
        ':alamat' => trim($d['Alamat_guru'] ?? ''),
        ':pass'   => password_hash($d['Password'] ?? 'guru123', PASSWORD_DEFAULT),
    ]);
}

function admin_hapus_guru(string $kodeGuru): bool
{
    $stmt = db()->prepare('DELETE FROM guru WHERE Kode_guru = :kode');
    return $stmt->execute([':kode' => $kodeGuru]);
}

function admin_update_guru(array $d): bool
{
    $kodeLama = trim($d['Kode_guru_lama'] ?? $d['kode_guru'] ?? '');
    $kodeBaru = trim($d['Kode_guru'] ?? '');
    $nama     = trim($d['Nama_guru'] ?? '');
    $nip      = trim($d['NIP'] ?? '');
    $telp     = trim($d['Telp_guru'] ?? '');
    $email    = trim($d['Email_guru'] ?? '');
    $alamat   = trim($d['Alamat_guru'] ?? '');

    if ($kodeLama === '' || $kodeBaru === '' || $nama === '' || $nip === '') {
        throw new InvalidArgumentException('Kode guru, nama, dan NIP wajib diisi.');
    }

    $pdo = db();

    $st = $pdo->prepare('SELECT * FROM guru WHERE Kode_guru = :k');
    $st->execute([':k' => $kodeLama]);
    $lama = $st->fetch(PDO::FETCH_ASSOC);
    if (!$lama) {
        throw new InvalidArgumentException('Data guru tidak ditemukan.');
    }

    // KK dikunci: edit biodata tidak boleh pindah KK.
    $kodeKK = trim((string) ($lama['Kode_KK'] ?? ''));

    if ($kodeBaru !== $kodeLama) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM guru WHERE Kode_guru = :k');
        $st->execute([':k' => $kodeBaru]);
        if ((int) $st->fetchColumn() > 0) {
            throw new InvalidArgumentException('Kode guru baru sudah terdaftar.');
        }
    }

    $st = $pdo->prepare('SELECT COUNT(*) FROM guru WHERE NIP = :nip AND Kode_guru <> :k');
    $st->execute([':nip' => $nip, ':k' => $kodeLama]);
    if ((int) $st->fetchColumn() > 0) {
        throw new InvalidArgumentException('NIP sudah dipakai guru lain.');
    }

    $st = $pdo->prepare('SELECT COUNT(*) FROM kompetensi_keahlian WHERE Kode_KK = :k');
    $st->execute([':k' => $kodeKK]);
    if ((int) $st->fetchColumn() === 0) {
        throw new InvalidArgumentException('Kompetensi keahlian tidak valid.');
    }

    $kkLama = trim((string) ($lama['Kode_KK'] ?? ''));
    if ($kkLama !== '' && $kkLama !== $kodeKK) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM pengajaran WHERE Kode_guru = :k');
        $st->execute([':k' => $kodeLama]);
        $n = (int) $st->fetchColumn();
        if ($n > 0) {
            throw new InvalidArgumentException("Guru masih memiliki $n penugasan. Hapus penugasannya dulu sebelum pindah KK.");
        }
    }

    $st = $pdo->prepare(
        'UPDATE guru SET
            Kode_guru = :baru,
            Kode_KK = :kk,
            Nama_guru = :nama,
            NIP = :nip,
            Telp_guru = :telp,
            Email_guru = :email,
            Alamat_guru = :alamat
         WHERE Kode_guru = :lama'
    );
    return $st->execute([
        ':baru'   => $kodeBaru,
        ':kk'     => $kodeKK,
        ':nama'   => $nama,
        ':nip'    => $nip,
        ':telp'   => $telp,
        ':email'  => $email,
        ':alamat' => $alamat,
        ':lama'   => $kodeLama,
    ]);
}

/**
 * Update KK guru (antisipasi salah input admin).
 * Ditolak bila guru sudah punya penugasan dan KK baru beda dari KK lama,
 * supaya tidak tercipta penugasan lintas KK. Hapus penugasan dulu bila
 * memang ingin pindah KK.
 */
function admin_update_guru_kk(string $kodeGuru, string $kodeKKBaru): bool
{
    $kodeGuru = trim($kodeGuru);
    $kodeKKBaru = trim($kodeKKBaru);
    if ($kodeGuru === '' || $kodeKKBaru === '') {
        throw new InvalidArgumentException('Kode guru dan KK wajib diisi.');
    }
    $pdo = db();
    $st = $pdo->prepare('SELECT Kode_KK FROM guru WHERE Kode_guru = :k');
    $st->execute([':k' => $kodeGuru]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new InvalidArgumentException('Data guru tidak ditemukan.');
    }
    $kkLama = trim((string) ($row['Kode_KK'] ?? ''));
    if ($kkLama === $kodeKKBaru) {
        return true;
    }
    $st = $pdo->prepare('SELECT COUNT(*) FROM kompetensi_keahlian WHERE Kode_KK = :k');
    $st->execute([':k' => $kodeKKBaru]);
    if ((int) $st->fetchColumn() === 0) {
        throw new InvalidArgumentException('Kompetensi keahlian tidak valid.');
    }
    $st = $pdo->prepare('SELECT COUNT(*) FROM pengajaran WHERE Kode_guru = :k');
    $st->execute([':k' => $kodeGuru]);
    $n = (int) $st->fetchColumn();
    if ($n > 0) {
        throw new InvalidArgumentException("Guru masih memiliki $n penugasan. Hapus penugasannya dulu sebelum pindah KK.");
    }
    $st = $pdo->prepare('UPDATE guru SET Kode_KK = :kk WHERE Kode_guru = :k');
    return $st->execute([':kk' => $kodeKKBaru, ':k' => $kodeGuru]);
}

// --- KOMPETENSI KEAHLIAN ---
function admin_semua_kk(): array
{
    return db()->query('SELECT * FROM kompetensi_keahlian ORDER BY Nama_kompetensi_keahlian ASC')->fetchAll();
}

// --- SISWA ---
function admin_semua_siswa(?string $filterKk = null, ?string $filterTingkat = null, ?string $sortBy = 'nama_asc'): array
{
    $sql = 'SELECT s.*, kk.Nama_kompetensi_keahlian
              FROM siswa s
              LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = s.Kode_KK
             WHERE 1=1';
    $params = [];

    if ($filterKk !== null && $filterKk !== '' && $filterKk !== 'semua') {
        $sql .= ' AND s.Kode_KK = :kk';
        $params[':kk'] = $filterKk;
    }

    if ($filterTingkat !== null && $filterTingkat !== '' && $filterTingkat !== 'semua') {
        $sql .= ' AND s.Tingkat = :tingkat';
        $params[':tingkat'] = $filterTingkat;
    }

    switch ($sortBy) {
        case 'kelas_asc':
            $sql .= ' ORDER BY FIELD(s.Tingkat, "X", "XI", "XII") ASC, s.Jurusan ASC, s.Nama_siswa ASC';
            break;
        case 'kelas_desc':
            $sql .= ' ORDER BY FIELD(s.Tingkat, "XII", "XI", "X") ASC, s.Jurusan DESC, s.Nama_siswa ASC';
            break;
        case 'nis_asc':
            $sql .= ' ORDER BY s.NISN ASC';
            break;
        case 'nis_desc':
            $sql .= ' ORDER BY s.NISN DESC';
            break;
        case 'nama_desc':
            $sql .= ' ORDER BY s.Nama_siswa DESC';
            break;
        case 'nama_asc':
        default:
            $sql .= ' ORDER BY s.Nama_siswa ASC';
            break;
    }

    if (!empty($params)) {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    return db()->query($sql)->fetchAll();
}

function admin_tambah_siswa(array $d): bool
{
    $nisn = substr(preg_replace('/[^0-9]/', '', trim($d['NISN'] ?? '')), 0, 8);
    if ($nisn === '') {
        throw new InvalidArgumentException('NIS wajib diisi maksimal 8 digit angka.');
    }

    $kodeKk = trim($d['Kode_KK'] ?? 'KK-RPL');
    $jurusan = trim($d['Jurusan'] ?? '');
    if ($jurusan === '') {
        $jurusan = str_replace('KK-', '', $kodeKk);
    }

    $pdo = db();
    $kodeWali = null;

    // Cek apakah ada data wali yang diisi
    $namaAyah = trim($d['Nama_ayah'] ?? '');
    $namaIbu = trim($d['Nama_ibu'] ?? '');
    $telpWali = trim($d['Telp_wali'] ?? '');

    if ($namaAyah !== '' || $namaIbu !== '' || $telpWali !== '') {
        // Generate Kode_wali baru
        $stmtLastWali = $pdo->query("SELECT Kode_wali FROM wali_murid ORDER BY Kode_wali DESC LIMIT 1");
        $lastWali = $stmtLastWali->fetchColumn();
        
        if ($lastWali && preg_match('/W-(\d+)/', $lastWali, $m)) {
            $nextNum = intval($m[1]) + 1;
        } else {
            $nextNum = 1;
        }
        $kodeWali = 'W-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

        // Insert data wali
        $stmtWali = $pdo->prepare(
            'INSERT INTO wali_murid (Kode_wali, Nama_ayah, Pekerjaan_ayah, Nama_ibu, Pekerjaan_ibu, Alamat_wali, Telp_wali)
             VALUES (:kode, :ayah, :pek_ayah, :ibu, :pek_ibu, :alamat, :telp)'
        );
        $stmtWali->execute([
            ':kode'     => $kodeWali,
            ':ayah'     => $namaAyah,
            ':pek_ayah' => trim($d['Pekerjaan_ayah'] ?? ''),
            ':ibu'      => $namaIbu,
            ':pek_ibu'  => trim($d['Pekerjaan_ibu'] ?? ''),
            ':alamat'   => trim($d['Alamat_wali'] ?? ''),
            ':telp'     => $telpWali,
        ]);
    }

    // Insert siswa dengan Kode_wali
    $stmt = $pdo->prepare(
        'INSERT INTO siswa (NISN, Kode_wali, Kode_KK, Tingkat, Jurusan, Nama_siswa, Alamat_siswa, Tgl_lahir, Password)
         VALUES (:nisn, :wali, :kk, :tingkat, :jurusan, :nama, :alamat, :tgl, :pass)'
    );
    return $stmt->execute([
        ':nisn'    => $nisn,
        ':wali'    => $kodeWali,
        ':kk'      => $kodeKk,
        ':tingkat' => trim($d['Tingkat'] ?? 'XII'),
        ':jurusan' => $jurusan,
        ':nama'    => trim($d['Nama_siswa']),
        ':alamat'  => trim($d['Alamat_siswa'] ?? ''),
        ':tgl'     => !empty($d['Tgl_lahir']) ? $d['Tgl_lahir'] : null,
        ':pass'    => password_hash($d['Password'] ?? 'siswa123', PASSWORD_DEFAULT),
    ]);
}

function admin_update_siswa(array $d): bool
{
    $nisnLama = trim($d['NISN_lama'] ?? '');
    $nisnBaru = substr(preg_replace('/[^0-9]/', '', trim($d['NISN'] ?? '')), 0, 8);
    
    if ($nisnLama === '' || $nisnBaru === '') {
        throw new InvalidArgumentException('NIS wajib diisi.');
    }

    $kodeKk = trim($d['Kode_KK'] ?? 'KK-RPL');
    $jurusan = trim($d['Jurusan'] ?? '');
    if ($jurusan === '') {
        $jurusan = str_replace('KK-', '', $kodeKk);
    }

    $pdo = db();
    $kodeWali = trim($d['Kode_wali'] ?? '');

    // Cek apakah ada data wali yang diisi
    $namaAyah = trim($d['Nama_ayah'] ?? '');
    $namaIbu = trim($d['Nama_ibu'] ?? '');
    $telpWali = trim($d['Telp_wali'] ?? '');

    if ($namaAyah !== '' || $namaIbu !== '' || $telpWali !== '') {
        if ($kodeWali === '') {
            // Generate Kode_wali baru
            $stmtLastWali = $pdo->query("SELECT Kode_wali FROM wali_murid ORDER BY Kode_wali DESC LIMIT 1");
            $lastWali = $stmtLastWali->fetchColumn();
            
            if ($lastWali && preg_match('/W-(\d+)/', $lastWali, $m)) {
                $nextNum = intval($m[1]) + 1;
            } else {
                $nextNum = 1;
            }
            $kodeWali = 'W-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

            // Insert data wali baru
            $stmtWali = $pdo->prepare(
                'INSERT INTO wali_murid (Kode_wali, Nama_ayah, Pekerjaan_ayah, Nama_ibu, Pekerjaan_ibu, Alamat_wali, Telp_wali)
                 VALUES (:kode, :ayah, :pek_ayah, :ibu, :pek_ibu, :alamat, :telp)'
            );
            $stmtWali->execute([
                ':kode'     => $kodeWali,
                ':ayah'     => $namaAyah,
                ':pek_ayah' => trim($d['Pekerjaan_ayah'] ?? ''),
                ':ibu'      => $namaIbu,
                ':pek_ibu'  => trim($d['Pekerjaan_ibu'] ?? ''),
                ':alamat'   => trim($d['Alamat_wali'] ?? ''),
                ':telp'     => $telpWali,
            ]);
        } else {
            // Update data wali existing
            $stmtWali = $pdo->prepare(
                'UPDATE wali_murid SET
                    Nama_ayah = :ayah,
                    Pekerjaan_ayah = :pek_ayah,
                    Nama_ibu = :ibu,
                    Pekerjaan_ibu = :pek_ibu,
                    Alamat_wali = :alamat,
                    Telp_wali = :telp
                 WHERE Kode_wali = :kode'
            );
            $stmtWali->execute([
                ':kode'     => $kodeWali,
                ':ayah'     => $namaAyah,
                ':pek_ayah' => trim($d['Pekerjaan_ayah'] ?? ''),
                ':ibu'      => $namaIbu,
                ':pek_ibu'  => trim($d['Pekerjaan_ibu'] ?? ''),
                ':alamat'   => trim($d['Alamat_wali'] ?? ''),
                ':telp'     => $telpWali,
            ]);
        }
    } else {
        // Jika semua field wali dikosongkan, set Kode_wali = NULL
        $kodeWali = null;
    }

    // Update siswa
    $stmt = $pdo->prepare(
        'UPDATE siswa SET
            NISN = :nisn_baru,
            Kode_wali = :wali,
            Kode_KK = :kk,
            Tingkat = :tingkat,
            Jurusan = :jurusan,
            Nama_siswa = :nama,
            Alamat_siswa = :alamat
         WHERE NISN = :nisn_lama'
    );
    return $stmt->execute([
        ':nisn_lama' => $nisnLama,
        ':nisn_baru' => $nisnBaru,
        ':wali'      => $kodeWali,
        ':kk'        => $kodeKk,
        ':tingkat'   => trim($d['Tingkat'] ?? 'XII'),
        ':jurusan'   => $jurusan,
        ':nama'      => trim($d['Nama_siswa']),
        ':alamat'    => trim($d['Alamat_siswa'] ?? ''),
    ]);
}

function admin_hapus_siswa(string $nisn): bool
{
    $stmt = db()->prepare('DELETE FROM siswa WHERE NISN = :nisn');
    return $stmt->execute([':nisn' => $nisn]);
}

// --- PENUGASAN MENGAJAR ---
function admin_semua_pengajaran(): array
{
    $stmt = db()->query(
        'SELECT p.*, g.Nama_guru, mp.Nama_mata_pelajaran, kk.Nama_kompetensi_keahlian
           FROM pengajaran p
           JOIN guru g ON g.Kode_guru = p.Kode_guru
           JOIN mata_pelajaran mp ON mp.Kode_mata_pelajaran = p.Kode_mata_pelajaran
           JOIN kompetensi_keahlian kk ON kk.Kode_KK = p.Kode_KK
          ORDER BY p.id_pengajaran DESC'
    );
    return $stmt->fetchAll();
}

function admin_tambah_pengajaran(array $d): bool
{
    $kodeGuru = trim($d['Kode_guru'] ?? '');
    $kodeKK   = trim($d['Kode_KK'] ?? '');
    $kodeMapel = trim($d['Kode_mata_pelajaran'] ?? '');
    if ($kodeGuru === '' || $kodeKK === '' || $kodeMapel === '') {
        throw new InvalidArgumentException('Guru, jurusan, dan mata pelajaran wajib dipilih.');
    }

    // Aturan: satu guru hanya boleh mengajar 1 kompetensi keahlian.
    $st = db()->prepare('SELECT DISTINCT Kode_KK FROM pengajaran WHERE Kode_guru = :g');
    $st->execute([':g' => $kodeGuru]);
    $kkAda = array_values(array_filter(array_map('strval', (array) $st->fetchAll(PDO::FETCH_COLUMN))));
    if ($kkAda !== [] && !in_array($kodeKK, $kkAda, true)) {
        throw new InvalidArgumentException('Satu guru hanya bisa mengajar 1 kompetensi keahlian.');
    }

    // Jika belum ada penugasan, kunci ke KK induk guru (kolom guru.Kode_KK).
    if ($kkAda === []) {
        try {
            $stG = db()->prepare('SELECT Kode_KK FROM guru WHERE Kode_guru = :g');
            $stG->execute([':g' => $kodeGuru]);
            $kkGuru = trim((string) $stG->fetchColumn());
            if ($kkGuru !== '' && $kkGuru !== $kodeKK) {
                throw new InvalidArgumentException('Satu guru hanya bisa mengajar 1 kompetensi keahlian.');
            }
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            // Abaikan jika kolom guru.Kode_KK tidak tersedia; validasi penugasan di atas tetap berlaku.
        }
    }

    // Cegah mapel lintas KK (mis. Basis Data masuk ke Animasi) akibat cache/JS lama.
    $cek = db()->prepare('SELECT Kode_KK FROM mata_pelajaran WHERE Kode_mata_pelajaran = :m');
    $cek->execute([':m' => $kodeMapel]);
    $kkMapel = (string) $cek->fetchColumn();
    if ($kkMapel === '') {
        throw new InvalidArgumentException('Mata pelajaran tidak valid.');
    }
    if ($kkMapel !== $kodeKK) {
        throw new InvalidArgumentException('Mata pelajaran tidak termasuk jurusan/KK yang dipilih.');
    }

    $stmt = db()->prepare(
        'INSERT INTO pengajaran (Kode_guru, Kode_KK, Kode_mata_pelajaran, Tahun_ajaran, Semester)
         VALUES (:guru, :kk, :mp, :ta, :sm)'
    );
    return $stmt->execute([
        ':guru' => $kodeGuru,
        ':kk'   => $kodeKK,
        ':mp'   => $kodeMapel,
        ':ta'   => TAHUN_AJARAN_AKTIF,
        ':sm'   => SEMESTER_AKTIF,
    ]);
}

function admin_hapus_pengajaran(int $id): bool
{
    $stmt = db()->prepare('DELETE FROM pengajaran WHERE id_pengajaran = :id');
    return $stmt->execute([':id' => $id]);
}

// --- MATA PELAJARAN ---
function admin_semua_mapel(): array
{
    $stmt = db()->query(
        'SELECT mp.*, kk.Nama_kompetensi_keahlian,
                (SELECT COUNT(*) FROM standar_kompetensi sk WHERE sk.Kode_mata_pelajaran = mp.Kode_mata_pelajaran) AS total_sk,
                (SELECT COUNT(*) FROM pengajaran p WHERE p.Kode_mata_pelajaran = mp.Kode_mata_pelajaran) AS total_pengajaran
           FROM mata_pelajaran mp
           LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = mp.Kode_KK
          ORDER BY mp.Nama_mata_pelajaran ASC'
    );
    return $stmt->fetchAll();
}

function admin_tambah_mapel(array $d): bool
{
    $kode = strtoupper(trim($d['Kode_mata_pelajaran'] ?? ''));
    $nama = trim($d['Nama_mata_pelajaran'] ?? '');
    $kk   = trim($d['Kode_KK'] ?? '');

    if ($kode === '' || $nama === '' || $kk === '') {
        throw new InvalidArgumentException('Semua kolom mata pelajaran wajib diisi.');
    }

    $stmt = db()->prepare(
        'INSERT INTO mata_pelajaran (Kode_mata_pelajaran, Kode_KK, Nama_mata_pelajaran)
         VALUES (:kode, :kk, :nama)'
    );
    return $stmt->execute([
        ':kode' => $kode,
        ':kk'   => $kk,
        ':nama' => $nama,
    ]);
}

function admin_update_mapel(array $d): bool
{
    $kode = strtoupper(trim($d['Kode_mata_pelajaran'] ?? ''));
    $nama = trim($d['Nama_mata_pelajaran'] ?? '');
    $kk   = trim($d['Kode_KK'] ?? '');

    if ($kode === '' || $nama === '' || $kk === '') {
        throw new InvalidArgumentException('Semua kolom mata pelajaran wajib diisi.');
    }

    $stmt = db()->prepare(
        'UPDATE mata_pelajaran
            SET Kode_KK = :kk,
                Nama_mata_pelajaran = :nama
          WHERE Kode_mata_pelajaran = :kode'
    );
    return $stmt->execute([
        ':kode' => $kode,
        ':kk'   => $kk,
        ':nama' => $nama,
    ]);
}

function admin_hapus_mapel(string $kode): bool
{
    $stmt = db()->prepare('DELETE FROM mata_pelajaran WHERE Kode_mata_pelajaran = :kode');
    return $stmt->execute([':kode' => $kode]);
}

// --- STANDAR KOMPETENSI ---
function admin_semua_sk(?string $filterMapel = null, ?string $filterKK = null): array
{
    $sql = 'SELECT sk.*, mp.Nama_mata_pelajaran, kk.Nama_kompetensi_keahlian,
                   (SELECT COUNT(*) FROM nilai n WHERE n.Kode_SK = sk.Kode_SK AND n.Kelas_SK = sk.Kelas_SK) AS total_nilai
              FROM standar_kompetensi sk
              LEFT JOIN mata_pelajaran mp ON mp.Kode_mata_pelajaran = sk.Kode_mata_pelajaran
              LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = sk.Kode_KK';

    $where = [];
    $params = [];

    if ($filterMapel !== null && $filterMapel !== '' && $filterMapel !== 'semua') {
        $where[] = 'sk.Kode_mata_pelajaran = :mapel';
        $params[':mapel'] = $filterMapel;
    }

    if ($filterKK !== null && $filterKK !== '' && $filterKK !== 'semua') {
        $where[] = 'sk.Kode_KK = :kk';
        $params[':kk'] = $filterKK;
    }

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY sk.Kode_mata_pelajaran ASC, sk.Kode_SK ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function admin_sk_ada(string $kodeSk, string $kelasSk): bool
{
    $stmt = db()->prepare('SELECT 1 FROM standar_kompetensi WHERE Kode_SK = :sk AND Kelas_SK = :kelas LIMIT 1');
    $stmt->execute([':sk' => $kodeSk, ':kelas' => $kelasSk]);
    return (bool) $stmt->fetchColumn();
}

function admin_tambah_sk(array $d): bool
{
    $kodeSk    = strtoupper(trim($d['Kode_SK'] ?? ''));
    $kodeMapel = strtoupper(trim($d['Kode_mata_pelajaran'] ?? ''));
    $namaSk    = trim($d['Nama_SK'] ?? '');
    $kelas     = trim($d['Kelas_SK'] ?? 'XII');
    $kkm       = (float) ($d['Nilai_kkm'] ?? 75);

    if ($kodeSk === '' || $namaSk === '' || $kodeMapel === '') {
        throw new InvalidArgumentException('Kode SK, Nama SK, dan Mata Pelajaran wajib diisi.');
    }

    $kelas = in_array($kelas, ['X', 'XI', 'XII'], true) ? $kelas : 'XII';
    if (admin_sk_ada($kodeSk, $kelas)) {
        throw new InvalidArgumentException('Kode SK untuk tingkat kelas tersebut sudah dipakai.');
    }

    // Ambil Kode_KK dari tabel mata_pelajaran jika belum disediakan
    $kodeKk = trim($d['Kode_KK'] ?? '');
    if ($kodeKk === '') {
        $stmtMapel = db()->prepare('SELECT Kode_KK FROM mata_pelajaran WHERE Kode_mata_pelajaran = :k');
        $stmtMapel->execute([':k' => $kodeMapel]);
        $kodeKk = (string) $stmtMapel->fetchColumn();
        if (!$kodeKk) {
            $kodeKk = 'KK-RPL';
        }
    }

    $stmt = db()->prepare(
        'INSERT INTO standar_kompetensi (Kode_SK, Kode_KK, Kode_mata_pelajaran, Nama_SK, Kelas_SK, Nilai_kkm)
         VALUES (:sk, :kk, :mp, :nama, :kelas, :kkm)'
    );
    return $stmt->execute([
        ':sk'    => $kodeSk,
        ':kk'    => $kodeKk,
        ':mp'    => $kodeMapel,
        ':nama'  => $namaSk,
        ':kelas' => in_array($kelas, ['X', 'XI', 'XII'], true) ? $kelas : 'XII',
        ':kkm'   => $kkm,
    ]);
}

function admin_update_sk(array $d): bool
{
    $kodeSk    = strtoupper(trim($d['Kode_SK'] ?? ''));
    $kodeLama  = strtoupper(trim($d['Kode_SK_lama'] ?? $kodeSk));
    $kodeMapel = strtoupper(trim($d['Kode_mata_pelajaran'] ?? ''));
    $namaSk    = trim($d['Nama_SK'] ?? '');
    $kelas     = trim($d['Kelas_SK'] ?? 'XII');
    $kelasLama = trim($d['Kelas_SK_lama'] ?? $kelas);
    $kkm       = (float) ($d['Nilai_kkm'] ?? 75);

    if ($kodeSk === '' || $namaSk === '' || $kodeMapel === '') {
        throw new InvalidArgumentException('Kode SK, Nama SK, dan Mata Pelajaran wajib diisi.');
    }

    $kelas = in_array($kelas, ['X', 'XI', 'XII'], true) ? $kelas : 'XII';
    $kelasLama = in_array($kelasLama, ['X', 'XI', 'XII'], true) ? $kelasLama : $kelas;

    $kodeKk = trim($d['Kode_KK'] ?? '');
    if ($kodeKk === '') {
        $stmtMapel = db()->prepare('SELECT Kode_KK FROM mata_pelajaran WHERE Kode_mata_pelajaran = :k');
        $stmtMapel->execute([':k' => $kodeMapel]);
        $kodeKk = (string) $stmtMapel->fetchColumn();
        if (!$kodeKk) {
            $kodeKk = 'KK-RPL';
        }
    }

    if (($kodeSk !== $kodeLama || $kelas !== $kelasLama) && admin_sk_ada($kodeSk, $kelas)) {
        throw new InvalidArgumentException('Kode SK untuk tingkat kelas tersebut sudah dipakai.');
    }

    $stmt = db()->prepare(
        'UPDATE standar_kompetensi
            SET Kode_SK = :sk,
                Kode_KK = :kk,
                Kode_mata_pelajaran = :mp,
                Nama_SK = :nama,
                Kelas_SK = :kelas,
                Nilai_kkm = :kkm
          WHERE Kode_SK = :sklama AND Kelas_SK = :kelaslama'
    );
    return $stmt->execute([
        ':sk'    => $kodeSk,
        ':kk'    => $kodeKk,
        ':mp'    => $kodeMapel,
        ':nama'  => $namaSk,
        ':kelas' => $kelas,
        ':kkm'   => $kkm,
        ':sklama' => $kodeLama,
        ':kelaslama' => $kelasLama,
    ]);
}

function admin_hapus_sk(string $kodeSk, string $kelasSk): bool
{
    $stmt = db()->prepare('DELETE FROM standar_kompetensi WHERE Kode_SK = :sk AND Kelas_SK = :kelas');
    return $stmt->execute([':sk' => $kodeSk, ':kelas' => $kelasSk]);
}


