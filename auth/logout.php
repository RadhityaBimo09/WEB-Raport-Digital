<?php
/**
 * =====================================================================
 * LOGOUT
 * Mengosongkan seluruh data session dan memutar ulang session id,
 * lalu kembali ke halaman login dengan pesan konfirmasi.
 * =====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

// Cek role sebelum logout untuk menentukan tujuan redirect
$isAdmin = (role_user() === 'admin');

// Buang seluruh data user, lalu terbitkan session id baru
// (mencegah session fixation) agar flash message tetap dapat dikirim.
$_SESSION = [];
session_regenerate_id(true);

set_flash('success', 'Anda telah keluar dari akun.');

$tujuan = $isAdmin ? 'auth/admin-login.php' : 'auth/login.php';
redirect(base_url($tujuan));
