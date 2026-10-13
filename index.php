<?php
/**
 * Entry point aplikasi.
 * Mengarahkan user ke portal sesuai session, atau ke halaman login.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

if (sudah_login()) {
    $role = role_user();
    if ($role === 'admin') {
        $target = 'app/admin/dashboard.php';
    } elseif ($role === 'guru') {
        $target = 'app/guru/dashboard.php';
    } else {
        $target = 'app/siswa/dashboard.php';
    }
    redirect(base_url($target));
}

redirect(base_url('auth/login.php'));
