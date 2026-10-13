<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/guru-repository.php';

// Guard: hanya guru yang sudah login.
$user = wajib_login('guru');

// Profil guru diambil dari session hasil autentikasi.
$guru = [
    'nama'   => $user['nama'],
    'nip'    => $user['nip'] ?: '-',
    'email'  => $user['email'] ?: '-',
    'avatar' => '../../assets/img/user-avatar.jpg',
];

// Ringkasan tugas mengajar (real data dari tabel pengajaran).
$tugasMengajar = guru_kelas_ajar($user['kode']);
$flash         = get_flash();

$pageTitle = "Dashboard Guru - E-Rapor Digital";
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
                Dashboard
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                <strong class="text-slate-800 dark:text-slate-100 font-extrabold"><?= e($guru['nama']) ?></strong>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <span><?= e(tanggal_indonesia()) ?></span>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <span class="text-brand-primary dark:text-sky-400 font-extrabold"><?= count($tugasMengajar) ?> Kelas Ajar</span>
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
    <div class="w-full">
        
        <!-- Ocean Banner -->
        <div class="col-span-12 w-full clay-block-ocean p-5 lg:p-6 rounded-[26px] text-white flex items-center justify-between gap-4 shadow-md flex-wrap sm:flex-nowrap">
            <div class="flex items-center gap-4 min-w-0">
                <!-- Foto Profil Guru -->
                <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 shadow-sm border-2 border-white/30">
                    <img 
                        src="<?= $guru['avatar'] ?>" 
                        alt="Foto <?= e($guru['nama']) ?>" 
                        class="w-full h-full object-cover" 
                        onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($guru['nama']) ?>&background=2B76B9&color=fff&bold=true'">
                </div>

                <div class="space-y-0.5 min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sky-200/90 block">Selamat Bertugas</span>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight truncate">
                        <?= e($guru['nama']) ?>
                    </h2>
                    <p class="text-xs text-sky-100/80 font-medium">
                        Siap melakukan pengelolaan & penginputan nilai siswa.
                    </p>
                </div>
            </div>

            <!-- Tombol Shortcut Rekapitulasi -->
            <a href="rekap-nilai.php" class="px-4 py-3 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-2xl text-xs font-extrabold text-white flex items-center gap-2 transition-all hover:scale-105 active:scale-95 shadow-xs shrink-0 cursor-pointer border border-white/20">
                <svg class="w-4 h-4 text-sky-100" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Rekap Kelas</span>
                <svg class="w-4 h-4 text-sky-100" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                </svg>
            </a>
        </div>

        

                

    </div>

    <!-- Modul Ringkasan Tugas Mengajar -->
    <div class="clay-card rounded-[28px] p-5 lg:p-6 flex-1 flex flex-col justify-between shadow-sm">
        <div>
            <!-- Header Tabel -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">
                        Ringkasan Tugas Mengajar
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-brand-primary bg-brand-primary/10 dark:bg-sky-400/10 dark:text-sky-400">
                        Total: <?= count($tugasMengajar) ?> Kelas Ajar
                    </span>
                </div>
            </div>

            <!-- Tabel Daftar Kelas Ajar (Murni XII RPL) -->
        <div class="overflow-x-auto pr-1">
    <table class="w-full text-left border-collapse min-w-[650px]">
        
        <!-- ==================== HEADER TABEL ==================== -->
        <thead>
            <!-- Border tipis di bawah header untuk pemisah -->
            <tr class="text-xs font-bold text-slate-400 border-b border-slate-200 dark:border-slate-700/80">
                <!-- w-12: Lebar tetap untuk kolom nomor -->
                <th class="pb-3 pl-2 w-12">NO</th>
                
                <!-- w-1/3: Lebar 33% agar nama kelas yang panjang tidak menumpuk/terpotong -->
                <th class="pb-3 w-1/3">KELAS</th>
                
                <!-- w-1/3: Lebar 33% seimbang dengan kolom kelas -->
                <th class="pb-3 w-1/3">MATA PELAJARAN</th>
                
                <!-- w-36: Lebar tetap 144px untuk kolom Daftar SK -->
                <th class="pb-3 text-center w-36">DAFTAR SK</th>
                
                <!-- w-40: Lebar cukup untuk menampung tombol Input Nilai -->
                <th class="pb-3 text-center w-40">AKSI</th>
            </tr>
        </thead>

        <!-- ==================== BODY TABEL ==================== -->
        <!-- divide-y: Memberi garis pemisah horizontal tipis antar baris (row) -->
        <tbody class="text-sm divide-y divide-slate-200/60 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
            
            <?php if (!$tugasMengajar): ?>
                <!-- State Kosong: Ditampilkan jika belum ada kelas ajar -->
                <tr>
                    <td colspan="5" class="py-10 text-center text-xs font-bold text-slate-400">
                        Belum ada kelas ajar yang ditugaskan pada periode
                        <?= e(TAHUN_AJARAN_AKTIF . ' — ' . SEMESTER_AKTIF) ?>.
                        <br>Hubungi bagian Kurikulum / Admin untuk penugasan mengajar.
                    </td>
                </tr>
            <?php else: ?>
                
                <!-- Looping Baris Data Kelas -->
                <?php $no = 1; foreach ($tugasMengajar as $m): ?>
                    <!-- hover:bg-slate-800/20: Efek sorot warna lembut saat kursor di atas baris -->
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        
                        <!-- Nomor Urut -->
                        <td class="py-3.5 pl-2 text-slate-400"><?= $no++ ?></td>
                        
                        <!-- Nama Kelas + Jurusan (leading-snug agar spasi baris rapi jika teks tertekuk) -->
                        <td class="py-3.5 pr-4 font-bold text-slate-900 dark:text-white leading-snug">
                            <?= e($m['nama_kelas'] . ' ' . $m['Nama_kompetensi_keahlian']) ?>
                        </td>
                        
                        <!-- Nama Mata Pelajaran -->
                        <td class="py-3.5 pr-4 font-semibold text-slate-700 dark:text-slate-200 leading-snug">
                            <?= e($m['Nama_mata_pelajaran']) ?>
                        </td>
                        
                        <!-- Total Standar Kompetensi (SK) -->
                        <td class="py-3.5 text-center">
                            <span class="text-xs font-bold text-sky-400">
                                <?= (int) $m['total_sk'] ?> SK
                            </span>
                        </td>
                        
                        <!-- Kolom Tombol Aksi "Input Nilai" -->
                        <td class="py-3.5 text-center">
                            <!-- 
                                DESKRIPSI CLASS TOMBOL DEFAULT:
                                - inline-flex items-center gap-1.5 : Mengatur posisi ikon dan teks sejajar secara mendatar
                                - px-3 py-1.5                     : Padding dalam tombol (Horizontal: 12px, Vertikal: 6px)
                                - rounded-lg                      : Sudut tombol melengkung halus
                                - text-xs font-bold               : Ukuran teks kecil dan tebal
                                - clay-block-ocean / bg-sky-600   : Warna dasar tombol (Gunakan clay-block sesuai tema proyekmu)
                                - text-white                      : Warna teks putih
                                - hover:opacity-90 transition     : Efek redup halus saat kursor mengarah ke tombol
                            -->
                            <a href="input-nilai.php?kelas=<?= (int) $m['id_pengajaran'] ?>" 
                               class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 clay-block-ocean text-white text-xs font-bold rounded-lg shadow-xs hover:opacity-90 transition-all cursor-pointer">
                                
                                <!-- Ikon Pensil Edit / Input -->
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                
                                <span>Input Nilai</span>
                            </a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>
</div>

        <!-- Footer Informatif -->
        <div class="mt-5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs font-bold text-slate-400 flex-wrap gap-2">
            <span>Menampilkan <?= count($tugasMengajar) ?> kelas diampu</span>
            <span class="text-[11px] font-semibold text-slate-400">Pilih "Input Nilai" untuk mulai memasukkan nilai SK</span>
        </div>
    </div>

</main>

<!-- 3. Right Panel -->
<?php include '../../components/right-panel.php'; ?>

<!-- 4. Footer -->
<?php include '../../components/footer.php'; ?>