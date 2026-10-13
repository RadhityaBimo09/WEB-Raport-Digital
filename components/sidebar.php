<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Deteksi portal: utamakan role dari session, fallback ke struktur folder.
$isSiswa = (role_user() === 'siswa')
    || (role_user() === null && strpos($_SERVER['REQUEST_URI'], '/siswa/') !== false);

$sidebarUser = user();
$sidebarNama = $sidebarUser['nama'] ?? ($isSiswa ? 'Siswa' : 'Guru');
$sidebarRole = $isSiswa ? 'Siswa' : 'Guru Pengajar';

// Data Guru Real dari Database
$guruNipFormatted = '-';
$guruMapelSummary = '-';
$guruMapelFull    = '-';
$guruJurusanLabel = 'Guru Pengajar';
$guruInitials     = 'G';
$guruEmail        = '-';
$guruTelp         = '-';
$guruAlamat       = '-';

if (!$isSiswa && $sidebarUser) {
    $kodeGuru = $sidebarUser['kode'] ?? '';

    // Inisial Nama (misal: Budi Santoso -> BS)
    $words = explode(' ', trim($sidebarNama));
    $guruInitials = strtoupper(substr($words[0] ?? 'G', 0, 1) . substr($words[1] ?? '', 0, 1));
    if (strlen($guruInitials) === 1 && isset($words[0][1])) {
        $guruInitials = strtoupper(substr($words[0], 0, 2));
    }

    try {
        $stmtG = db()->prepare(
            'SELECT g.NIP, g.Kode_KK, g.Email_guru, g.Telp_guru, g.Alamat_guru,
                    kk.Nama_kompetensi_keahlian
               FROM guru g
               LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = g.Kode_KK
              WHERE g.Kode_guru = :k
              LIMIT 1'
        );
        $stmtG->execute([':k' => $kodeGuru]);
        $rowG = $stmtG->fetch();

        if ($rowG) {
            $guruEmail  = $rowG['Email_guru'] ?: '-';
            $guruTelp   = $rowG['Telp_guru'] ?: '-';
            $guruAlamat = $rowG['Alamat_guru'] ?: '-';

            $rawNip = trim((string)($rowG['NIP'] ?? ''));
            if (strlen($rawNip) === 18) {
                $guruNipFormatted = substr($rawNip, 0, 8) . ' ' . substr($rawNip, 8, 6) . ' ' . substr($rawNip, 14, 1) . ' ' . substr($rawNip, 15);
            } elseif ($rawNip !== '') {
                $guruNipFormatted = $rawNip;
            }

            $jurusanSingkat = str_replace('KK-', '', (string)($rowG['Kode_KK'] ?? ''));
            $guruJurusanLabel = 'Guru Pengajar ' . ($jurusanSingkat ?: 'Kejuruan');
        }

        // Ambil daftar mapel yang diajar
        $stmtA = db()->prepare(
            'SELECT DISTINCT mp.Nama_mata_pelajaran
               FROM pengajaran p
               JOIN mata_pelajaran mp ON mp.Kode_mata_pelajaran = p.Kode_mata_pelajaran
              WHERE p.Kode_guru = :k
              ORDER BY mp.Nama_mata_pelajaran ASC'
        );
        $stmtA->execute([':k' => $kodeGuru]);
        $rowsA = $stmtA->fetchAll();

        $mapels = [];
        foreach ($rowsA as $ra) {
            $mapels[] = $ra['Nama_mata_pelajaran'];
        }

        if (!empty($mapels)) {
            $guruMapelFull = implode(', ', $mapels);
            $cleanMapels = array_map(function($m) {
                if (stripos($m, 'Pemrograman Web') !== false) return 'Web';
                return $m;
            }, $mapels);
            $guruMapelSummary = implode(' & ', array_slice($cleanMapels, 0, 2));
        }
    } catch (Throwable $e) {
        // Fallback jika query gagal
    }
}
?>

<style>
  #sidebar:not(.is-expanded) .guru-profile-expanded { display: none !important; }
  #sidebar.is-expanded .guru-profile-collapsed { display: none !important; }
  #sidebar.is-expanded {
    width: 290px !important;
  }
</style>

<!-- Mobile Topbar -->
<div class="lg:hidden clay-card rounded-2xl px-3.5 py-3 flex items-center justify-between gap-2 shadow-sm">
    <div class="flex items-center gap-2.5">
        <img src="../../assets/img/logo-bi.svg" alt="Logo" class="w-9 h-9 rounded-[10px] object-cover shrink-0" onerror="this.src='https://ui-avatars.com/api/?name=B+I&background=2B76B9&color=fff'">
        <span class="font-extrabold text-slate-800 dark:text-slate-100 text-base">E-Rapor</span>
    </div>

    <nav class="flex items-center gap-1.5">
        <?php if ($isSiswa): ?>
            <a href="dashboard.php" title="Dashboard" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all <?= ($currentPage == 'dashboard.php') ? 'clay-block-ocean text-white font-bold shadow-xs' : 'text-slate-500 hover:text-brand-primary'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </a>
            <a href="profil.php" title="Profil Saya" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all <?= ($currentPage == 'profil.php') ? 'clay-block-ocean text-white font-bold shadow-xs' : 'text-slate-500 hover:text-brand-primary'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </a>
            <a href="rapor.php" title="Rapor Digital" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all <?= ($currentPage == 'rapor.php') ? 'clay-block-ocean text-white font-bold shadow-xs' : 'text-slate-500 hover:text-brand-primary'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </a>
        <?php else: ?>
            <a href="dashboard.php" title="Dashboard" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all <?= ($currentPage == 'dashboard.php') ? 'clay-block-ocean text-white font-bold shadow-xs' : 'text-slate-500 hover:text-brand-primary'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </a>
            <a href="input-nilai.php" title="Input Nilai" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all <?= ($currentPage == 'input-nilai.php') ? 'clay-block-ocean text-white font-bold shadow-xs' : 'text-slate-500 hover:text-brand-primary'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </a>
            <a href="rekap-nilai.php" title="Rekap Nilai" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all <?= ($currentPage == 'rekap-nilai.php') ? 'clay-block-ocean text-white font-bold shadow-xs' : 'text-slate-500 hover:text-brand-primary'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </a>
        <?php endif; ?>
        <button type="button" onclick="showLogoutModal()" title="Keluar" class="w-9 h-9 rounded-full overflow-hidden ml-1 shrink-0 border-0 flex items-center justify-center text-rose-500 cursor-pointer bg-transparent outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        </button>
    </nav>
</div>

<!-- Desktop Sidebar -->
<aside id="sidebar" class="hidden lg:flex relative clay-card rounded-[32px] py-6 px-3 flex-col transition-all duration-300 ease-in-out w-20 h-[calc(100vh-3rem)] sticky top-6 z-30 shrink-0">

  <!-- Header Section -->
  <div class="relative flex items-center justify-start w-full h-11 mb-6 shrink-0">
    <div class="flex items-center gap-3.5 sidebar-logo-wrap w-full">
      <img src="../../assets/img/logo-bi.svg" alt="Logo" class="w-10 h-10 rounded-[12px] object-cover shrink-0" onerror="this.src='https://ui-avatars.com/api/?name=B+I&background=2B76B9&color=fff'">

      <div class="sidebar-text-animated flex-col">
        <span class="font-extrabold text-slate-800 dark:text-slate-100 text-1xl whitespace-nowrap block leading-none">E-RAPOR</span>
        <span class="text-[9px] font-bold text-brand-primary/80 dark:text-sky-400 uppercase tracking-wider whitespace-nowrap mt-1">PIONEER OF IT SCHOOL</span>
      </div>
    </div>

    <!-- Floating Toggle Button -->
    <button id="sidebar-toggle" type="button" aria-label="Toggle Sidebar" class="absolute -right-[23px] top-1/2 -translate-y-1/2 w-7 h-7 bg-white dark:bg-slate-800 cursor-pointer border-0 rounded-full shadow-md flex items-center justify-center text-slate-600 dark:text-slate-200 hover:text-brand-primary dark:hover:text-sky-400 hover:scale-110 transition-all z-50 group outline-none">
      <svg id="icon-chevron-left" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      <svg id="icon-chevron-right" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
      <div id="sidebar-toggle-tooltip" class="sidebar-tooltip absolute left-full ml-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 bg-slate-800 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-xl pointer-events-none whitespace-nowrap z-50 transition-all duration-200">
        Buka Sidebar
      </div>
    </button>
  </div>

  <!-- Nav Items -->
  <nav class="space-y-2.5 flex-1 w-full flex flex-col items-center overflow-x-hidden overflow-y-auto">
    <?php if ($isSiswa): ?>
        <a href="dashboard.php" class="sidebar-nav-item relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'dashboard.php') ? 'is-active text-brand-primary dark:text-sky-400' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
          <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'dashboard.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </div>
          <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Dashboard</span>
        </a>

        <a href="profil.php" class="sidebar-nav-item relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'profil.php') ? 'is-active text-brand-primary dark:text-sky-400' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
          <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'profil.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
          <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Profil Saya</span>
        </a>

        <a href="rapor.php" class="sidebar-nav-item relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'rapor.php') ? 'is-active text-brand-primary dark:text-sky-400' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
          <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'rapor.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
          </div>
          <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Rapor Digital</span>
        </a>
    <?php else: ?>
        <a href="dashboard.php" class="sidebar-nav-item relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'dashboard.php') ? 'is-active text-brand-primary dark:text-sky-400' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
          <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'dashboard.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </div>
          <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Dashboard</span>
        </a>

        <a href="input-nilai.php" class="sidebar-nav-item relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'input-nilai.php') ? 'is-active text-brand-primary dark:text-sky-400' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
          <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'input-nilai.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          </div>
          <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Input Nilai</span>
        </a>

        <a href="rekap-nilai.php" class="sidebar-nav-item relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'rekap-nilai.php') ? 'is-active text-brand-primary dark:text-sky-400' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
          <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'rekap-nilai.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
          <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Rekap Nilai</span>
        </a>
    <?php endif; ?>
  </nav>

  <!-- Bottom Section -->
  <div class="mt-auto pt-3 space-y-3 w-full shrink-0">

    <div id="theme-toggle" class="sidebar-text-animated p-1 clay-inset rounded-full items-center gap-1 cursor-pointer my-2 w-full hidden">
      <div class="theme-option-light flex-1 py-1.5 px-3 rounded-full flex items-center justify-center gap-2 text-xs font-bold text-slate-800 bg-white shadow-xs">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><path stroke-linecap="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
        <span>Light</span>
      </div>
      <div class="theme-option-dark flex-1 py-1.5 px-3 rounded-full flex items-center justify-center gap-2 text-xs font-bold text-slate-400 bg-transparent">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
        <span>Dark</span>
      </div>
    </div>

    <button id="theme-toggle-collapsed" type="button" title="Ganti Tema" class="w-10 h-10 mx-auto clay-card rounded-2xl flex items-center justify-center text-slate-600 dark:text-slate-300 cursor-pointer my-2 border-0 outline-none">
      <svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><path stroke-linecap="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
      <svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
    </button>

    <?php if ($isSiswa): ?>
      <!-- Profil Siswa Sederhana -->
      <div class="relative w-full flex items-center justify-center p-1.5 rounded-2xl sidebar-profile-card">
        <button type="button" onclick="showLogoutModal()" title="Keluar Akun" class="relative w-10 h-10 flex items-center justify-center shrink-0 rounded-full overflow-hidden border-0 cursor-pointer bg-transparent p-0 outline-none">
          <img src="../../assets/img/user-avatar.jpg" alt="Avatar" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($sidebarNama) ?>&background=2B76B9&color=fff'">
        </button>

        <div class="sidebar-text-animated flex-col items-start flex-1 text-left overflow-hidden ml-3">
          <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 leading-tight truncate w-full"><?= e($sidebarNama) ?></span>
          <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 truncate w-full mt-0.5"><?= e($sidebarRole) ?></span>
        </div>

        <button type="button" onclick="showLogoutModal()" title="Keluar Akun" class="sidebar-text-animated p-2 rounded-xl text-slate-400 hover:text-rose-600 transition-all shrink-0 bg-transparent cursor-pointer border-0 outline-none">
          <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        </button>
      </div>

    <?php else: ?>
      <!-- Profil Guru Versi Collapsed (Mengecil) -->
      <div class="guru-profile-collapsed flex items-center justify-center w-full my-1">
        <button type="button" onclick="showLogoutModal()" title="Profil: <?= e($sidebarNama) ?> (Klik untuk Keluar)" class="relative w-10 h-10 rounded-2xl bg-gradient-to-br from-sky-500 to-brand-primary text-white font-black flex items-center justify-center text-xs shadow-md cursor-pointer border-0 outline-none hover:scale-105 transition-transform">
          <?= e($guruInitials) ?>
          
        </button>
      </div>

      <!-- Profil Guru Lengkap (Versi Expanded / Terbuka Sesuai Desain) -->
      <div class="guru-profile-expanded w-full">
        <div class="clay-card rounded-[24px] p-4 border border-slate-200/80 dark:border-white/10 shadow-sm relative w-full">
          
          <!-- Bagian Atas: Avatar + Nama + Role -->
          <div class="flex items-center gap-3">
            <div class="relative w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-brand-primary text-white font-black flex items-center justify-center text-base shadow-md shrink-0">
              <?= e($guruInitials) ?>
              
            </div>

            <div class="min-w-0 flex-1">
              <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 truncate leading-snug">
                <?= e($sidebarNama) ?>
              </h4>
              <span class="text-xs font-bold text-brand-primary dark:text-sky-400 block mt-0.5 truncate">
                <?= e($guruJurusanLabel) ?>
              </span>
            </div>
          </div>

          <!-- Bagian Tengah: Info NIP, Mapel (Ukuran Lebih Besar & Jelas) -->
          <div class="border-t border-slate-200/60 dark:border-slate-700/60 mt-3.5 pt-3 space-y-2.5 text-xs">
            
            <!-- Row NIP -->
            <div class="flex items-center justify-between gap-2">
              <span class="flex items-center gap-2 text-slate-400 font-bold shrink-0 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"></path></svg>
                NIP
              </span>
              <span class="font-extrabold text-slate-700 dark:text-slate-200 font-mono tracking-tight text-xs">
                <?= e($guruNipFormatted) ?>
              </span>
            </div>

            <!-- Row Mapel -->
            <div class="flex items-center justify-between gap-2">
              <span class="flex items-center gap-2 text-slate-400 font-bold shrink-0 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Mapel
              </span>
              <span class="font-extrabold text-slate-700 dark:text-slate-200 truncate max-w-[130px] text-right text-xs" title="<?= e($guruMapelFull) ?>">
                <?= e($guruMapelSummary) ?>
              </span>
            </div>

          </div>

          <!-- Bagian Bawah: Tombol Aksi -->
          <div class="border-t border-slate-200/60 dark:border-slate-700/60 mt-3.5 pt-3 flex items-center gap-2">
            <button type="button" onclick="showGuruProfileModal()" class="flex-1 py-2.5 px-3 rounded-xl clay-card text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-brand-primary dark:hover:text-sky-400 transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs border border-slate-200/60 dark:border-white/5 outline-none">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              <span>Profil Guru</span>
            </button>

            <button type="button" onclick="showLogoutModal()" title="Keluar Akun" class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 transition-all shrink-0 bg-transparent cursor-pointer border-0 outline-none">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
          </div>

        </div>
      </div>
    <?php endif; ?>

  </div>

</aside>
<!-- TAG ASIDE SELESAI DI SINI -->

<!-- ======================================================== -->
<!-- MODAL DETAIL PROFIL GURU -->
<!-- ======================================================== -->
<?php if (!$isSiswa): ?>
<div id="guru-profile-modal-overlay" class="hidden fixed inset-0 z-[99999] bg-slate-900/40 backdrop-blur-[2px]">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 shadow-2xl border border-slate-200/80 dark:border-white/10 relative z-10 animate-in fade-in zoom-in duration-150">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-200/60 dark:border-slate-700/60 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 to-brand-primary text-white font-black flex items-center justify-center text-sm shadow-md">
                        <?= e($guruInitials) ?>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100"><?= e($sidebarNama) ?></h3>
                        <p class="text-xs font-semibold text-brand-primary dark:text-sky-400"><?= e($guruJurusanLabel) ?></p>
                    </div>
                </div>
                <button type="button" onclick="hideGuruProfileModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 cursor-pointer border-0">
                    ✕
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-bold">NIP</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-200 font-mono"><?= e($guruNipFormatted) ?></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-bold">Email Sekolah</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-200"><?= e($guruEmail) ?></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-bold">No. Telepon</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-200"><?= e($guruTelp) ?></span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 font-bold">Mapel Diampu</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-200 text-right max-w-[200px]"><?= e($guruMapelFull) ?></span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-400 font-bold">Alamat</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-200 text-right max-w-[200px]"><?= e($guruAlamat) ?></span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-200/60 dark:border-slate-700/60">
                <button type="button" onclick="hideGuruProfileModal()" class="w-full py-2.5 rounded-xl font-bold text-xs text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer border-0">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- ======================================================== -->
<!-- MODAL POPUP LOGOUT -->
<!-- ======================================================== -->
<div id="logout-modal-overlay" class="hidden fixed inset-0 z-[99999] bg-slate-900/20 backdrop-blur-[1px]">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-auto p-4 flex justify-center items-center">
        <div class="clay-card bg-white dark:bg-slate-900 rounded-2xl p-6 !w-[340px] !max-w-[340px] text-center shadow-2xl border-0 outline-none relative z-10 animate-in fade-in zoom-in duration-150 mx-auto">
            <!-- Icon Container -->
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-4 border-0">
                <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            
            <!-- Text Content -->
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100 mb-1.5">Konfirmasi Keluar</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Apakah kamu yakin ingin keluar dari akun ini?</p>
            
            <!-- Actions -->
            <div class="flex items-center justify-center gap-3 w-full">
                <button type="button" onclick="hideLogoutModal()" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer border-0 outline-none">
                    Batal
                </button>
                <button type="button" onclick="confirmLogout()" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-rose-500 hover:bg-rose-600 shadow-md shadow-rose-500/20 transition-all cursor-pointer border-0 outline-none whitespace-nowrap">
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showLogoutModal() {
        const overlay = document.getElementById('logout-modal-overlay');
        if (!overlay) return;
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideLogoutModal() {
        const overlay = document.getElementById('logout-modal-overlay');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function confirmLogout() {
        window.location.href = '../../auth/logout.php';
    }

    function showGuruProfileModal() {
        const overlay = document.getElementById('guru-profile-modal-overlay');
        if (!overlay) return;
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideGuruProfileModal() {
        const overlay = document.getElementById('guru-profile-modal-overlay');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('logout-modal-overlay')?.addEventListener('click', function (e) {
        if (e.target === this) hideLogoutModal();
    });

    document.getElementById('guru-profile-modal-overlay')?.addEventListener('click', function (e) {
        if (e.target === this) hideGuruProfileModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('logout-modal-overlay');
            if (overlay && !overlay.classList.contains('hidden')) hideLogoutModal();

            const guruOverlay = document.getElementById('guru-profile-modal-overlay');
            if (guruOverlay && !guruOverlay.classList.contains('hidden')) hideGuruProfileModal();
        }
    });
</script>
</script>































































































