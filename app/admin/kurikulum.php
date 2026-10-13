<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin-repository.php';

$admin = wajib_login('admin');
$self  = base_url('app/admin/kurikulum.php');

// ---------- POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_token'] ?? null)) {
        set_flash('error', 'Token CSRF tidak valid.');
        redirect($self);
    }

    $aksi = $_POST['aksi'] ?? '';
    $buka = strtoupper(trim($_POST['buka'] ?? ''));   // mapel yang tetap terbuka

    try {
        if ($aksi === 'tambah_mapel') {
            admin_tambah_mapel($_POST);
            set_flash('success', 'Mata pelajaran berhasil ditambahkan.');
            $buka = strtoupper(trim($_POST['Kode_mata_pelajaran'] ?? ''));
        } elseif ($aksi === 'edit_mapel') {
            admin_update_mapel($_POST);
            set_flash('success', 'Mata pelajaran berhasil diperbarui.');
        } elseif ($aksi === 'hapus_mapel') {
            $kode = strtoupper(trim($_POST['kode_mapel'] ?? ''));
            $st = db()->prepare('SELECT COUNT(*) FROM pengajaran WHERE Kode_mata_pelajaran = :k');
            $st->execute([':k' => $kode]);
            $nP = (int) $st->fetchColumn();
            $st = db()->prepare('SELECT COUNT(*) FROM standar_kompetensi WHERE Kode_mata_pelajaran = :k');
            $st->execute([':k' => $kode]);
            $nS = (int) $st->fetchColumn();
            if ($nP > 0 || $nS > 0) {
                set_flash('error', "Mapel tidak dapat dihapus karena masih terkait $nP penugasan guru dan $nS standar kompetensi.");
            } elseif ($kode !== '') {
                admin_hapus_mapel($kode);
                set_flash('success', 'Mata pelajaran berhasil dihapus.');
            }
            $buka = '';
        } elseif ($aksi === 'tambah_sk') {
            admin_tambah_sk($_POST);
            set_flash('success', 'Standar Kompetensi berhasil ditambahkan.');
        } elseif ($aksi === 'edit_sk') {
            admin_update_sk($_POST);
            set_flash('success', 'Standar Kompetensi berhasil diperbarui.');
        } elseif ($aksi === 'hapus_sk') {
            $kodeSk = strtoupper(trim($_POST['kode_sk'] ?? ''));
            $kelasSk = strtoupper(trim($_POST['kelas_sk'] ?? ''));
            $st = db()->prepare('SELECT COUNT(*) FROM nilai WHERE Kode_SK = :sk AND Kelas_SK = :kelas');
            $st->execute([':sk' => $kodeSk, ':kelas' => $kelasSk]);
            $nN = (int) $st->fetchColumn();
            if ($nN > 0) {
                set_flash('error', "SK tidak dapat dihapus karena sudah memiliki $nN data nilai siswa.");
            } elseif ($kodeSk !== '' && $kelasSk !== '') {
                admin_hapus_sk($kodeSk, $kelasSk);
                set_flash('success', 'Standar Kompetensi berhasil dihapus.');
            }
        }
    } catch (Throwable $e) {
        $pesan = [
            'tambah_mapel' => 'Gagal menambah mapel. Kode mungkin sudah terdaftar.',
            'edit_mapel'   => 'Gagal memperbarui mapel.',
        ];
        set_flash('error', $pesan[$aksi] ?? 'Terjadi kesalahan: ' . $e->getMessage());
    }

    redirect($self . ($buka !== '' ? '?buka=' . urlencode($buka) : ''));
}

// ---------- DATA ----------
$daftarMapel = admin_semua_mapel();
$daftarKK    = admin_semua_kk();

$skPerMapel = [];
foreach (admin_semua_sk('', '') as $sk) {
    $skPerMapel[$sk['Kode_mata_pelajaran']][] = $sk;
}

$buka  = strtoupper(trim($_GET['buka'] ?? ''));
$flash = get_flash();

$pageTitle = "Kurikulum - Panel Admin";
include __DIR__ . '/../../components/header.php';
include __DIR__ . '/../../components/sidebar-admin.php';

$inp = 'w-full px-4 py-2.5 clay-inset rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none';
$lbl = 'block text-[11px] font-extrabold uppercase text-slate-400 mb-1 pl-1';
$trash = 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16';
$pen   = 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
?>

<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <?php include __DIR__ . '/../../components/flash.php'; ?>

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">Kurikulum</h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                Mata pelajaran dan Standar Kompetensi (SK) &bull; klik mapel untuk melihat SK-nya
            </p>
        </div>
        <button type="button" onclick="toggleModal('modal-mapel-tambah', true)"
            class="px-4 py-2.5 clay-block-ocean text-white rounded-2xl text-xs sm:text-sm font-extrabold flex items-center gap-2 shadow-sm hover:opacity-90 transition-all cursor-pointer">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Mapel</span>
        </button>
    </div>

    <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">Mata Pelajaran</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-amber-600 bg-amber-500/10 dark:bg-amber-400/10 dark:text-amber-400">
                    <?= count($daftarMapel) ?> Mapel
                </span>
            </div>
            <input type="text" id="cari" placeholder="Cari mapel, kode, atau SK..."
                   class="w-full sm:w-64 px-3.5 py-2 rounded-xl clay-inset text-xs font-bold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none bg-white/50 dark:bg-slate-800/50">
        </div>

        <?php if (!$daftarMapel): ?>
            <p class="py-10 text-center text-xs font-bold text-slate-400">Belum ada mata pelajaran.</p>
        <?php endif; ?>

        <div class="divide-y divide-slate-200/60 dark:divide-slate-700/60">
        <?php foreach ($daftarMapel as $m):
            $kode = $m['Kode_mata_pelajaran'];
            $sks  = $skPerMapel[$kode] ?? [];
            $open = ($buka === strtoupper($kode));
            $cari = strtolower($m['Nama_mata_pelajaran'] . ' ' . $kode . ' ' . implode(' ', array_map(fn($s) => $s['Kode_SK'] . ' ' . $s['Nama_SK'], $sks)));
        ?>
            <div class="item-mapel" data-cari="<?= e($cari) ?>">
                <div class="flex items-center gap-3 py-3.5">
                    <button type="button" onclick="toggleDetail('<?= e($kode) ?>')" class="flex-1 min-w-0 flex items-center gap-3 text-left cursor-pointer">
                        <svg id="chev-<?= e($kode) ?>" class="w-4 h-4 text-slate-400 shrink-0 transition-transform <?= $open ? 'rotate-90' : '' ?>" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        <div class="min-w-0">
                            <div class="font-extrabold text-sm text-slate-800 dark:text-slate-100 truncate"><?= e($m['Nama_mata_pelajaran']) ?></div>
                            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">
                                <span class="text-brand-primary dark:text-sky-400 font-bold"><?= e($kode) ?></span>
                                &bull; <?= e($m['Nama_kompetensi_keahlian'] ?? $m['Kode_KK']) ?>
                                &bull; <?= (int) ($m['total_pengajaran'] ?? 0) ?> guru
                            </div>
                        </div>
                    </button>
                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-black shrink-0 <?= $sks ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' ?>">
                        <?= $sks ? count($sks) . ' SK' : 'Belum ada SK' ?>
                    </span>
                    <button type="button" class="p-1.5 rounded-lg text-amber-500 hover:bg-amber-500/10 cursor-pointer shrink-0" title="Edit mapel"
                        data-kode="<?= e($kode) ?>" data-nama="<?= e($m['Nama_mata_pelajaran']) ?>" data-kk="<?= e($m['Kode_KK']) ?>"
                        onclick="editMapel(this)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $pen ?>"/></svg>
                    </button>
                    <form method="POST" class="shrink-0 js-confirm-hapus" data-nama="<?= e($m['Nama_mata_pelajaran'] . ' (' . $kode . ')') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus_mapel">
                        <input type="hidden" name="kode_mapel" value="<?= e($kode) ?>">
                        <button class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-500/10 cursor-pointer" title="Hapus mapel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $trash ?>"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Daftar SK -->
                <div id="detail-<?= e($kode) ?>" class="<?= $open ? '' : 'hidden' ?> pb-4 pl-7">
                    <div class="rounded-2xl bg-slate-100/60 dark:bg-slate-800/40 p-4 space-y-2">
                        <?php if (!$sks): ?>
                            <p class="text-xs font-bold text-slate-400">Belum ada Standar Kompetensi untuk mapel ini.</p>
                        <?php endif; ?>
                        <?php foreach ($sks as $sk): ?>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="font-extrabold text-brand-primary dark:text-sky-400 w-20 shrink-0"><?= e($sk['Kode_SK']) ?></span>
                                <span class="flex-1 min-w-0 font-bold text-slate-700 dark:text-slate-200"><?= e($sk['Nama_SK']) ?></span>
                                <span class="px-2 py-0.5 rounded-md bg-slate-200/70 dark:bg-slate-800 font-bold shrink-0">Kelas <?= e($sk['Kelas_SK']) ?></span>
                                <span class="font-extrabold text-rose-500 dark:text-rose-400 shrink-0" title="KKM">KKM <?= (float) ($sk['Nilai_kkm'] ?? 75) ?></span>
                                <span class="text-slate-400 font-semibold shrink-0 hidden sm:inline"><?= (int) ($sk['total_nilai'] ?? 0) ?> nilai</span>
                                <button type="button" class="p-1 rounded-md text-amber-500 hover:bg-amber-500/10 cursor-pointer" title="Edit SK"
                                    data-kode="<?= e($sk['Kode_SK']) ?>" data-nama="<?= e($sk['Nama_SK']) ?>" data-mapel="<?= e($kode) ?>"
                                    data-kelas="<?= e($sk['Kelas_SK']) ?>" data-kkm="<?= (float) ($sk['Nilai_kkm'] ?? 75) ?>"
                                    onclick="editSk(this)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $pen ?>"/></svg>
                                </button>
                                <form method="POST" class="js-confirm-hapus" data-nama="<?= e($sk['Kode_SK'] . ' — ' . $sk['Nama_SK']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus_sk">
                                    <input type="hidden" name="kode_sk" value="<?= e($sk['Kode_SK']) ?>">
                                    <input type="hidden" name="kelas_sk" value="<?= e($sk['Kelas_SK']) ?>">
                                    <input type="hidden" name="buka" value="<?= e($kode) ?>">
                                    <button class="p-1 rounded-md text-rose-500 hover:bg-rose-500/10 cursor-pointer" title="Hapus SK">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $trash ?>"/></svg>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        <button type="button" data-mapel="<?= e($kode) ?>" onclick="tambahSk(this)"
                            class="mt-2 px-3.5 py-2 clay-card rounded-xl text-xs font-extrabold text-amber-600 dark:text-amber-400 hover:bg-amber-500/10 cursor-pointer">
                            + Tambah SK
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
            <p id="kosong" class="hidden py-10 text-center text-xs font-bold text-slate-400">Tidak ada yang cocok.</p>
        </div>
    </div>
</main>

<?php
// Pilihan jurusan dipakai di dua modal mapel
$optKK = '';
foreach ($daftarKK as $kk) {
    $optKK .= '<option value="' . e($kk['Kode_KK']) . '">' . e($kk['Nama_kompetensi_keahlian']) . '</option>';
}
function modal_open(string $id, string $judul): void { ?>
<div id="<?= $id ?>" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-lg shadow-2xl border border-slate-200/80 dark:border-white/10">
        <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100"><?= $judul ?></h3>
            <button type="button" onclick="toggleModal('<?= $id ?>', false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
<?php }
function modal_close(string $id, string $simpan): void { ?>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                <button type="button" onclick="toggleModal('<?= $id ?>', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
                <button class="px-5 py-2 clay-block-ocean text-white rounded-xl text-xs font-extrabold cursor-pointer"><?= $simpan ?></button>
            </div>
        </form>
    </div>
</div>
<?php }
?>

<!-- Tambah mapel -->
<?php modal_open('modal-mapel-tambah', 'Tambah Mata Pelajaran'); ?>
        <form method="POST" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah_mapel">
            <div><label class="<?= $lbl ?>">Kode Mapel</label><input type="text" name="Kode_mata_pelajaran" required placeholder="PEMWEB" class="<?= $inp ?> uppercase"></div>
            <div><label class="<?= $lbl ?>">Nama Mata Pelajaran</label><input type="text" name="Nama_mata_pelajaran" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Jurusan (KK)</label><select name="Kode_KK" required class="<?= $inp ?> cursor-pointer"><?= $optKK ?></select></div>
<?php modal_close('modal-mapel-tambah', 'Simpan Mapel'); ?>

<!-- Edit mapel -->
<?php modal_open('modal-mapel-edit', 'Edit Mata Pelajaran'); ?>
        <form method="POST" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="edit_mapel">
            <input type="hidden" name="buka" id="em-buka">
            <div><label class="<?= $lbl ?>">Kode (tetap)</label><input type="text" id="em-kode" name="Kode_mata_pelajaran" readonly class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-800/80 text-slate-500 rounded-xl text-xs font-mono font-bold cursor-not-allowed"></div>
            <div><label class="<?= $lbl ?>">Nama Mata Pelajaran</label><input type="text" id="em-nama" name="Nama_mata_pelajaran" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Jurusan (KK)</label><select id="em-kk" name="Kode_KK" required class="<?= $inp ?> cursor-pointer"><?= $optKK ?></select></div>
<?php modal_close('modal-mapel-edit', 'Perbarui'); ?>

<!-- Tambah SK -->
<?php modal_open('modal-sk-tambah', 'Tambah Standar Kompetensi'); ?>
        <form method="POST" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah_sk">
            <input type="hidden" name="Kode_mata_pelajaran" id="ts-mapel">
            <input type="hidden" name="buka" id="ts-buka">
            <div><label class="<?= $lbl ?>">Kode SK</label><input type="text" name="Kode_SK" required placeholder="SK-PW5" class="<?= $inp ?> uppercase"></div>
            <div><label class="<?= $lbl ?>">Nama Standar Kompetensi</label><textarea name="Nama_SK" rows="2" required class="<?= $inp ?>"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">Tingkat Kelas</label>
                    <select name="Kelas_SK" required class="<?= $inp ?> cursor-pointer"><option value="X">Kelas X</option><option value="XI">Kelas XI</option><option value="XII" selected>Kelas XII</option></select></div>
                <div><label class="<?= $lbl ?>">Nilai KKM</label><input type="number" step="0.1" name="Nilai_kkm" value="75" min="0" max="100" required class="<?= $inp ?>"></div>
            </div>
<?php modal_close('modal-sk-tambah', 'Simpan SK'); ?>

<!-- Edit SK -->
<?php modal_open('modal-sk-edit', 'Edit Standar Kompetensi'); ?>
        <form method="POST" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="edit_sk">
            <input type="hidden" name="Kode_SK_lama" id="es-kode-lama">
            <input type="hidden" name="Kelas_SK_lama" id="es-kelas-lama">
            <input type="hidden" name="Kode_mata_pelajaran" id="es-mapel">
            <input type="hidden" name="buka" id="es-buka">
            <div><label class="<?= $lbl ?>">Kode SK (tetap)</label><input type="text" id="es-kode" name="Kode_SK" readonly class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-800/80 text-slate-500 rounded-xl text-xs font-mono font-bold cursor-not-allowed"></div>
            <div><label class="<?= $lbl ?>">Nama Standar Kompetensi</label><textarea id="es-nama" name="Nama_SK" rows="2" required class="<?= $inp ?>"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">Tingkat Kelas</label>
                    <select id="es-kelas" name="Kelas_SK" required class="<?= $inp ?> cursor-pointer"><option value="X">Kelas X</option><option value="XI">Kelas XI</option><option value="XII">Kelas XII</option></select></div>
                <div><label class="<?= $lbl ?>">Nilai KKM</label><input type="number" step="0.1" id="es-kkm" name="Nilai_kkm" min="0" max="100" required class="<?= $inp ?>"></div>
            </div>
<?php modal_close('modal-sk-edit', 'Perbarui SK'); ?>

<script>
const $ = id => document.getElementById(id);
function toggleModal(id, show) { $(id).classList.toggle('hidden', !show); }
function toggleDetail(k) {
    $('detail-' + k).classList.toggle('hidden');
    $('chev-' + k).classList.toggle('rotate-90');
}
function editMapel(b) {
    $('em-kode').value = b.dataset.kode; $('em-buka').value = b.dataset.kode;
    $('em-nama').value = b.dataset.nama; $('em-kk').value = b.dataset.kk;
    toggleModal('modal-mapel-edit', true);
}
function tambahSk(b) {
    $('ts-mapel').value = b.dataset.mapel; $('ts-buka').value = b.dataset.mapel;
    toggleModal('modal-sk-tambah', true);
}
function editSk(b) {
    $('es-kode').value = b.dataset.kode; $('es-nama').value = b.dataset.nama;
    $('es-kode-lama').value = b.dataset.kode; $('es-kelas-lama').value = b.dataset.kelas;
    $('es-mapel').value = b.dataset.mapel; $('es-buka').value = b.dataset.mapel;
    $('es-kelas').value = b.dataset.kelas; $('es-kkm').value = b.dataset.kkm;
    toggleModal('modal-sk-edit', true);
}
$('cari').addEventListener('input', e => {
    const v = e.target.value.toLowerCase().trim();
    let n = 0;
    document.querySelectorAll('.item-mapel').forEach(el => {
        const ok = el.dataset.cari.includes(v);
        el.style.display = ok ? '' : 'none';
        if (ok) n++;
    });
    $('kosong').classList.toggle('hidden', n > 0);
});
</script>

<!-- Modal konfirmasi hapus (reusable) -->
<?php include __DIR__ . '/../../components/confirm-hapus.php'; ?>

<?php include __DIR__ . '/../../components/footer.php'; ?>
