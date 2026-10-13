
<?php
require_once __DIR__ . '/../config/auth.php';

// Sudah login? Langsung lempar ke portal masing-masing.
if (sudah_login()) {
    redirect(base_url(
        role_user() === 'guru' ? 'app/guru/dashboard.php' : 'app/siswa/dashboard.php'
    ));
}

$flash       = get_flash();
$oldUsername = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);

$pageTitle = "Login - E-Rapor Digital SMK Bina Informatika";
include '../components/header-auth.php'; 
?>

<!-- Container Utama Halaman Login dengan Background Image + Soft Ambient Overlay -->
<div class="w-full min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-10 relative overflow-hidden bg-cover bg-center bg-no-repeat font-sans"
     style="background-image: url('../assets/img/bg-login.jpg');">

    <!-- Overlay Gelap Transparan + Glass Backdrop Blur -->
    <div class="absolute inset-0 bg-slate-950/60 dark:bg-slate-950/80 backdrop-blur-[6px]"></div>

    <!-- Ambient Glow Aksen Clay -->
    <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-sky-500/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-[30rem] h-[30rem] rounded-full bg-emerald-500/20 blur-3xl pointer-events-none"></div>

    <!-- Container Split Screen -->
    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center relative z-10 my-auto">
        
        <!-- SISI KIRI: Branding & Headline -->
        <div class="lg:col-span-6 space-y-4 text-center lg:text-left px-2">
            <div class="flex items-center justify-center lg:justify-start gap-3.5 mb-2">
                <!-- Logo Resmi SMK Bina Informatika -->
                <img 
                    src="../assets/img/logo-bi.svg" 
                    alt="SMK Bina Informatika" 
                    class="h-12 w-auto drop-shadow-md transition-transform duration-300 hover:scale-105 cursor-default shrink-0"
                    onerror="this.src='https://ui-avatars.com/api/?name=B+I&background=2B76B9&color=fff'">
                
                <div class="text-left">
                    <span class="block font-heading text-base font-black uppercase tracking-wider text-white drop-shadow-sm">
                        BINA INFORMATIKA
                    </span>
                    <span class="block text-[11px] text-sky-200/90 font-bold tracking-wide drop-shadow-xs">
                        THE PIONEER OF IT SCHOOL
                    </span>
                </div>
            </div>

            <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black uppercase tracking-tight text-white leading-tight drop-shadow-md">
                E-RAPOR <br class="hidden lg:block"/>
                <span class="text-sky-400 drop-shadow-[0_0_25px_rgba(56,189,248,0.5)]">DIGITAL.</span>
            </h1>
            
            <p class="text-xs sm:text-sm text-slate-200 font-semibold leading-relaxed max-w-md mx-auto lg:mx-0 drop-shadow-xs">
                Sistem pengelolaan transkrip nilai dan hasil belajar siswa secara transparan, akurat, dan terintegrasi.
            </p>
        </div>

        <!-- SISI KANAN: Card Form Claymorphism Soft Glass -->
        <div class="lg:col-span-6 w-full">
            <div class="clay-card p-6 sm:p-8 rounded-[32px] shadow-2xl border border-white/30 dark:border-white/10 relative transition-all bg-white/85 dark:bg-slate-900/85 backdrop-blur-xl">
                
                <div class="mb-6">
                    <h2 class="font-heading text-xl sm:text-2xl font-extrabold text-slate-800 dark:text-slate-100">
                        Masuk Akun
                    </h2>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Masukkan NIP atau NISN terdaftar untuk melanjutkan.</p>
                </div>

                <?php if ($flash): ?>
                    <?php $isError = ($flash['tipe'] ?? '') === 'error'; ?>
                    <div class="mb-4 px-4 py-3 rounded-2xl flex items-start gap-2.5 text-[11px] font-bold <?= $isError ? 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20' ?>">
                        <svg class="w-4 h-4 shrink-0 mt-px" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <?php if ($isError): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <?php endif; ?>
                        </svg>
                        <span class="leading-snug"><?= e($flash['pesan']) ?></span>
                    </div>
                <?php endif; ?>

                <form class="space-y-4" action="process-login.php" method="POST">
                    <?= csrf_field() ?>
                    
                    <!-- Input Username (NIP / NISN) -->
                    <div class="space-y-1.5">
                        <label for="username" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 pl-1">
                            NIP / NISN
                        </label>
                        <div class="relative">
                            <input
                                id="username"
                                name="username"
                                type="text"
                                required
                                autocomplete="username"
                                value="<?= e($oldUsername) ?>"
                                placeholder="Masukkan NIP atau NISN"
                                class="w-full pl-4 pr-10 py-3.5 clay-inset rounded-2xl text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/40 bg-white/60 dark:bg-slate-950/40">
                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Input Password -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center pl-1">
                            <label for="password" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Password
                            </label>
                            <a href="javascript:void(0);" onclick="alert('Silakan hubungi Admin Sekolah untuk melakukan Reset Password.')" class="text-[11px] text-brand-primary dark:text-sky-400 hover:underline font-extrabold">Lupa Password?</a>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                placeholder="Masukkan password akun"
                                class="w-full pl-4 pr-10 py-3.5 clay-inset rounded-2xl text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/40 bg-white/60 dark:bg-slate-950/40">
                            <button
                                type="button"
                                onclick="togglePasswordVisibility()"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer focus:outline-none"
                                aria-label="Lihat Password">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit Ocean Clay dengan Shimmering Effect -->
                    <button type="submit" class="relative group w-full py-3.5 px-6 rounded-2xl font-black text-white clay-block-ocean clay-btn text-xs sm:text-sm tracking-wider uppercase shadow-md overflow-hidden cursor-pointer mt-2">
                        <!-- Kilatan Cahaya Shimmering -->
                        <span class="absolute top-0 left-[-100%] w-full h-full bg-gradient-to-r from-transparent via-white/25 to-transparent group-hover:left-[100%] transition-all duration-700 ease-in-out pointer-events-none"></span>
                        
                        <span class="relative flex items-center justify-center gap-2">
                            <span>MASUK SEKARANG</span>
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                    </button>
                </form>

                                <!-- Catatan Akses & Tombol Portal Admin -->
                <div class="mt-6 pt-4 border-t border-slate-300/50 dark:border-slate-700/60 text-center space-y-3">
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 leading-relaxed">
                        Akun dikelola oleh Admin Sekolah. Guru masuk menggunakan <strong>NIP</strong>,
                        siswa menggunakan <strong>NISN</strong>.
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include '../components/footer.php'; ?>