<?php
/**
 * =====================================================================
 * HELPER UMUM
 * Fungsi kecil yang dipakai lintas modul (escaping, konversi nilai,
 * flash message, redirect, CSRF).
 * =====================================================================
 */

declare(strict_types=1);

if (!function_exists('e')) {
    /** Escape output HTML. */
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('nilai_ke_huruf')) {
    /**
     * Konversi nilai angka -> nilai huruf.
     * Skala disamakan persis dengan assets/scripts/main.js (recalculateGrade)
     * agar badge di browser dan data tersimpan selalu konsisten.
     */
    function nilai_ke_huruf($angka): string
    {
        if ($angka === null || $angka === '') {
            return '-';
        }

        $angka = (float) $angka;

        if ($angka >= 88) return 'A';
        if ($angka >= 78) return 'B';
        if ($angka >= 70) return 'C';

        return 'D';
    }
}

if (!function_exists('gaya_predikat')) {
    /** Kelas CSS badge berdasarkan nilai huruf. */
    function gaya_predikat(?string $huruf): string
    {
        switch (strtoupper((string) $huruf)) {
            case 'A':
            case 'B':
                return 'clay-block-mint text-emerald-950';
            case 'C':
                return 'clay-block-amber text-amber-950';
            case '-':
                return 'clay-inset text-slate-500';
            default:
                return 'clay-block-alpha text-rose-950';
        }
    }
}

if (!function_exists('redirect')) {
    /** Redirect + hentikan eksekusi. */
    function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('set_flash')) {
    /** Simpan pesan sekali-tampil ke session. */
    function set_flash(string $tipe, string $pesan): void
    {
        $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
    }
}

if (!function_exists('get_flash')) {
    /** Ambil sekaligus hapus flash message. */
    function get_flash(): ?array
    {
        if (empty($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}

if (!function_exists('csrf_token')) {
    /** Token CSRF per-session. */
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /** Hidden input CSRF siap pakai di dalam <form>. */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /** Validasi token CSRF dari request POST. */
    function csrf_verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('tanggal_indonesia')) {
    /** Format tanggal Indonesia, contoh: Selasa, 08 September 2026. */
    function tanggal_indonesia(?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();

        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        return $hari[(int) date('w', $timestamp)]
            . ', ' . date('d', $timestamp)
            . ' ' . $bulan[(int) date('n', $timestamp)]
            . ' ' . date('Y', $timestamp);
    }
}
