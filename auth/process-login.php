<?php
/**
 * =====================================================================
 * PROSES LOGIN
 * Menerima POST dari auth/login.php, memvalidasi kredensial ke database,
 * lalu mengarahkan user ke portal sesuai perannya.
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

// Hanya menerima metode POST.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect(base_url('auth/login.php'));
}

// Proteksi CSRF.
if (!csrf_verify($_POST['_token'] ?? null)) {
    set_flash('error', 'Sesi form tidak valid atau kedaluwarsa. Silakan coba lagi.');
    redirect(base_url('auth/login.php'));
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    set_flash('error', 'NIP/NISN dan password wajib diisi.');
    $_SESSION['old_username'] = $username;
    redirect(base_url('auth/login.php'));
}

try {
    $user = coba_login($username, $password);
} catch (Throwable $e) {
    set_flash('error', 'Terjadi kesalahan pada server. Silakan hubungi Admin Sekolah.');
    redirect(base_url('auth/login.php'));
}

if ($user === null) {
    set_flash('error', 'NIP/NISN atau password salah. Periksa kembali data Anda.');
    $_SESSION['old_username'] = $username;
    redirect(base_url('auth/login.php'));
}

unset($_SESSION['old_username']);

set_flash('success', 'Selamat datang kembali, ' . $user['nama'] . '.');

redirect(base_url(
    $user['role'] === 'guru' ? 'app/guru/dashboard.php' : 'app/siswa/dashboard.php'
));
