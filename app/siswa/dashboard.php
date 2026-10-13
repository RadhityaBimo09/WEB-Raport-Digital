<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/siswa-repository.php';

// Guard: hanya siswa yang sudah login.
$user = wajib_login('siswa');

// Ambil profil lengkap dari database berdasarkan NISN
$profil = siswa_profil($user['kode']);
if (!$profil) {
    redirect(base_url('auth/logout.php'));
}

$siswa = [
    'nama'    => $profil['Nama_siswa'],
    'nisn'    => $profil['NISN'],
    'kelas'   => $profil['nama_kelas'],
    'jurusan' => $profil['Nama_kompetensi_keahlian'],
    'avatar'  => '../../assets/img/user-avatar.jpg',
];

// Ambil statistik dan nilai terbaru
$stat         = siswa_statistik($profil['NISN'], $profil['Kode_KK']);
$nilaiTerbaru = $stat['nilai_terbaru'];
$rataRata     = $stat['rata_rata'];

// Label deskripsi rata-rata
if ($rataRata !== null) {
    if ($rataRata >= 88) {
        $rataLabel = 'Sangat Baik';
    } elseif ($rataRata >= 78) {
        $rataLabel = 'Baik';
    } elseif ($rataRata >= 70) {
        $rataLabel = 'Cukup';
    } else {
        $rataLabel = 'Perlu Perbaikan';
    }
} else {
    $rataLabel = 'Belum Ada Nilai';
}

$pageTitle = "Dashboard Siswa - E-Rapor Digital";
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
                Dashboard
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                <strong class="text-slate-800 dark:text-slate-100 font-extrabold"><?= e($siswa['nama']) ?></strong>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <span>NISN: <strong class="text-slate-700 dark:text-slate-300"><?= e($siswa['nisn']) ?></strong></span>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <span>Kelas: <strong class="text-brand-primary dark:text-sky-400"><?= e($siswa['kelas']) ?></strong></span>
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

    <!-- Modul Highlight Stats -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        
        <!-- Ocean Banner Rata-Rata -->
        <div class="md:col-span-8 clay-block-ocean p-5 lg:p-6 rounded-[26px] text-white flex items-center justify-between gap-4 shadow-md">
            <div class="space-y-1">
                <span class="text-[10px] font-black uppercase tracking-widest text-sky-200/90 block">Rata-Rata Nilai Rapor</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl lg:text-4xl font-black tracking-tight"><?= $rataRata !== null ? number_format($rataRata, 1) : '-' ?></span>
                    <span class="text-xs font-extrabold px-2.5 py-0.5 bg-emerald-400/30 text-emerald-200 rounded-full border border-emerald-300/30"><?= e($rataLabel) ?></span>
                </div>
                <p class="text-xs text-sky-100/80 font-medium pt-1">
                    <?php if ($stat['total_sk'] > 0 && $stat['sk_tuntas'] === $stat['total_sk']): ?>
                        Seluruh nilai mata pelajaran tuntas di atas KKM Minimal.
                    <?php elseif ($stat['total_sk'] > 0): ?>
                        <?= $stat['sk_tuntas'] ?> dari <?= $stat['total_sk'] ?> SK sudah tuntas di atas KKM.
                    <?php else: ?>
                        Belum ada data penilaian pada periode ini.
                    <?php endif; ?>
                </p>
            </div>

            <!-- Tombol Rapor Digital -->
            <a href="rapor.php" class="px-4 py-3 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-2xl text-xs font-extrabold text-white flex items-center gap-2 transition-all hover:scale-105 active:scale-95 shadow-xs shrink-0 cursor-pointer border border-white/20">
                <svg class="w-4 h-4 text-sky-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Rapor Digital</span>
                <svg class="w-4 h-4 text-sky-100" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                </svg>
            </a>
        </div>

        <!-- Mint Block Progres SK -->
        <div class="md:col-span-4 clay-block-mint p-5 rounded-[26px] flex flex-col justify-between shadow-sm">
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-950/80 dark:text-emerald-200/80">Progres Ketuntasan</span>
                <span class="text-xs font-black text-emerald-950 dark:text-emerald-200 bg-white/60 dark:bg-black/30 px-2 py-0.5 rounded-lg"><?= $stat['persen_tuntas'] ?>%</span>
            </div>
            
            <div class="my-2">
                <p class="text-xl font-black text-emerald-950 dark:text-emerald-200"><?= $stat['sk_tuntas'] ?> / <?= $stat['total_sk'] ?> <span class="text-xs font-bold text-emerald-900/80 dark:text-emerald-300/80">SK Tuntas</span></p>
            </div>

            <div class="w-full h-2 bg-emerald-950/10 dark:bg-black/40 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-700 dark:bg-emerald-400 rounded-full" style="width: <?= $stat['persen_tuntas'] ?>%"></div>
            </div>
        </div>

    </div>

    <!-- Modul Nilai Penilaian Terbaru -->
    <div class="clay-card rounded-[28px] p-5 lg:p-6 flex-1 flex flex-col justify-between shadow-sm">
        <div>
            <!-- Header Nilai Terbaru -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">
                        Nilai Penilaian Terbaru
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-brand-primary bg-brand-primary/10 dark:bg-sky-400/10 dark:text-sky-400">
                        Terupdate
                    </span>
                </div>

                <a href="rapor.php" class="text-xs font-bold text-brand-primary dark:text-sky-400 hover:underline flex items-center gap-1">
                    <span>Lihat Transkrip Lengkap</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <!-- List Card Nilai -->
            <div class="space-y-3">
                <?php if (empty($nilaiTerbaru)): ?>
                    <div class="py-8 text-center">
                        <p class="text-xs font-extrabold text-slate-400">Belum ada nilai penilaian yang diinput oleh Guru Pengampu.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($nilaiTerbaru as $n): ?>
                        <div class="clay-card p-4 rounded-2xl flex items-center justify-between flex-wrap sm:flex-nowrap gap-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md border border-slate-200/60 dark:border-white/5">
                            
                            <!-- Info SK & Mapel -->
                            <div class="space-y-1 min-w-0 flex-1">
                                <span class="text-[10px] font-extrabold text-brand-primary dark:text-sky-400 tracking-wider uppercase block">
                                    <?= e($n['Nama_mata_pelajaran']) ?>
                                </span>
                                <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 leading-snug">
                                    <?= e($n['Nama_SK']) ?>
                                </h3>
                                <p class="text-[11px] font-semibold text-slate-400">
                                    KKM Minimal: <strong class="text-slate-600 dark:text-slate-300"><?= (int) $n['Nilai_kkm'] ?></strong>
                                </p>
                            </div>

                            <!-- Boks Nilai Angka & Huruf Predikat -->
                            <div class="flex items-center shrink-0">
                                <div class="clay-card p-1.5 pl-3.5 rounded-2xl flex items-center gap-3 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <div class="text-right">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block leading-none">NILAI</span>
                                        <span class="text-sm font-black text-slate-800 dark:text-slate-100 leading-tight block mt-1"><?= $n['Nilai_angka'] !== null ? (int) $n['Nilai_angka'] : '-' ?></span>
                                    </div>
                                    <div class="w-8 h-8 <?= gaya_predikat($n['Nilai_huruf']) ?> rounded-xl flex items-center justify-center font-black text-xs shadow-xs shrink-0">
                                        <?= e($n['Nilai_huruf']) ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs font-bold text-slate-400 flex-wrap gap-2">
            <span>Menampilkan <?= count($nilaiTerbaru) ?> nilai penilaian terbaru</span>
            <span class="text-[11px] font-semibold text-slate-400">Nilai di atas diinput langsung oleh Guru Pengampu</span>
        </div>
    </div>

</main>


<!-- 3. Footer -->
<?php include '../../components/footer.php'; ?>