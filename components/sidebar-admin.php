<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$adminUser   = user();
?>

<!-- Mobile Navigation Bar (Tampil di layar mobile < lg) -->
<div class="lg:hidden w-full flex flex-col gap-2 mb-2">
  <header class="flex items-center justify-between p-3.5 clay-card rounded-2xl w-full">
    <div class="flex items-center gap-2.5">
      <img src="../../assets/img/logo-bi.svg" alt="Logo" class="w-8 h-8 rounded-xl object-cover shrink-0" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=E11D48&color=fff'">
      <div>
        <span class="font-extrabold text-slate-800 dark:text-slate-100 text-sm block leading-none">E-RAPOR</span>
        <span class="text-[9px] font-black text-rose-500 dark:text-rose-400 uppercase tracking-wider">PANEL ADMIN</span>
      </div>
    </div>
    <div class="flex items-center gap-1.5">
      <button type="button" onclick="toggleTheme()" title="Ganti Tema" class="p-2 rounded-xl text-slate-600 dark:text-amber-400 hover:bg-slate-200/50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer border-0 bg-transparent outline-none">
        <svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><path stroke-linecap="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
        <svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
      </button>
      <button type="button" onclick="showLogoutModal()" title="Keluar" class="p-2 rounded-xl text-rose-500 hover:bg-rose-500/10 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
      </button>
    </div>
  </header>

  <!-- Horizontal Scrollable Menu Mobile -->
  <nav class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-extrabold scrollbar-none">
    <a href="dashboard.php" class="px-3 py-2 rounded-xl whitespace-nowrap transition-all <?= ($currentPage == 'dashboard.php') ? 'clay-block-ocean text-white shadow-sm' : 'clay-card text-slate-600 dark:text-slate-300'; ?>">
      Dashboard
    </a>
    <a href="guru.php" class="px-3 py-2 rounded-xl whitespace-nowrap transition-all <?= ($currentPage == 'guru.php') ? 'clay-block-ocean text-white shadow-sm' : 'clay-card text-slate-600 dark:text-slate-300'; ?>">
      Guru
    </a>
    <a href="siswa.php" class="px-3 py-2 rounded-xl whitespace-nowrap transition-all <?= ($currentPage == 'siswa.php') ? 'clay-block-ocean text-white shadow-sm' : 'clay-card text-slate-600 dark:text-slate-300'; ?>">
      Siswa
    </a>
    <a href="kurikulum.php" class="px-3 py-2 rounded-xl whitespace-nowrap transition-all <?= ($currentPage == 'mapel.php' || $currentPage == 'kurikulum.php') ? 'clay-block-ocean text-white shadow-sm' : 'clay-card text-slate-600 dark:text-slate-300'; ?>">
      Mapel
    </a>
  </nav>
</div>

<!-- Desktop Sidebar (Tampil di lg ke atas) -->
<aside id="sidebar" class="hidden lg:flex relative clay-card rounded-[32px] py-6 px-3 flex-col transition-all duration-300 ease-in-out w-20 h-[calc(100vh-3rem)] sticky top-6 z-30 shrink-0">

  <!-- Header Section -->
  <div class="relative flex items-center justify-start w-full h-11 mb-6 shrink-0">
    <div class="flex items-center gap-3.5 sidebar-logo-wrap w-full">
      <img src="../../assets/img/logo-bi.svg" alt="Logo" class="w-10 h-10 rounded-[12px] object-cover shrink-0" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=E11D48&color=fff'">

      <div class="sidebar-text-animated flex-col">
        <span class="font-extrabold text-slate-800 dark:text-slate-100 text-base whitespace-nowrap block leading-none">E-RAPOR</span>
        <span class="text-[9px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider whitespace-nowrap mt-1">PANEL ADMIN</span>
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
    <a href="dashboard.php" class="sidebar-nav-item group relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'dashboard.php') ? 'is-active text-brand-primary dark:text-sky-400 font-extrabold' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
      <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'dashboard.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
      </div>
      <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Dashboard</span>
      <div class="nav-tooltip absolute left-full ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 hidden">
        Dashboard
      </div>
    </a>

    <a href="guru.php" class="sidebar-nav-item group relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'guru.php') ? 'is-active text-brand-primary dark:text-sky-400 font-extrabold' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
      <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'guru.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
      </div>
      <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Kelola Guru</span>
      <div class="nav-tooltip absolute left-full ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 hidden">
        Kelola Guru
      </div>
    </a>

    <a href="siswa.php" class="sidebar-nav-item group relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'siswa.php') ? 'is-active text-brand-primary dark:text-sky-400 font-extrabold' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
      <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'siswa.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
      </div>
      <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Kelola Siswa</span>
      <div class="nav-tooltip absolute left-full ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 hidden">
        Kelola Siswa
      </div>
    </a>

    <a href="kurikulum.php" class="sidebar-nav-item group relative flex items-center justify-center w-full min-h-[44px] rounded-2xl transition-all <?= ($currentPage == 'mapel.php' || $currentPage == 'kurikulum.php') ? 'is-active text-brand-primary dark:text-sky-400 font-extrabold' : 'text-slate-500 hover:bg-white/50 dark:hover:bg-white/5'; ?>">
      <div class="sidebar-icon-box w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-transform <?= ($currentPage == 'mapel.php' || $currentPage == 'kurikulum.php') ? 'clay-block-ocean text-white shadow-md font-extrabold' : ''; ?>">
        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
      </div>
      <span class="sidebar-text-animated font-bold text-sm pl-3 flex-1 truncate">Mata Pelajaran</span>
      <div class="nav-tooltip absolute left-full ml-3 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 hidden">
        Mata Pelajaran
      </div>
    </a>



  <!-- Bottom Section -->
  <div class="mt-auto pt-3 space-y-3 w-full shrink-0">

    <!-- Switcher Mode Gelap / Terang (Expanded) -->
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

    <!-- Tombol Mode Gelap / Terang (Collapsed) -->
    <button id="theme-toggle-collapsed" type="button" title="Ganti Tema" class="w-10 h-10 mx-auto clay-card rounded-2xl flex items-center justify-center text-slate-600 dark:text-slate-300 cursor-pointer my-2 border-0 outline-none">
      <svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><path stroke-linecap="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path></svg>
      <svg class="w-5 h-5 block dark:hidden text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
    </button>

    <!-- Profil Admin Sederhana (Mendukung Collapsed & Expanded) -->
    <div class="relative w-full flex items-center justify-center p-1.5 rounded-2xl border border-transparent sidebar-profile-card transition-all">
      <button type="button" onclick="showLogoutModal()" title="Profil: Administrator (Klik untuk Keluar)" class="relative w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 font-black flex items-center justify-center text-xs shrink-0 cursor-pointer border-0 outline-none hover:scale-105 transition-transform">
        AD
      </button>

      <div class="sidebar-text-animated flex-col items-start flex-1 text-left overflow-hidden ml-3 min-w-0">
        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 leading-tight truncate w-full block">Administrator</span>
        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 truncate w-full mt-0.5 block">Super Admin</span>
      </div>

      <button type="button" onclick="showLogoutModal()" title="Keluar Akun" class="sidebar-text-animated p-2 rounded-xl text-slate-400 hover:text-rose-600 transition-all shrink-0 bg-transparent cursor-pointer border-0 shadow-none outline-none">
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
      </button>
    </div>

  </div>

</aside>

<!-- MODAL LOGOUT ADMIN -->
<div id="logout-modal-overlay" class="hidden fixed inset-0 z-[99999] bg-slate-900/20 backdrop-blur-[1px]">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-auto p-4 flex justify-center items-center">
        <div class="clay-card bg-white dark:bg-slate-900 rounded-2xl p-6 !w-[340px] !max-w-[340px] text-center shadow-2xl border-0 relative z-10 mx-auto">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </div>
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100 mb-1.5">Keluar Panel Admin</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Apakah Anda yakin ingin keluar dari Sesi Admin?</p>
            <div class="flex items-center justify-center gap-3 w-full">
                <button type="button" onclick="hideLogoutModal()" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition-all cursor-pointer border-0">Batal</button>
                <button type="button" onclick="confirmLogout()" class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-rose-500 hover:bg-rose-600 shadow-md transition-all cursor-pointer border-0">Ya, Keluar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showLogoutModal() {
        const overlay = document.getElementById('logout-modal-overlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function hideLogoutModal() {
        const overlay = document.getElementById('logout-modal-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function confirmLogout() {
        window.location.href = '../../auth/logout.php';
    }

    document.getElementById('logout-modal-overlay')?.addEventListener('click', function (e) {
        if (e.target === this) hideLogoutModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('logout-modal-overlay');
            if (overlay && !overlay.classList.contains('hidden')) hideLogoutModal();
        }
    });
</script>
</script>