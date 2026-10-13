<?php
/**
 * =====================================================================
 * INPUT NILAI SISWA (PORTAL GURU)
 * ---------------------------------------------------------------------
 * Alur:
 *  1. Tanpa parameter  -> tampilkan daftar kelas ajar milik guru.
 *  2. ?kelas=<id>      -> tampilkan form penilaian per Standar Kompetensi.
 *  3. Submit form      -> diproses oleh app/guru/simpan-nilai.php
 * =====================================================================
 */

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/guru-repository.php';

$user = wajib_login('guru');

$daftarKelas = guru_kelas_ajar($user['kode']);
$idKelas     = isset($_GET['kelas']) ? (int) $_GET['kelas'] : 0;

// Validasi kepemilikan kelas ajar (cegah akses kelas guru lain via URL).
$kelasAktif = $idKelas > 0 ? guru_kelas_ajar_satu($user['kode'], $idKelas) : null;

$skList    = [];
$skAktif   = null;
$siswaList = [];
$nilaiMap  = [];

if ($kelasAktif) {
    $skList = guru_daftar_sk($kelasAktif['Kode_KK'], $kelasAktif['Kode_mata_pelajaran'], $kelasAktif['Tingkat']);

    $kodeSKDiminta = isset($_GET['sk']) ? trim((string) $_GET['sk']) : '';

    foreach ($skList as $sk) {
        if ($sk['Kode_SK'] === $kodeSKDiminta) {
            $skAktif = $sk;
            break;
        }
    }

    // Default: SK pertama pada mata pelajaran tersebut.
    if ($skAktif === null && $skList) {
        $skAktif = $skList[0];
    }

    $siswaList = guru_daftar_siswa($kelasAktif['Kode_KK'], $kelasAktif['Tingkat']);

    if ($skAktif) {
        $nilaiMap = guru_nilai_per_sk($skAktif['Kode_SK'], $skAktif['Kelas_SK']);
    }
}

$flash = get_flash();

$pageTitle = "Input Nilai Siswa - E-Rapor Digital";
include '../../components/header.php';
?>

<!-- 1. Sidebar -->
<?php include '../../components/sidebar.php'; ?>

<!-- 2. Main Content Area -->
<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <?php include '../../components/flash.php'; ?>

    <!-- TOP HEADER UTAMA -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                Input Nilai Siswa
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                <?php if ($kelasAktif): ?>
                    <span>Kelas: <strong class="text-brand-primary dark:text-sky-400"><?= e($kelasAktif['nama_kelas']) ?></strong></span>
                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                    <span><?= e($kelasAktif['Nama_mata_pelajaran']) ?></span>
                <?php else: ?>
                    <span>Silakan pilih kelas &amp; mata pelajaran untuk mulai menginput nilai</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <!-- Jam Realtime -->
            <div class="px-4 py-2.5 clay-card rounded-2xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hidden sm:flex items-center gap-2.5 shadow-xs">
                <svg class="w-4.5 h-4.5 text-brand-primary dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"></path>
                </svg>
                <span id="realtime-clock" class="font-extrabold tracking-wide">00:00:00 WIB</span>
            </div>

            <!-- Tombol Navigasi Dinamis -->
            <?php if ($kelasAktif): ?>
                <a href="input-nilai.php" class="px-4 py-2.5 clay-card rounded-2xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 hover:text-brand-primary transition-all cursor-pointer shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span>Pilih Kelas Lain</span>
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="px-4 py-2.5 clay-card rounded-2xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 hover:text-brand-primary transition-all cursor-pointer shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Dashboard</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$kelasAktif): ?>
        <!-- ============ KONDISI A: PILIH KELAS AJAR ============ -->

        <!-- Compact Ocean Banner -->
        <div class="clay-block-ocean p-4 sm:p-5 rounded-[22px] text-white flex items-center justify-between gap-4 shadow-md flex-wrap sm:flex-nowrap">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/20">
                    <svg class="w-5 h-5 text-sky-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <h2 class="text-base sm:text-lg font-black tracking-tight truncate">Pilih Kelas &amp; Mata Pelajaran</h2>
                    <p class="text-xs text-sky-100/80 font-medium truncate">
                        Pilih salah satu kelas di bawah untuk memasukkan nilai Standar Kompetensi (SK).
                    </p>
                </div>
            </div>

            <div class="px-3 py-1.5 bg-white/20 backdrop-blur-md rounded-xl text-xs font-black text-white shrink-0 border border-white/20 shadow-2xs flex items-center gap-2">
                <span>Total:</span>
                <span class="px-2 py-0.5 bg-white/30 rounded-lg text-xs font-black"><?= count($daftarKelas) ?> Kelas</span>
            </div>
        </div>

        <!-- Section Grid Card Kelas Ajar -->
        <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-3">
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base">Daftar Kelas Ajar Aktif</h3>
                <span class="text-xs font-bold text-slate-400">Semester <?= e(SEMESTER_AKTIF) ?></span>
            </div>

            <?php if (!$daftarKelas): ?>
                <p class="py-10 text-center text-xs font-bold text-slate-400">
                    Belum ada kelas ajar aktif pada periode <?= e(TAHUN_AJARAN_AKTIF . ' — ' . SEMESTER_AKTIF) ?>.
                </p>
            <?php else: ?>
               <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
    <?php foreach ($daftarKelas as $m): ?>
        <div class="clay-card p-4 sm:p-5 rounded-[22px] border border-slate-200/80 dark:border-white/10 shadow-xs hover:-translate-y-0.5 hover:shadow-md transition-all flex flex-col justify-between gap-4">

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <!-- Badge Tingkat & Jurusan -->
                    <span class="px-3 py-1 clay-block-mint text-emerald-950 text-xs font-black rounded-xl">
                        <?= e($m['nama_kelas'] . ' ' . $m['Nama_kompetensi_keahlian']) ?>
                    </span>
                    <span class="text-xs font-extrabold text-slate-400">
                        KKM Minimal: <strong class="text-slate-700 dark:text-slate-200"><?= (int) $m['kkm'] ?></strong>
                    </span>
                </div>

                <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-100 leading-snug">
                    <?= e($m['Nama_mata_pelajaran']) ?>
                </h4>

                <p class="text-xs font-semibold text-slate-400">
                    Berisi <strong class="text-brand-primary dark:text-sky-400"><?= (int) $m['total_sk'] ?> Standar Kompetensi</strong>
                    &bull; <?= (int) $m['total_siswa'] ?> Siswa
                </p>
            </div>

            <a href="input-nilai.php?kelas=<?= (int) $m['id_pengajaran'] ?>" class="w-full py-2.5 clay-block-ocean text-white rounded-xl text-xs font-extrabold clay-btn flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Buka Form Penilaian</span>
            </a>

        </div>
    <?php endforeach; ?>
</div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ============ KONDISI B: FORM PENILAIAN ============ -->

        <!-- Modul Selection SK -->
        <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm">
            <form method="GET" action="input-nilai.php" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <input type="hidden" name="kelas" value="<?= (int) $kelasAktif['id_pengajaran'] ?>">

                <div class="md:col-span-9 space-y-1.5">
                    <label for="select-sk" class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest block pl-1">
                        Pilih Standar Kompetensi (SK)
                    </label>
                    <div class="relative">
                        <select id="select-sk" name="sk" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3.5 clay-inset rounded-2xl text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 appearance-none focus:outline-none focus:ring-2 focus:ring-brand-primary/40 cursor-pointer">
                            <?php foreach ($skList as $sk): ?>
                                <option value="<?= e($sk['Kode_SK']) ?>" <?= ($skAktif && $sk['Kode_SK'] === $skAktif['Kode_SK']) ? 'selected' : '' ?>>
                                    <?= e($sk['Nama_SK']) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!$skList): ?>
                                <option value="">Belum ada Standar Kompetensi pada mapel ini</option>
                            <?php endif; ?>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-3 flex justify-end pt-2 md:pt-5">
                    <div class="w-full clay-card px-4 py-2.5 rounded-2xl text-center flex items-center justify-between gap-3 shadow-sm border border-slate-200/80 dark:border-white/10">
                        <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">KKM Minimal</span>
                        <span class="text-base font-black text-brand-primary dark:text-sky-400 clay-inset px-3 py-1 rounded-xl">
                            <?= (int) ($skAktif['Nilai_kkm'] ?? $kelasAktif['kkm']) ?>
                        </span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Input Nilai Siswa -->
        <div class="clay-card rounded-[28px] p-5 lg:p-6 flex-1 flex flex-col justify-between shadow-sm">
            <form action="simpan-nilai.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="kelas" value="<?= (int) $kelasAktif['id_pengajaran'] ?>">
                <input type="hidden" name="sk" value="<?= e($skAktif['Kode_SK'] ?? '') ?>">

                <!-- Header Tabel Clean -->
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">
                            Daftar Siswa &amp; Penilaian
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-brand-primary bg-brand-primary/10 dark:bg-sky-400/10 dark:text-sky-400">
                            <?= count($siswaList) ?> Siswa
                        </span>
                    </div>

                    <?php if ($skAktif): ?>
                        <span class="text-[11px] font-bold text-slate-400 truncate max-w-full">
                            SK aktif: <?= e($skAktif['Kode_SK']) ?>
                        </span>
                    <?php endif; ?>
                </div>

            <!-- Scroll Dibatasi untuk ~8 Siswa (456px) -->
<div class="overflow-x-auto overflow-y-auto pr-1" style="max-height: 585px;">
    <table class="w-full text-left border-collapse min-w-[650px]">
         <thead class="sticky top-0 bg-slate-100/90 dark:bg-[#111823]/90 backdrop-blur-md z-10">
             <tr class="text-xs font-bold text-slate-400 border-b border-slate-200 dark:border-slate-700/80">
                 <th class="pb-3 pl-2 w-12">NO</th>
                 <th class="pb-3 w-36">NISN</th>
                  <th class="pb-3">NAMA SISWA</th>
                    <th class="pb-3 text-center w-48">NILAI ANGKA</th>
                 <th class="pb-3 text-center w-28">NILAI HURUF</th>
             </tr>
            </thead>
        <!-- tbody dst... -->
                        <tbody class="text-sm divide-y divide-slate-200/60 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
                            <?php if (!$siswaList || !$skAktif): ?>
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-xs font-bold text-slate-400">
                                        <?= !$skAktif
                                            ? 'Mata pelajaran ini belum memiliki Standar Kompetensi.'
                                            : 'Belum ada siswa terdaftar pada kelas ini.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($siswaList as $i => $s): ?>
                                <?php
                                    $angka = $nilaiMap[$s['NISN']]['angka'] ?? null;
                                    $huruf = $angka !== null ? nilai_ke_huruf($angka) : '-';
                                ?>
                                <tr class="hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                                    <td class="py-3.5 pl-2 font-bold text-slate-400"><?= $i + 1 ?></td>
                                    <td class="py-3.5 font-bold text-slate-500 dark:text-slate-400"><?= e($s['NISN']) ?></td>
                                    <td class="py-3.5 font-extrabold text-slate-800 dark:text-slate-100">
                                        <?= e($s['Nama_siswa']) ?>
                                    </td>

                                    <!-- Stepper Input Nilai Angka -->
                                    <td class="py-3.5 text-center">
                                        <div class="inline-flex items-center justify-center gap-1.5 p-1.5 clay-inset rounded-2xl">
                                            <button
                                                type="button"
                                                onclick="updateNilai(this, -1)"
                                                class="w-7 h-7 clay-block-alpha rounded-xl flex items-center justify-center text-rose-950 font-black active:scale-90 transition-all cursor-pointer select-none shadow-xs">
                                                -
                                            </button>
                                            <input
                                                type="number"
                                                name="nilai[<?= e($s['NISN']) ?>]"
                                                min="0"
                                                max="100"
                                                step="any"
                                                value="<?= $angka !== null ? e((string) (0 + $angka)) : '' ?>"
                                                placeholder="-"
                                                onchange="recalculateGrade(this)"
                                                oninput="recalculateGrade(this)"
                                                class="w-11 text-center py-0.5 bg-transparent font-black text-slate-800 dark:text-slate-100 focus:outline-none text-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            <button
                                                type="button"
                                                onclick="updateNilai(this, 1)"
                                                class="w-7 h-7 clay-block-ocean rounded-xl flex items-center justify-center text-white font-black active:scale-90 transition-all cursor-pointer select-none shadow-xs">
                                                +
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Badge Stat Card Nilai Huruf Dinamis -->
                                    <td class="py-3.5 text-center">
                                        <span class="grade-badge px-3.5 py-1.5 <?= gaya_predikat($huruf) ?> rounded-xl text-xs font-black inline-block shadow-xs transition-all">
                                            <?= e($huruf) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer & Action Button -->
                <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-3">
                    <span class="text-xs font-bold text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        Kosongkan kolom nilai bila siswa belum dinilai
                    </span>

                    <button type="submit" <?= (!$siswaList || !$skAktif) ? 'disabled' : '' ?> class="px-6 py-3 clay-block-ocean text-white rounded-2xl text-xs sm:text-sm font-extrabold clay-btn flex items-center gap-2 shadow-md cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Semua Nilai
                    </button>
                </div>
            </form>
        </div>

    <?php endif; ?>

</main>

<!-- 3. Right Panel -->
<?php include '../../components/right-panel.php'; ?>

<!-- 4. Footer -->
<?php include '../../components/footer.php'; ?>
