<!-- Single Entry Point untuk Seluruh JS Project -->
<?php
// Samakan dengan header.php agar path absolut benar di semua kedalaman folder.
if (!isset($baseUrl)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $projectRoot = explode('/', trim($scriptName, '/'))[0] ?? '';
    $baseUrl = $protocol . '://' . $host . '/' . ($projectRoot ? $projectRoot . '/' : '');
}
?>
    <script src="<?= $baseUrl ?>assets/scripts/main.js?v=<?= file_exists(__DIR__ . '/../assets/scripts/main.js') ? filemtime(__DIR__ . '/../assets/scripts/main.js') : '1.0' ?>"></script>
</body>
</html>