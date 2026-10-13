<?php
/**
 * =====================================================================
 * AUTENTIKASI & SESSION HANDLING
 * ---------------------------------------------------------------------
 * Satu file ini menangani: start session aman, login guru/siswa,
 * penjaga halaman (guard), dan logout.
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

/** Mulai session dengan cookie params yang lebih aman. */
function mulai_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Lax',
    ]);

    session_name('ERAPOR_SESS');
    session_start();
}

mulai_session();

/** Base URL project, contoh: http://localhost/pelaporan_nilai/ */
function base_url(string $path = ''): string
{
    static $base = null;

    if ($base === null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $root     = explode('/', trim($script, '/'))[0] ?? '';

        // Jika project berada langsung di document root, $root bisa berupa
        // nama folder modul (auth/app). Deteksi lewat posisi file config.
        if (in_array($root, ['auth', 'app', 'components', 'config', 'assets'], true)) {
            $root = '';
        }

        $base = $protocol . '://' . $host . '/' . ($root !== '' ? $root . '/' : '');
    }

    return $base . ltrim($path, '/');
}

/**
 * Verifikasi password.
 * Mendukung dua format:
 *  - hash password_hash() (produksi),
 *  - plaintext hasil seeding SQL (otomatis di-upgrade ke hash saat login).
 */
function verifikasi_password(string $input, string $tersimpan): bool
{
    if ($tersimpan === '') {
        return false;
    }

    $info = password_get_info($tersimpan);

    if (!empty($info['algo'])) {
        return password_verify($input, $tersimpan);
    }

    return hash_equals($tersimpan, $input);
}

/** Re-hash password plaintext hasil seeding menjadi hash yang aman. */
function upgrade_password(string $tabel, string $kolomKunci, string $kunci, string $passwordBaru): void
{
    $allowed = [
        'guru'  => 'Kode_guru',
        'siswa' => 'NISN',
        'admin' => 'id_admin',
    ];

    if (!isset($allowed[$tabel]) || $allowed[$tabel] !== $kolomKunci) {
        return;
    }

    $kolomPass = ($tabel === 'admin') ? 'password' : 'Password';
    $sql = "UPDATE `{$tabel}` SET `{$kolomPass}` = :pass WHERE `{$kolomKunci}` = :kunci";
    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':pass'  => password_hash($passwordBaru, PASSWORD_DEFAULT),
        ':kunci' => $kunci,
    ]);
}

/**
 * Proses login khusus Admin (tabel `admin`).
 */
function coba_login_admin(string $username, string $password): ?array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return null;
    }

    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT id_admin, username, password
           FROM admin
          WHERE username = :u
          LIMIT 1'
    );
    $stmt->execute([':u' => $username]);
    $admin = $stmt->fetch();

    if ($admin && verifikasi_password($password, (string) $admin['password'])) {
        if (!password_get_info((string) $admin['password'])['algo']) {
            upgrade_password('admin', 'id_admin', (string) $admin['id_admin'], $password);
        }

        return simpan_session_user([
            'role'     => 'admin',
            'kode'     => $admin['id_admin'],
            'nama'     => 'Administrator',
            'username' => $admin['username'],
        ]);
    }

    return null;
}

/**
 * Proses login. Username diisi NIP (guru) atau NISN (siswa).
 * Mengembalikan array user bila sukses, null bila gagal.
 */
function coba_login(string $username, string $password): ?array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return null;
    }

    $pdo = db();

    // --- 1. Coba sebagai GURU (berdasarkan NIP) ---
    $stmt = $pdo->prepare(
        'SELECT Kode_guru, Kode_KK, Nama_guru, NIP, Email_guru, Alamat_guru, Telp_guru, Password
           FROM guru
          WHERE NIP = :u
          LIMIT 1'
    );
    $stmt->execute([':u' => $username]);
    $guru = $stmt->fetch();

    if ($guru && verifikasi_password($password, (string) $guru['Password'])) {
        if (!password_get_info((string) $guru['Password'])['algo']) {
            upgrade_password('guru', 'Kode_guru', (string) $guru['Kode_guru'], $password);
        }

        return simpan_session_user([
            'role'  => 'guru',
            'kode'  => $guru['Kode_guru'],
            'nama'  => $guru['Nama_guru'],
            'nip'   => $guru['NIP'],
            'email' => $guru['Email_guru'],
            'kk'    => $guru['Kode_KK'],
        ]);
    }

    // --- 2. Coba sebagai SISWA (berdasarkan NISN) ---
    $stmt = $pdo->prepare(
        'SELECT s.NISN, s.Kode_KK, s.Nama_siswa, s.Alamat_siswa, s.Tgl_lahir, s.Password,
                kk.Nama_kompetensi_keahlian
           FROM siswa s
           LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = s.Kode_KK
          WHERE s.NISN = :u
          LIMIT 1'
    );
    $stmt->execute([':u' => $username]);
    $siswa = $stmt->fetch();

    if ($siswa && verifikasi_password($password, (string) $siswa['Password'])) {
        if (!password_get_info((string) $siswa['Password'])['algo']) {
            upgrade_password('siswa', 'NISN', (string) $siswa['NISN'], $password);
        }

        return simpan_session_user([
            'role'    => 'siswa',
            'kode'    => $siswa['NISN'],
            'nama'    => $siswa['Nama_siswa'],
            'nisn'    => $siswa['NISN'],
            'kk'      => $siswa['Kode_KK'],
            'jurusan' => $siswa['Nama_kompetensi_keahlian'],
        ]);
    }

    return null;
}

/** Tulis identitas user ke session (dengan regenerate id). */
function simpan_session_user(array $user): array
{
    session_regenerate_id(true);

    $_SESSION['user']       = $user;
    $_SESSION['login_time'] = time();

    return $user;
}

/** Data user aktif, null bila belum login. */
function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function sudah_login(): bool
{
    return isset($_SESSION['user']['role']);
}

function role_user(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Guard halaman. Dipanggil paling atas pada setiap halaman terproteksi.
 * $role = 'guru' | 'siswa'
 */
function wajib_login(string $role): array
{
    if (!sudah_login()) {
        set_flash('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        $loginPage = ($role === 'admin') ? 'auth/admin-login.php' : 'auth/login.php';
        redirect(base_url($loginPage));
    }

    if (role_user() !== $role) {
        // Sudah login tapi salah portal -> lempar ke portal yang benar.
        $roleAktif = role_user();
        if ($roleAktif === 'admin') {
            $tujuan = 'app/admin/dashboard.php';
        } elseif ($roleAktif === 'guru') {
            $tujuan = 'app/guru/dashboard.php';
        } else {
            $tujuan = 'app/siswa/dashboard.php';
        }
        redirect(base_url($tujuan));
    }

    return user();
}

/** Hapus session & cookie login. */
function logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
