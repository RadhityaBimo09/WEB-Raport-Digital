<?php
/**
 * =====================================================================
 * KONFIGURASI KONEKSI DATABASE (PDO)
 * E-Rapor Digital SMK Bina Informatika
 * ---------------------------------------------------------------------
 * Ubah nilai konstanta di bawah sesuai environment (XAMPP/Laragon/dll).
 * =====================================================================
 */

declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'db_raport');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Identitas periode akademik aktif (dipakai filter pengajaran & panel kanan)
define('TAHUN_AJARAN_AKTIF', '2026/2027');
define('SEMESTER_AKTIF', 'Ganjil');

/**
 * Mengembalikan instance PDO tunggal (singleton sederhana).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Jangan bocorkan kredensial / stack trace ke browser.
        http_response_code(500);
        die(
            '<div style="font-family:system-ui;padding:32px;max-width:640px;margin:40px auto;'
            . 'border-radius:16px;background:#fff1f2;color:#881337;line-height:1.6">'
            . '<h2 style="margin:0 0 8px">Koneksi Database Gagal</h2>'
            . '<p style="margin:0">Pastikan service MySQL/MariaDB berjalan dan database '
            . '<strong>' . htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') . '</strong> sudah diimport '
            . 'dari <code>database/db_rapor_full.sql</code>.</p>'
            . '<p style="margin:12px 0 0;font-size:12px;opacity:.8">Detail: '
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>'
            . '</div>'
        );
    }

    return $pdo;
}
