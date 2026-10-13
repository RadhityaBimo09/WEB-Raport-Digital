<?php
/**
 * =====================================================================
 * PROSES LOGIN ADMIN
 * Menerima POST dari auth/admin-login.php, memvalidasi kredensial ke DB,
 * lalu mengarahkan ke portal admin.
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

// Hanya menerima POST.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect(base_url('auth/admin-login.php'));
}

// Proteksi CSRF.
if (!csrf_verify($_POST['_token'] ?? null)) {
    set_flash('error', 'Sesi form tidak valid atau kedaluwarsa. Silakan coba lagi.');
    redirect(base_url('auth/admin-login.php'));
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    set_flash('error', 'Username dan password wajib diisi.');
    $_SESSION['old_username'] = $username;
    redirect(base_url('auth/admin-login.php'));
}

try {
    $user = coba_login_admin($username, $password);
} catch (Throwable $e) {
    set_flash('error', 'Terjadi kesalahan pada server database.');
    redirect(base_url('auth/admin-login.php'));
}

if ($user === null) {
    set_flash('error', 'Username atau password admin salah.');
    $_SESSION['old_username'] = $username;
    redirect(base_url('auth/admin-login.php'));
}

unset($_SESSION['old_username']);

set_flash('success', 'Selamat datang di Panel Admin, ' . $user['nama'] . '.');
redirect(base_url('app/admin/dashboard.php'));