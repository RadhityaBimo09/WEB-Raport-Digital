<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/siswa-repository.php';

// Guard: hanya siswa yang sudah login.
$user = wajib_login('siswa');

// Ambil profil lengkap dari database
$profil = siswa_profil($user['kode']);
if (!$profil) {
    redirect(base_url('auth/logout.php'));
}

// Data transkrip rapor siswa & statistik
$raporMapel = siswa_rapor($profil['NISN'], $profil['Kode_KK']);
$stat       = siswa_statistik($profil['NISN'], $profil['Kode_KK']);

// 1. Nilai Rata-Rata Keseluruhan (IP Rapor)
$totalRerata = $stat['rata_rata'];

// 2. Keterangan Capaian Dinamis
$isTuntas     = $stat['is_tuntas'];
$keteranganCapaian = $isTuntas 
    ? 'Seluruh nilai mata pelajaran tuntas di atas KKM Minimal.' 
    : ($stat['mapel_belum_tuntas'] > 0 
        ? "Terdapat <strong class='text-rose-200 underline'>{$stat['mapel_belum_tuntas']} mata pelajaran</strong> yang belum mencapai KKM Minimal."
        : 'Belum ada data penilaian pada periode ini.');

$pageTitle = "Rapor Digital - E-Rapor Digital";
include '../../components/header.php';
?>

<!-- 1. Sidebar -->
<?php include '../../components/sidebar.php'; ?>

<!-- 2. Main Content Area -->
<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <!-- Top Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                Rapor / Hasil Belajar
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                Transkrip Nilai Capaian Standar Kompetensi (SK) — <?= e(SEMESTER_AKTIF) ?> <?= e(TAHUN_AJARAN_AKTIF) ?>
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <!-- Jam Realtime -->
            <div class="px-4 py-2.5 clay-card rounded-2xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2.5 shadow-xs">
                <svg class="w-4.5 h-4.5 text-brand-primary dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"></path>
                </svg>
                <span id="realtime-clock" class="font-extrabold tracking-wide">00:00:00 WIB</span>
            </div>
        </div>
    </div>

    <!-- Summary Banner -->
    <div class="clay-block-ocean p-5 lg:p-6 rounded-[26px] text-white flex items-center justify-between gap-4 shadow-md flex-wrap sm:flex-nowrap">
        <div class="space-y-1 min-w-0">
            <span class="text-[10px] font-black uppercase tracking-widest text-sky-200/90 block">Rata-Rata Keseluruhan (IP)</span>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight">Rata-Rata Rapor: <?= $totalRerata !== null ? number_format($totalRerata, 1) : '-' ?></h2>
            <p class="text-xs text-sky-100/80 font-medium">
                <?= $keteranganCapaian ?>
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="px-4 py-2.5 bg-white/20 backdrop-blur-md rounded-2xl text-center border border-white/20 shadow-xs">
                <span class="text-[9px] uppercase tracking-wider block opacity-80">Total Mapel</span>
                <span class="text-base font-black leading-none"><?= count($raporMapel) ?> Mapel</span>
            </div>
        </div>
    </div>

    <!-- Modul Utama Rapor + Search Bar -->
    <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm space-y-4">
        
        <!-- Header & Search Input Filter -->
        <div class="flex items-center justify-between flex-wrap gap-3 border-b border-slate-200/60 dark:border-slate-700/60 pb-3">
            <div>
                <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">
                    Nilai Capaian Mata Pelajaran
                </h2>
                <p class="text-xs font-semibold text-slate-400 mt-0.5">Klik baris mata pelajaran untuk melihat rincian Standar Kompetensi (SK).</p>
            </div>

            <!-- Field Search Bar -->
            <div class="relative w-full sm:w-72">
                <input 
                    type="text" 
                    id="search-mapel-input" 
                    onkeyup="filterRapor()" 
                    placeholder="Cari mapel atau materi SK..." 
                    class="w-full pl-9 pr-4 py-2.5 clay-inset rounded-2xl text-xs font-bold text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/40">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Container List Accordion Mapel -->
        <div id="mapel-list-container" class="space-y-3">
            <?php if (empty($raporMapel)): ?>
                <div class="py-8 text-center">
                    <p class="text-xs font-extrabold text-slate-400">Belum ada data mata pelajaran pada jurusan ini.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($raporMapel as $index => $m): 
                $skSearchText = implode(' ', array_column($m['sk_list'], 'nama'));
                $searchableContent = strtolower($m['nama_mapel'] . ' ' . $skSearchText);
            ?>
                <div class="mapel-card-item clay-card rounded-2xl border border-slate-200/80 dark:border-white/10 overflow-hidden shadow-2xs transition-all" data-search="<?= e($searchableContent) ?>">
                    
                    <!-- Header Baris Mapel (Clickable) -->
                    <button 
                        type="button" 
                        onclick="toggleMapelDetail(<?= $index ?>)" 
                        class="w-full p-4 flex items-center justify-between gap-4 text-left hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer select-none">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Badge Semester Murni -->
                            <span class="px-3 py-1 clay-inset rounded-xl text-xs font-black text-brand-primary dark:text-sky-400 shrink-0">
                                <?= e(SEMESTER_AKTIF) ?>
                            </span>
                            <div class="min-w-0">
                                <h3 class="mapel-name text-sm sm:text-base font-extrabold text-slate-800 dark:text-slate-100 truncate">
                                    <?= e($m['nama_mapel']) ?>
                                </h3>
                                <span class="text-[11px] font-bold text-slate-400 block">
                                    KKM Minimal: <?= (int) $m['kkm'] ?> • <?= $m['total_sk'] ?> Standar Kompetensi
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-right hidden sm:block">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Nilai Akhir</span>
                                <strong class="text-base font-black text-slate-800 dark:text-slate-100"><?= $m['rerata'] !== null ? $m['rerata'] : '-' ?></strong>
                            </div>

                            <span class="px-3 py-1 <?= gaya_predikat($m['predikat']) ?> rounded-xl text-xs font-black shadow-xs">
                                <?= e($m['predikat']) ?>
                            </span>

                            <svg id="arrow-icon-<?= $index ?>" class="w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <!-- Body Rincian SK (Expandable) -->
                    <div id="sk-detail-<?= $index ?>" class="hidden border-t border-slate-200/60 dark:border-slate-700/60 bg-slate-50/40 dark:bg-slate-900/30 p-4 space-y-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-2">Rincian Nilai Standar Kompetensi (SK)</span>
                        
                        <div class="divide-y divide-slate-200/50 dark:divide-slate-800">
                            <?php foreach ($m['sk_list'] as $sk): ?>
                                <div class="py-2 flex items-center justify-between text-xs gap-3">
                                    <span class="font-bold text-slate-700 dark:text-slate-300 min-w-0 truncate">
                                        <?= e($sk['nama']) ?>
                                    </span>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="font-black text-slate-800 dark:text-slate-100"><?= $sk['nilai'] !== null ? (int) $sk['nilai'] : '-' ?></span>
                                        <span class="px-2 py-0.5 <?= gaya_predikat($sk['huruf']) ?> rounded-md text-[10px] font-black">
                                            <?= e($sk['huruf']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pesan Jika Hasil Pencarian Tidak Ditemukan -->
        <div id="no-result-msg" class="hidden text-center py-8">
            <p class="text-xs font-extrabold text-slate-400">Mata pelajaran atau materi SK yang kamu cari tidak ditemukan.</p>
        </div>

    </div>

</main>


<!-- 3. Footer -->
<?php include '../../components/footer.php'; ?>