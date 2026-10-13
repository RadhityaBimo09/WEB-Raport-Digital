<?php
/**
 * =====================================================================
 * REKAPITULASI NILAI (PORTAL GURU)
 * ---------------------------------------------------------------------
 * ?kelas=all        -> gabungan seluruh mapel yang diampu (kolom = mapel)
 * ?kelas=<id>       -> detail satu mapel (kolom = Standar Kompetensi)
 * ?tingkat=all|X|XI|XII -> saring kelas fisik saat mode gabungan
 * =====================================================================
 */

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/guru-repository.php';

$user = wajib_login('guru');

$daftarKelas = guru_kelas_ajar($user['kode']);

// Filter kelas fisik (Tingkat): all | X | XI | XII.
// Berlaku saat mode gabungan (kelas=all). Saat satu mapel dipilih,
// tampilan ikut Tingkat mapel itu (filter Tingkat diabaikan).
$paramTingkat = isset($_GET['tingkat']) ? strtoupper(trim((string) $_GET['tingkat'])) : 'all';
if (!in_array($paramTingkat, ['X', 'XI', 'XII'], true)) {
    $paramTingkat = 'all';
}

$paramKelas = isset($_GET['kelas']) ? trim((string) $_GET['kelas']) : 'all';
$kelasAktif = null;

if ($paramKelas !== 'all' && ctype_digit($paramKelas)) {
    $kelasAktif = guru_kelas_ajar_satu($user['kode'], (int) $paramKelas);
}

if ($kelasAktif === null) {
    $paramKelas = 'all';
}

// Jika mapel terpilih beda Tingkat dengan filter kelas, kembali ke mode gabungan.
if ($kelasAktif !== null && $paramTingkat !== 'all' && ($kelasAktif['Tingkat'] ?? '') !== $paramTingkat) {
    $kelasAktif = null;
    $paramKelas = 'all';
}

// Saat mode gabungan (kelas=all), batasi daftar mapel ke Tingkat terpilih.
$daftarKelasTampil = $daftarKelas;
if ($kelasAktif === null && $paramTingkat !== 'all') {
    $daftarKelasTampil = array_values(array_filter(
        $daftarKelas,
        static fn ($k) => ($k['Tingkat'] ?? '') === $paramTingkat
    ));
}

$rekap = $kelasAktif
    ? guru_rekap_kelas($kelasAktif)
    : guru_rekap_semua($daftarKelasTampil);

$namaScope = $kelasAktif
    ? $kelasAktif['Nama_mata_pelajaran']
    : '— Semua Mata Pelajaran Diampu —';

if ($kelasAktif) {
    $namaKelas = $kelasAktif['nama_kelas'];
} elseif ($paramTingkat !== 'all') {
    $namaKK = $daftarKelasTampil[0]['Nama_kompetensi_keahlian'] ?? ($daftarKelas[0]['Nama_kompetensi_keahlian'] ?? '');
    $namaKelas = trim($paramTingkat . ' ' . $namaKK);
} else {
    $namaKelas = ($daftarKelas[0]['nama_kelas'] ?? '-');
}

$flash = get_flash();

$pageTitle = "Rekapitulasi Nilai - E-Rapor Digital";
include '../../components/header.php';
?>

<!-- 1. Sidebar -->
<?php include '../../components/sidebar.php'; ?>

<!-- 2. Main Content Area -->
<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <?php include '../../components/flash.php'; ?>

    <!-- Top Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">
                Rekapitulasi Nilai
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                <span>Kelas: <strong class="text-brand-primary dark:text-sky-400"><?= e($namaKelas) ?></strong></span>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <span><?= e($namaScope) ?></span>
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

            <!-- Action Button Export PDF -->
            <button
                type="button"
                onclick="openExportConfirm()"
                class="px-4 py-2.5 clay-block-alpha text-rose-950 rounded-2xl text-xs sm:text-sm font-extrabold clay-btn flex items-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-rose-950" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Export PDF</span>
            </button>
        </div>
    </div>

    <!-- Filter Selector Card -->
    <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm">
        <form method="GET" action="rekap-nilai.php" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-3 space-y-1.5">
                <label for="select-tingkat" class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest block pl-1">
                    Pilih Kelas
                </label>
                <div class="relative">
                    <select id="select-tingkat" name="tingkat" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3.5 clay-inset rounded-2xl text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 appearance-none focus:outline-none focus:ring-2 focus:ring-brand-primary/40 cursor-pointer">
                        <option value="all" <?= $paramTingkat === 'all' ? 'selected' : '' ?>>&mdash; Semua Kelas &mdash;</option>
                        <?php foreach (['X', 'XI', 'XII'] as $t): ?>
                            <option value="<?= $t ?>" <?= $paramTingkat === $t ? 'selected' : '' ?>>
                                Kelas <?= $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
            <div class="md:col-span-7 space-y-1.5">
                <label for="select-mapel" class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest block pl-1">
                    Pilih Scope / Mata Pelajaran
                </label>
                <div class="relative">
                    <select id="select-mapel" name="kelas" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3.5 clay-inset rounded-2xl text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 appearance-none focus:outline-none focus:ring-2 focus:ring-brand-primary/40 cursor-pointer">
                        <option value="all" <?= $paramKelas === 'all' ? 'selected' : '' ?>>&mdash; Semua Mata Pelajaran Diampu &mdash;</option>
                        <?php
                        $opsiMapel = $daftarKelas;
                        if ($paramTingkat !== 'all') {
                            $opsiMapel = array_values(array_filter(
                                $daftarKelas,
                                static fn ($k) => ($k['Tingkat'] ?? '') === $paramTingkat
                            ));
                        }
                        foreach ($opsiMapel as $k): ?>
                            <option value="<?= (int) $k['id_pengajaran'] ?>" <?= $paramKelas === (string) $k['id_pengajaran'] ? 'selected' : '' ?>>
                                <?= e($k['nama_kelas'] . ' — ' . $k['Nama_mata_pelajaran']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end pt-2 md:pt-5">
                <?php if ($kelasAktif): ?>
                    <a href="input-nilai.php?kelas=<?= (int) $kelasAktif['id_pengajaran'] ?>" class="w-full py-3.5 clay-block-ocean text-white rounded-2xl text-xs font-extrabold clay-btn flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Edit Nilai</span>
                    </a>
                <?php else: ?>
                    <a href="input-nilai.php" class="w-full py-3.5 clay-card text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-extrabold flex items-center justify-center gap-2 border border-slate-200/80 dark:border-white/10 hover:text-brand-primary transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <span>Pilih Mapel</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Modul Tabel Rekapitulasi -->
    <div class="clay-card rounded-[28px] p-5 lg:p-6 flex-1 flex flex-col justify-between shadow-sm">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">
                        Transkrip Nilai Kelas
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-brand-primary bg-brand-primary/10 dark:bg-sky-400/10 dark:text-sky-400">
                        <?= count($rekap['baris']) ?> Siswa
                    </span>
                </div>
            </div>

            <!-- Tabel Transkrip -->
            <div class="overflow-x-auto pr-1">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="text-xs font-bold text-slate-400 border-b border-slate-200 dark:border-slate-700/80">
                            <th class="pb-3 pl-2 w-12">NO</th>
                            <th class="pb-3 w-32">NISN</th>
                            <th class="pb-3">NAMA SISWA</th>
                            <?php foreach ($rekap['kolom'] as $kolom): ?>
                                <th class="pb-3 text-center" title="<?= e($kolom['judul']) ?>">
                                    <?= e(strtoupper($kolom['label'])) ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="pb-3 text-center w-28">RATA-RATA</th>
                            <th class="pb-3 text-center w-24">PREDIKAT</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200/60 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
                        <?php if (!$rekap['baris']): ?>
                            <tr>
                                <td colspan="<?= count($rekap['kolom']) + 5 ?>" class="py-10 text-center text-xs font-bold text-slate-400">
                                    Belum ada data nilai untuk ditampilkan.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($rekap['baris'] as $i => $s): ?>
                            <tr class="hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                                <td class="py-3.5 pl-2 font-bold text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-3.5 font-bold text-slate-500 dark:text-slate-400"><?= e($s['nisn']) ?></td>
                                <td class="py-3.5 font-extrabold text-slate-800 dark:text-slate-100">
                                    <?= e($s['nama']) ?>
                                </td>

                                <?php foreach ($rekap['kolom'] as $kolom): ?>
                                    <?php $val = $s['nilai'][$kolom['key']] ?? null; ?>
                                    <td class="py-3.5 text-center font-semibold <?= $val === null ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-300' ?>">
                                        <?= $val === null ? '–' : e((string) (0 + $val)) ?>
                                    </td>
                                <?php endforeach; ?>

                                <td class="py-3.5 text-center font-black text-slate-800 dark:text-slate-100">
                                    <?= $s['rata'] === null ? '–' : e((string) (0 + $s['rata'])) ?>
                                </td>

                                <td class="py-3.5 text-center">
                                    <span class="px-3 py-1 <?= gaya_predikat($s['huruf']) ?> rounded-xl text-xs font-black inline-block shadow-xs">
                                        <?= e($s['huruf']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs font-bold text-slate-400 flex-wrap gap-2">
            <span>
                <?= $kelasAktif
                    ? 'Menampilkan seluruh SK pada mata pelajaran terpilih'
                    : 'Menampilkan rata-rata nilai per mata pelajaran yang diampu' ?>
            </span>
            <span class="text-[11px] font-semibold text-slate-400">Rata-rata dihitung otomatis dari total SK terinput</span>
        </div>
    </div>

</main>

<!-- Modal Konfirmasi Export PDF -->
<div id="modal-export-pdf" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="export-pdf-title">
    <div id="modal-export-pdf-content" class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-md shadow-2xl border border-slate-200/80 dark:border-white/10 transition-all duration-300 opacity-0 scale-95">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 font-black text-[11px] tracking-tighter">
                PDF
            </div>
            <div class="min-w-0">
                <h3 id="export-pdf-title" class="text-base font-extrabold text-slate-800 dark:text-slate-100">Export menjadi PDF?</h3>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Apakah Anda ingin mengekspor <?= e($kelasAktif ? $kelasAktif['Nama_mata_pelajaran'] : 'rekap ini') ?> menjadi PDF?
                </p>
            </div>
        </div>
        <div class="flex justify-end gap-2.5 pt-4 mt-4 border-t border-slate-200/60 dark:border-slate-700/60">
            <button type="button" id="cancel-export-pdf" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
            <a href="export-rekap.php?kelas=<?= e($paramKelas) ?><?= $paramTingkat !== 'all' ? '&tingkat=' . e($paramTingkat) : '' ?>" target="_blank" rel="noopener" data-no-transition id="confirm-export-pdf" class="px-5 py-2.5 clay-block-ocean text-white rounded-xl text-xs font-extrabold clay-btn inline-flex items-center justify-center cursor-pointer">Ya, Export</a>
        </div>
    </div>
</div>

<!-- 3. Right Panel -->
<?php include '../../components/right-panel.php'; ?>

<!-- 4. Footer -->
<?php include '../../components/footer.php'; ?>
