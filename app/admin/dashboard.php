<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin-repository.php';

// Guard: Wajib login role admin
$admin = wajib_login('admin');

$stat  = admin_statistik();
$flash = get_flash();

$pageTitle = "Dashboard Admin - E-Rapor Digital";
include __DIR__ . '/../../components/header.php';
?>

<!-- 1. Sidebar Khusus Admin -->
<?php include __DIR__ . '/../../components/sidebar-admin.php'; ?>

<!-- 2. Main Content Area -->
<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <?php include __DIR__ . '/../../components/flash.php'; ?>

    <!-- Top Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                Dashboard Administrator
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                <span class="text-slate-800 dark:text-slate-100 font-extrabold"><?= e($admin['nama']) ?></span>
                <span class="text-slate-400 dark:text-slate-600">•</span>
                <span><?= e(tanggal_indonesia()) ?></span>
                <span class="text-slate-400 dark:text-slate-600">•</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold text-rose-500 bg-rose-500/10 dark:text-rose-400 dark:bg-rose-950/60 border border-rose-500/20 dark:border-rose-800/40">
                    Periode: <?= e(TAHUN_AJARAN_AKTIF . ' ' . SEMESTER_AKTIF) ?>
                </span>
            </p>
        </div>

        <!-- Jam Realtime -->
        <div class="px-4 py-2.5 clay-card rounded-2xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hidden sm:flex items-center gap-2.5 shadow-xs">
            <svg class="w-4.5 h-4.5 text-brand-primary dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"></path>
            </svg>
            <span id="realtime-clock" class="font-extrabold tracking-wide">00:00:00 WIB</span>
        </div>
    </div>

    <!-- Ocean Banner Admin -->
    <div class="clay-block-ocean p-6 lg:p-7 rounded-[26px] text-white shadow-md">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-white text-[10px] font-black uppercase tracking-wider backdrop-blur-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                PUSAT PENGELOLAAN DATA MASTER
            </span>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white pt-1">
                Selamat Datang, Administrator!
            </h2>
            <p class="text-xs sm:text-sm text-sky-100/90 font-medium max-w-2xl leading-relaxed">
                Kelola data guru pengampu, data siswa terdaftar, mata pelajaran, serta alokasi penugasan kelas ajar.
            </p>
        </div>
    </div>

    <!-- Grid Card Ringkasan Statistik (Selalu Berjejer 4 Kolom) -->
    <div class="grid grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Card Total Guru -->
        <a href="guru.php" class="clay-card p-4 sm:p-5 rounded-[22px] shadow-xs hover:-translate-y-0.5 transition-all flex flex-col justify-between group min-w-0">
            <div class="flex items-start justify-between gap-2">
                <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block leading-tight">Total Guru</span>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-sky-500/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                    </svg>
                </div>
            </div>
            <div class="my-3 sm:my-4">
                <div class="flex items-baseline gap-1 sm:gap-1.5 flex-wrap">
                    <span class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-800 dark:text-slate-100"><?= $stat['total_guru'] ?></span>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">Guru</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between text-[11px] sm:text-xs font-extrabold text-brand-primary dark:text-sky-400">
                <span class="truncate">Kelola Data</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                </svg>
            </div>
        </a>

        <!-- Card Total Siswa -->
        <a href="siswa.php" class="clay-card p-4 sm:p-5 rounded-[22px] shadow-xs hover:-translate-y-0.5 transition-all flex flex-col justify-between group min-w-0">
            <div class="flex items-start justify-between gap-2">
                <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block leading-tight">Total Siswa</span>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="my-3 sm:my-4">
                <div class="flex items-baseline gap-1 sm:gap-1.5 flex-wrap">
                    <span class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-800 dark:text-slate-100"><?= $stat['total_siswa'] ?></span>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">Siswa</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between text-[11px] sm:text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                <span class="truncate">Kelola Data</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                </svg>
            </div>
        </a>

        <!-- Card Total Mapel -->
        <a href="kurikulum.php" class="clay-card p-4 sm:p-5 rounded-[22px] shadow-xs hover:-translate-y-0.5 transition-all flex flex-col justify-between group min-w-0">
            <div class="flex items-start justify-between gap-2">
                <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block leading-tight">Mata Pelajaran • <?= $stat['total_sk'] ?> SK</span>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                    </svg>
                </div>
            </div>
            <div class="my-3 sm:my-4">
                <div class="flex items-baseline gap-1 sm:gap-1.5 flex-wrap">
                    <span class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-800 dark:text-slate-100"><?= $stat['total_mapel'] ?></span>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">Mapel</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between text-[11px] sm:text-xs font-extrabold text-amber-600 dark:text-amber-400">
                <span class="truncate">Kelola Mapel</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                </svg>
            </div>
        </a>

        <!-- Card Penugasan Kelas -->
        <a href="guru.php" class="clay-card p-4 sm:p-5 rounded-[22px] shadow-xs hover:-translate-y-0.5 transition-all flex flex-col justify-between group min-w-0">
            <div class="flex items-start justify-between gap-2">
                <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block leading-tight">Penugasan Mengajar</span>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"></path>
                    </svg>
                </div>
            </div>
            <div class="my-3 sm:my-4">
                <div class="flex items-baseline gap-1 sm:gap-1.5 flex-wrap">
                    <span class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-800 dark:text-slate-100"><?= $stat['total_kelas_ajar'] ?></span>
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">Kelas</span>
                </div>
            </div>
            <div class="pt-2.5 sm:pt-3 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between text-[11px] sm:text-xs font-extrabold text-rose-600 dark:text-rose-400">
                <span class="truncate">Atur Penugasan</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                </svg>
            </div>
        </a>

    </div>

   

</main>

<!-- 3. Footer -->
<?php include __DIR__ . '/../../components/footer.php'; ?>
