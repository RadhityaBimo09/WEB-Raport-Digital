<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/siswa-repository.php';

// Guard: hanya siswa yang sudah login.
$user = wajib_login('siswa');

// Ambil data profil dari database berdasarkan NISN login
$profil = siswa_profil($user['kode']);
if (!$profil) {
    redirect(base_url('auth/logout.php'));
}

// Ambil data wali dari database berdasarkan NISN login
$wali = siswa_wali($profil['NISN']);

$siswa = [
    'nama'          => $profil['Nama_siswa'],
    'nisn'          => $profil['NISN'],
    'kelas'         => $profil['nama_kelas'],
    'jurusan'       => $profil['Nama_kompetensi_keahlian'],
    'ttl'           => !empty($profil['Tgl_lahir']) ? $profil['Tgl_lahir'] : '-',
    'alamat'        => !empty($profil['Alamat_siswa']) ? $profil['Alamat_siswa'] : '-',
    'avatar'        => '../../assets/img/user-avatar.jpg',

    // Data Orang Tua / Wali
    'nama_ayah'      => $wali['Nama_ayah'] ?? '-',
    'pekerjaan_ayah' => $wali['Pekerjaan_ayah'] ?? '-',
    'nama_ibu'       => $wali['Nama_ibu'] ?? '-',
    'pekerjaan_ibu'  => $wali['Pekerjaan_ibu'] ?? '-',
    'no_telp_wali'   => $wali['Telp_wali'] ?? '-',
];

$pageTitle = "Profil Saya & Data Wali - E-Rapor Digital";
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
                Profil Saya & Data Wali
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                Informasi Biodata Diri & Orang Tua Terdaftar
            </p>
        </div>

        <!-- Jam Realtime Kanan Atas -->
        <div class="px-4 py-2.5 clay-card rounded-2xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hidden sm:flex items-center gap-2.5 shadow-xs">
            <svg class="w-4.5 h-4.5 text-brand-primary dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"></path>
            </svg>
            <span id="realtime-clock" class="font-extrabold tracking-wide">00:00:00 WIB</span>
        </div>
    </div>

    <!-- Modul Highlight Banner Profil -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        
        <!-- Ocean Banner (8 Kolom) -->
        <div class="md:col-span-7 lg:col-span-8 clay-block-ocean p-5 lg:p-6 rounded-[26px] text-white flex items-center justify-between gap-4 shadow-md flex-wrap sm:flex-nowrap">
            <div class="flex items-center gap-4 min-w-0">
                <!-- Foto / Avatar Siswa -->
                <div class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 shadow-sm border-2 border-white/30">
                    <img 
                        src="<?= $siswa['avatar'] ?>" 
                        alt="Foto <?= e($siswa['nama']) ?>" 
                        class="w-full h-full object-cover" 
                        onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama']) ?>&background=2B76B9&color=fff&bold=true'">
                </div>

                <div class="space-y-0.5 min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sky-200/90 block">Selamat Datang</span>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight truncate">
                        <?= e($siswa['nama']) ?>
                    </h2>
                    <p class="text-xs text-sky-100/80 font-medium">
                        Siswa Aktif Kelas <?= e($siswa['kelas']) ?> — <?= e($siswa['jurusan']) ?>
                    </p>
                </div>
            </div>

            <!-- Tombol Shortcut Ke Rapor -->
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

        <!-- Card Kecil Detail Identitas Nomor (4 Kolom) -->
        <div class="md:col-span-5 lg:col-span-4 clay-card p-5 rounded-[26px] flex flex-col justify-between shadow-xs border border-slate-200/80 dark:border-white/10">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Nomor Induk</span>
            </div>
            
            <div class="space-y-2">
                <!-- Row NISN -->
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">NISN</span>
                        <strong class="text-xs font-black text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['nisn']) ?></strong>
                    </div>
                </div>

                <!-- Row Kelas & Jurusan -->
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Kelas Jurusan</span>
                        <strong class="text-xs font-black text-slate-800 dark:text-slate-100 block mt-0.5 truncate"><?= e($siswa['kelas']) ?></strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Grid Detail Profil Siswa & Orang Tua -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
        
        <!-- Section 1: Biodata Siswa (7 Kolom) -->
        <div class="md:col-span-7 clay-card rounded-[28px] p-5 lg:p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
                    <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base">
                        Detail Biodata Siswa
                    </h3>
                </div>

                <div class="space-y-3.5">
                    <!-- Row Nama Lengkap -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Nama Lengkap</span>
                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['nama']) ?></strong>
                        </div>
                    </div>

                    <!-- Row Tanggal Lahir -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Tanggal Lahir</span>
                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['ttl']) ?></strong>
                        </div>
                    </div>

                    <!-- Row Kompetensi Keahlian -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Kompetensi Keahlian</span>
                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['jurusan']) ?></strong>
                        </div>
                    </div>

                    <!-- Row Alamat -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Alamat Tinggal</span>
                            <strong class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5 leading-snug"><?= e($siswa['alamat']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Data Orang Tua / Wali (5 Kolom) -->
        <div class="md:col-span-5 clay-card rounded-[28px] p-5 lg:p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
                    <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base">
                        Data Orang Tua / Wali
                    </h3>
                </div>

                <div class="space-y-3.5">
                    <!-- Row Nama Ayah -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Nama Ayah</span>
                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['nama_ayah']) ?></strong>
                            <span class="text-[10px] font-bold text-slate-400"><?= e($siswa['pekerjaan_ayah']) ?></span>
                        </div>
                    </div>

                    <!-- Row Nama Ibu -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">Nama Ibu</span>
                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['nama_ibu']) ?></strong>
                            <span class="text-[10px] font-bold text-slate-400"><?= e($siswa['pekerjaan_ibu']) ?></span>
                        </div>
                    </div>

                    <!-- Row Telepon Wali -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-primary/10 dark:bg-sky-400/10 text-brand-primary dark:text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block leading-none">No. Telepon / WhatsApp Wali</span>
                            <strong class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 block mt-0.5"><?= e($siswa['no_telp_wali']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer Catatan Informasi -->
    <div class="clay-card rounded-2xl p-4 text-xs font-bold text-slate-400 flex items-center justify-between flex-wrap gap-2 shadow-2xs border border-slate-200/60 dark:border-white/5">
        <span>Apabila terdapat kesalahan data biodata di atas, silakan hubungi Wali Kelas atau Admin Kurikulum Sekolah.</span>
        <span class="text-[11px] text-brand-primary dark:text-sky-400 font-extrabold">Data Terkoneksi</span>
    </div>

</main>


<!-- 3. Footer -->
<?php include '../../components/footer.php'; ?>