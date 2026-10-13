<?php
// Deteksi Base URL Project secara otomatis
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
// Mengambil folder root project (contoh: /pelaporan_nilai)
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$projectRoot = explode('/', trim($scriptName, '/'))[0] ?? '';
$baseUrl = $protocol . '://' . $host . '/' . ($projectRoot ? $projectRoot . '/' : '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin">
    <title><?= $pageTitle ?? 'E-Rapor | SMK Bina Informatika' ?></title>

    <!-- Fontshare Chillax -->
    <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=chillax@300,400,500,600,700&display=swap">

    <!-- CSS Output Utama (Menggunakan Base URL Otomatis & Cache Buster) -->
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/output.css?v=<?= file_exists(__DIR__ . '/../assets/css/output.css') ? filemtime(__DIR__ . '/../assets/css/output.css') : '1.0' ?>">
</head>
<body class="flex flex-col lg:flex-row min-h-screen p-4 sm:p-5 lg:p-7 gap-5 lg:gap-6 font-sans bg-[#EBF2F8] dark:bg-[#0F1620] text-slate-800 dark:text-slate-100 transition-colors duration-300">