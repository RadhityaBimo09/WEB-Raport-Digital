<?php
require_once __DIR__ . '/../config/auth.php';

// Deteksi portal: utamakan role session, fallback ke struktur folder.
$isSiswa = (role_user() === 'siswa')
    || (role_user() === null && strpos($_SERVER['REQUEST_URI'], '/siswa/') !== false);

$panelUser = user();
$namaUser  = $panelUser['nama'] ?? ($isSiswa ? 'Siswa' : 'Guru');
$subUser   = $isSiswa
    ? 'NISN: ' . ($panelUser['nisn'] ?? '-')
    : 'NIP: ' . ($panelUser['nip'] ?? '-');

// Progres input nilai (khusus portal guru) diambil langsung dari database.
$progresKelas = [];
$progresTotal = 0;

if (!$isSiswa && $panelUser) {
    require_once __DIR__ . '/../config/guru-repository.php';
    $progresKelas = guru_progres_input($panelUser['kode']);
    $progresTotal = guru_progres_total($progresKelas);
}
?>

<!-- Right Panel / Sidebar Kanan -->
<aside class="w-full xl:w-80 flex flex-col gap-5 shrink-0">

  <!-- Widget 1: Profile & Tahun Ajaran Summary -->
  <div class="clay-card rounded-[28px] p-5 shadow-sm">
    <div class="flex items-center gap-3.5">
      <!-- Avatar Murni Tanpa Border -->
      <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 shadow-sm">
        <img 
          src="../../assets/img/user-avatar.jpg" 
          alt="Avatar <?= htmlspecialchars($namaUser) ?>" 
          class="w-full h-full object-cover" 
          onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($namaUser) ?>&background=2B76B9&color=fff&bold=true'">
      </div>
      <div class="min-w-0 flex-1">
        <span class="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">Tahun Ajaran</span>
        <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 truncate"><?= e(TAHUN_AJARAN_AKTIF . ' — ' . SEMESTER_AKTIF) ?></h3>
        <span class="inline-block mt-1 px-2.5 py-0.5 clay-block-mint font-extrabold text-[10px] rounded-full">
          Semester Aktif
        </span>
      </div>
    </div>
  </div>

  <!-- Widget 2: Progres Input Nilai (Guru) / Agenda & Deadline (Siswa) -->
  <div class="clay-card rounded-[28px] p-5 shadow-sm flex-1 flex flex-col justify-between gap-6">
    <div class="space-y-5">

      <?php if ($isSiswa): ?>
      <!-- Section A: Header & Agenda List (khusus siswa) -->
      <div>
        <div class="mb-3.5">
          <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Agenda & Deadline</h2>
        </div>

        <div class="space-y-2.5" id="agenda-list">
          <!-- Item 1: Deadline Input SK -->
          <div class="p-3 rounded-2xl clay-inset flex items-start gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-rose-500 mt-1 shrink-0"></div>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-snug">Deadline Input Nilai SK 1-4</p>
              <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">15 Sep 2026 • 23:59 WIB</span>
            </div>
          </div>

          <!-- Item 2: Pelaksanaan UTS -->
          <div class="p-3 rounded-2xl clay-inset flex items-start gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-brand-primary dark:bg-sky-400 mt-1 shrink-0"></div>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-snug">Pelaksanaan UTS Ganjil</p>
              <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">22 – 26 Sep 2026</span>
            </div>
          </div>

          <!-- Item 3: Pembagian Rapor -->
          <div class="p-3 rounded-2xl clay-inset flex items-start gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1 shrink-0"></div>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-snug">Pembagian Rapor Digital</p>
              <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">05 Okt 2026</span>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Section B: Progres Penilaian Guru (khusus guru, tanpa Agenda) -->
      <?php if (!$isSiswa): ?>
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-xs">Progres Input Nilai</h3>
          <span class="text-[10px] font-black text-brand-primary dark:text-sky-400 uppercase tracking-wider"><?= (int) $progresTotal ?>% Total</span>
        </div>

        <div class="space-y-2.5">
          <?php if (!$progresKelas): ?>
            <p class="text-[11px] font-semibold text-slate-400 py-2">Belum ada kelas ajar aktif.</p>
          <?php endif; ?>

          <?php foreach ($progresKelas as $p): ?>
            <?php
              // Hijau bila sudah tuntas 100%, kuning bila masih berjalan.
              $selesai = $p['persen'] >= 100;
              $blok    = $selesai ? 'clay-block-mint' : 'clay-block-amber';
              $teks    = $selesai ? 'text-emerald-950 dark:text-emerald-200' : 'text-amber-950 dark:text-amber-200';
              $chip    = $selesai
                  ? 'bg-white/60 dark:bg-emerald-950/60 dark:text-emerald-300'
                  : 'bg-white/60 dark:bg-amber-950/60 dark:text-amber-300';
              $track   = $selesai ? 'bg-emerald-950/10 dark:bg-black/40' : 'bg-amber-950/10 dark:bg-black/40';
              $bar     = $selesai ? 'bg-emerald-700 dark:bg-emerald-400' : 'bg-amber-700 dark:bg-amber-400';
            ?>
            <a href="<?= e(base_url('app/guru/input-nilai.php?kelas=' . $p['id_pengajaran'])) ?>"
               class="<?= $blok ?> p-3 rounded-2xl shadow-xs block hover:-translate-y-0.5 transition-transform">
              <div class="flex justify-between items-center font-extrabold <?= $teks ?> text-[11px] mb-1.5">
                <span class="truncate pr-2"><?= e($p['label']) ?></span>
                <span class="<?= $chip ?> px-2 py-0.5 rounded-md text-[10px] font-black"><?= (int) $p['persen'] ?>%</span>
              </div>
              <div class="w-full h-1.5 <?= $track ?> rounded-full overflow-hidden">
                <div class="h-full <?= $bar ?> rounded-full" style="width: <?= (int) $p['persen'] ?>%"></div>
              </div>
              <span class="block mt-1.5 text-[10px] font-bold <?= $teks ?> opacity-70">
                <?= (int) $p['terisi'] ?> / <?= (int) $p['target'] ?> sel nilai terisi
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <?php if ($isSiswa): ?>
    <!-- Footer Catatan Informasi Admin / Kurikulum (khusus siswa, terkait Agenda) -->
    <div class="pt-3 border-t border-slate-200/60 dark:border-slate-700/60 text-center">
      <p class="text-[11px] font-semibold text-slate-400">
        Jadwal dikelola terpusat oleh Kurikulum / Admin.
      </p>
    </div>
    <?php endif; ?>
  </div>

</aside>


































































































































































