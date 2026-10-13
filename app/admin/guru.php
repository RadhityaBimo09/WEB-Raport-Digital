<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin-repository.php';

$admin = wajib_login('admin');
$self  = base_url('app/admin/guru.php');

// ---------- POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_token'] ?? null)) {
        set_flash('error', 'Token CSRF tidak valid.');
        redirect($self);
    }

    $aksi = $_POST['aksi'] ?? '';
    $buka = trim($_POST['buka'] ?? '');   // guru yang tetap terbuka setelah aksi

    try {
        if ($aksi === 'tambah_guru') {
            admin_tambah_guru($_POST);
            set_flash('success', 'Data guru berhasil ditambahkan.');
        } elseif ($aksi === 'edit_guru') {
            admin_update_guru($_POST);
            set_flash('success', 'Data guru berhasil diperbarui.');
            $buka = trim($_POST['Kode_guru'] ?? $buka);
        } elseif ($aksi === 'hapus_guru') {
            $kode = trim($_POST['kode_guru'] ?? '');
            $st = db()->prepare('SELECT COUNT(*) FROM pengajaran WHERE Kode_guru = :k');
            $st->execute([':k' => $kode]);
            $n = (int) $st->fetchColumn();
            if ($n > 0) {
                set_flash('error', "Guru masih memiliki $n penugasan mengajar. Hapus penugasannya terlebih dahulu.");
            } elseif ($kode !== '') {
                admin_hapus_guru($kode);
                set_flash('success', 'Data guru berhasil dihapus.');
            }
        } elseif ($aksi === 'tambah_tugas') {
            admin_tambah_pengajaran($_POST);
            set_flash('success', 'Penugasan mengajar berhasil ditambahkan.');
        } elseif ($aksi === 'edit_kk_guru') {
            admin_update_guru_kk(trim($_POST['kode_guru'] ?? ''), trim($_POST['Kode_KK'] ?? ''));
            set_flash('success', 'KK guru berhasil diperbarui.');
        } elseif ($aksi === 'hapus_tugas') {
            $id = (int) ($_POST['id_pengajaran'] ?? 0);
            if ($id > 0) {
                admin_hapus_pengajaran($id);
                set_flash('success', 'Penugasan mengajar berhasil dihapus.');
            }
        }
    } catch (InvalidArgumentException $e) {
        set_flash('error', $e->getMessage());
    } catch (Throwable $e) {
        $pesan = [
            'tambah_guru' => 'Gagal menambah guru. NIP/Kode mungkin sudah terdaftar.',
            'edit_guru' => 'Gagal memperbarui guru. NIP/Kode mungkin sudah dipakai.',
            'tambah_tugas' => 'Gagal menambah, pelajaran tersebut mungkin sudah ada.',
        ];
        set_flash('error', $pesan[$aksi] ?? 'Terjadi kesalahan: ' . $e->getMessage());
    }

    redirect($self . ($buka !== '' ? '?buka=' . urlencode($buka) : ''));
}

// ---------- DATA ----------
$daftarGuru = admin_semua_guru();
$daftarKK   = admin_semua_kk();
$daftarMapel = admin_semua_mapel();

// Penugasan dikelompokkan per guru
$tugasPerGuru = [];
$rows = db()->query(
    'SELECT p.id_pengajaran, p.Kode_guru, p.Kode_KK, p.Kode_mata_pelajaran, p.Tahun_ajaran, p.Semester,
            mp.Nama_mata_pelajaran, kk.Nama_kompetensi_keahlian
       FROM pengajaran p
  LEFT JOIN mata_pelajaran mp ON mp.Kode_mata_pelajaran = p.Kode_mata_pelajaran
  LEFT JOIN kompetensi_keahlian kk ON kk.Kode_KK = p.Kode_KK
   ORDER BY mp.Nama_mata_pelajaran ASC'
)->fetchAll();
foreach ($rows as $r) {
    $tugasPerGuru[$r['Kode_guru']][] = $r;
}

$buka  = trim($_GET['buka'] ?? '');
$flash = get_flash();

$pageTitle = "Guru & Pengajaran - Panel Admin";
include __DIR__ . '/../../components/header.php';
include __DIR__ . '/../../components/sidebar-admin.php';

$ico = [
    'plus'  => 'M12 4v16m8-8H4',
    'edit'  => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    'trash' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
];
$inp = 'w-full px-4 py-2.5 clay-inset rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none';
$lbl = 'block text-[11px] font-extrabold uppercase text-slate-400 mb-1 pl-1';
?>

<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <?php include __DIR__ . '/../../components/flash.php'; ?>

    <div id="notif-kk" class="hidden clay-block-alpha text-rose-950 dark:text-rose-200 px-4 py-3 rounded-2xl flex items-start gap-2.5 shadow-xs text-xs font-extrabold">
        <svg class="w-4 h-4 shrink-0 mt-px" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
        <span id="notif-kk-pesan" class="leading-snug flex-1"></span>
        <button type="button" onclick="document.getElementById('notif-kk').classList.add('hidden')" class="shrink-0 font-black text-base leading-none cursor-pointer">&times;</button>
    </div>

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">Guru &amp; Pengajaran</h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                Klik nama guru untuk melihat dan mengatur mapel yang diampu &bull; <?= e(TAHUN_AJARAN_AKTIF . ' ' . SEMESTER_AKTIF) ?>
            </p>
        </div>
        <button type="button" onclick="toggleModal('modal-guru', true)"
            class="px-4 py-2.5 clay-block-ocean text-white rounded-2xl text-xs sm:text-sm font-extrabold flex items-center gap-2 shadow-sm hover:opacity-90 transition-all cursor-pointer">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ico['plus'] ?>"/></svg>
            <span>Tambah Guru</span>
        </button>
    </div>

    <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">Daftar Guru</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-brand-primary bg-brand-primary/10 dark:bg-sky-400/10 dark:text-sky-400">
                    <?= count($daftarGuru) ?> Guru
                </span>
            </div>
            <input type="text" id="cari" placeholder="Cari nama, kode, atau NIP..."
                   class="w-full sm:w-64 px-3.5 py-2 rounded-xl clay-inset text-xs font-bold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none bg-white/50 dark:bg-slate-800/50">
        </div>

        <?php if (!$daftarGuru): ?>
            <p class="py-10 text-center text-xs font-bold text-slate-400">Belum ada data guru terdaftar.</p>
        <?php endif; ?>

        <div class="divide-y divide-slate-200/60 dark:divide-slate-700/60" id="list-guru">
        <?php foreach ($daftarGuru as $g):
            $kode  = $g['Kode_guru'];
            $tugas = $tugasPerGuru[$kode] ?? [];
            $kkGuru = ($g['Kode_KK'] ?? '');
            $kkKunci = ($tugas[0]['Kode_KK'] ?? '') !== '' ? $tugas[0]['Kode_KK'] : $kkGuru;
            $namaKunci = $tugas[0]['Nama_kompetensi_keahlian'] ?? '';
            if ($namaKunci === '' && $kkKunci !== '') { foreach ($daftarKK as $kkRef) { if (($kkRef['Kode_KK'] ?? '') === $kkKunci) { $namaKunci = $kkRef['Nama_kompetensi_keahlian'] ?? $kkKunci; break; } } if ($namaKunci === '') $namaKunci = $kkKunci; }
            $open  = ($buka === $kode);
        ?>
            <div class="item-guru" data-cari="<?= e(strtolower($g['Nama_guru'] . ' ' . $kode . ' ' . ($g['NIP'] ?? ''))) ?>">
                <!-- Baris guru -->
                <div class="flex items-center gap-3 py-3.5">
                    <button type="button" onclick="toggleDetail('<?= e($kode) ?>')" class="flex-1 min-w-0 flex items-center gap-3 text-left cursor-pointer">
                        <svg id="chev-<?= e($kode) ?>" class="w-4 h-4 text-slate-400 shrink-0 transition-transform <?= $open ? 'rotate-90' : '' ?>" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        <div class="min-w-0">
                            <div class="font-extrabold text-sm text-slate-800 dark:text-slate-100 truncate"><?= e($g['Nama_guru']) ?></div>
                            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">
                                <span class="text-brand-primary dark:text-sky-400 font-bold"><?= e($kode) ?></span>
                                &bull; NIP <?= e($g['NIP'] ?: '-') ?><?= !empty($g['Email_guru']) ? ' &bull; ' . e($g['Email_guru']) : '' ?>
                            </div>
                        </div>
                    </button>
                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-black shrink-0 <?= $tugas ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' ?>">
                        <?= $tugas ? count($tugas) . ' mapel' : 'Belum ditugaskan' ?>
                    </span>
                    <button type="button" onclick="editGuru('<?= e($kode) ?>')" class="p-1.5 rounded-lg text-sky-500 hover:bg-sky-500/10 cursor-pointer shrink-0" title="Edit biodata guru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ico['edit'] ?>"/></svg>
                        </button>
                        <form method="POST" class="shrink-0 js-confirm-hapus" data-nama="<?= e($g['Nama_guru'] . ' (' . $kode . ')') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus_guru">
                        <input type="hidden" name="kode_guru" value="<?= e($kode) ?>">
                        <button class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-500/10 cursor-pointer" title="Hapus guru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ico['trash'] ?>"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Detail penugasan -->
                <div id="detail-<?= e($kode) ?>" class="<?= $open ? '' : 'hidden' ?> pb-4 pl-7">
                    <div class="rounded-2xl border border-slate-200/70 dark:border-white/10 bg-slate-100/60 dark:bg-slate-800/40 overflow-hidden">
                        <div class="flex items-center justify-between gap-2 flex-wrap px-4 py-3 bg-slate-50 dark:bg-white/5 border-b border-slate-200/70 dark:border-white/10">
                            <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penugasan mengajar &bull; <?= count($tugas) ?> mapel</p>
                            <span class="text-[10px] font-bold text-slate-400"><?= e(TAHUN_AJARAN_AKTIF . ' ' . SEMESTER_AKTIF) ?></span>
                        </div>
                        <form method="POST" class="flex items-center gap-2 flex-wrap px-4 py-2.5 bg-slate-50 dark:bg-white/5 border-b border-slate-200/70 dark:border-white/10">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="edit_kk_guru">
                            <input type="hidden" name="kode_guru" value="<?= e($kode) ?>">
                            <input type="hidden" name="buka" value="<?= e($kode) ?>">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">KK Guru:</label>
                            <select name="Kode_KK" class="!py-1.5 !px-3 text-xs font-bold rounded-xl clay-inset cursor-pointer text-slate-700 dark:text-slate-200" <?= $tugas ? 'disabled' : '' ?>>
                                <?php foreach ($daftarKK as $kk): ?>
                                    <option value="<?= e($kk['Kode_KK']) ?>" <?= ($kk['Kode_KK'] ?? '') === $kkGuru ? 'selected' : '' ?>><?= e($kk['Nama_kompetensi_keahlian']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$tugas): ?>
                            <button class="px-3 py-1.5 rounded-xl text-[11px] font-extrabold bg-amber-500/15 text-amber-700 hover:bg-amber-500/25 cursor-pointer">Ubah KK</button>
                            <?php else: ?>
                            <span class="text-[10px] font-bold text-slate-400">Terkunci (hapus penugasan dulu)</span>
                            <?php endif; ?>
                        </form>
                        <div class="p-4 space-y-3">
                        <?php if (!$tugas): ?>
                            <p class="text-xs font-bold text-slate-400">Guru ini belum mengampu mata pelajaran.</p>
                        <?php else: ?>
                            <ul class="flex flex-col gap-2">
                            <?php foreach ($tugas as $t): ?>
                                <li class="flex items-center justify-between gap-3 text-xs px-3 py-2.5 rounded-xl border border-slate-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5">
                                    <span class="min-w-0">
                                        <span class="font-extrabold text-slate-700 dark:text-slate-200"><?= e($t['Nama_mata_pelajaran'] ?? $t['Kode_mata_pelajaran']) ?></span>
                                        <span class="text-slate-500 dark:text-slate-400 font-semibold"> &bull; <?= e($t['Nama_kompetensi_keahlian'] ?? $t['Kode_KK']) ?> &bull; <?= e($t['Tahun_ajaran'] . ' ' . $t['Semester']) ?></span>
                                    </span>
                                    <form method="POST" class="js-confirm-hapus" data-nama="<?= e('Penugasan ' . ($t['Nama_mata_pelajaran'] ?? $t['Kode_mata_pelajaran']) . ' — ' . ($t['Nama_kompetensi_keahlian'] ?? $t['Kode_KK'])) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="hapus_tugas">
                                        <input type="hidden" name="id_pengajaran" value="<?= (int) $t['id_pengajaran'] ?>">
                                        <input type="hidden" name="buka" value="<?= e($kode) ?>">
                                        <button class="p-1 rounded-md text-rose-500 hover:bg-rose-500/10 cursor-pointer" title="Hapus penugasan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <!-- Form tambah penugasan -->
                        <form method="POST" class="pt-3 border-t border-slate-200/70 dark:border-slate-700/60" onsubmit="return kunciKK(this)" data-kunci="<?= e($kkKunci) ?>" data-nama="<?= e($namaKunci) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="tambah_tugas">
                            <input type="hidden" name="Kode_guru" value="<?= e($kode) ?>">
                            <input type="hidden" name="buka" value="<?= e($kode) ?>">
                            <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Tambah penugasan baru<?php if ($kkKunci !== ''): ?> &bull; Terkunci: <?= e($namaKunci) ?><?php endif; ?></p>
                            <div class="flex flex-col sm:flex-row gap-2.5 sm:items-end">
                                <div class="flex-1 min-w-0 sm:min-w-[180px]">
                                    <label class="<?= $lbl ?>">Jurusan / KK</label>
                                    <?php if ($kkKunci !== ''): ?>
                                    <input type="hidden" name="Kode_KK" value="<?= e($kkKunci) ?>">
                                    <select disabled class="<?= $inp ?> cursor-not-allowed opacity-80">
                                        <option selected><?= e($namaKunci) ?></option>
                                    </select>
                                    <?php else: ?>
                                    <select name="Kode_KK" required onchange="filterMapel(this)" class="<?= $inp ?> cursor-pointer">
                                        <?php foreach ($daftarKK as $kk): ?>
                                            <option value="<?= e($kk['Kode_KK']) ?>"><?= e($kk['Nama_kompetensi_keahlian']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0 sm:min-w-[200px]">
                                    <label class="<?= $lbl ?>">Mata pelajaran</label>
                                    <?php $adaKunci = $kkKunci !== ''; $mapelKK = $adaKunci ? array_values(array_filter($daftarMapel, fn($m) => ($m['Kode_KK'] ?? '') === $kkKunci)) : $daftarMapel; ?>
                                    <select name="Kode_mata_pelajaran" <?= $mapelKK ? 'required' : 'disabled' ?> class="<?= $inp ?> cursor-pointer sel-mapel" <?= $mapelKK ? '' : 'disabled' ?>>
                                        <?php if ($adaKunci && !$mapelKK): ?>
                                            <option value="" selected disabled>Belum ada mata pelajaran untuk KK ini</option>
                                        <?php endif; ?>
                                        <?php foreach ($daftarMapel as $m): ?>
                                            <option value="<?= e($m['Kode_mata_pelajaran']) ?>" data-kk="<?= e($m['Kode_KK']) ?>"><?= e($m['Nama_mata_pelajaran']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="shrink-0">
                                    <button <?= $mapelKK ? '' : 'disabled' ?> class="px-5 py-2.5 clay-block-ocean text-white rounded-xl text-xs font-extrabold cursor-pointer whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed">Tambah</button>
                                </div>
                            </div>
                            <p class="text-[11px] font-semibold text-slate-400 mt-2"><?php if ($kkKunci !== ''): ?>Guru ini terkunci ke <?= e($namaKunci) ?>. Satu guru hanya bisa mengajar 1 kompetensi keahlian.<?php else: ?>Daftar mapel otomatis mengikuti jurusan terpilih.<?php endif; ?></p>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
            <p id="kosong" class="hidden py-10 text-center text-xs font-bold text-slate-400">Tidak ada guru yang cocok.</p>
        </div>
    </div>
</main>

<!-- Modal tambah guru -->
<div id="modal-guru" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-lg shadow-2xl border border-slate-200/80 dark:border-white/10">
        <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100">Tambah Guru Baru</h3>
            <button type="button" onclick="toggleModal('modal-guru', false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form method="POST" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah_guru">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">Kode Guru</label><input type="text" name="Kode_guru" required placeholder="G-005" class="<?= $inp ?>"></div>
                <div>
                    <label class="<?= $lbl ?>">Jurusan (KK)</label>
                    <select name="Kode_KK" required class="<?= $inp ?> cursor-pointer">
                        <?php foreach ($daftarKK as $kk): ?>
                            <option value="<?= e($kk['Kode_KK']) ?>"><?= e($kk['Nama_kompetensi_keahlian']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div><label class="<?= $lbl ?>">Nama Lengkap</label><input type="text" name="Nama_guru" required placeholder="Nama beserta gelar" class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">NIP (username login)</label><input type="text" name="NIP" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Email</label><input type="email" name="Email_guru" class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Password Awal</label><input type="text" name="Password" value="guru123" required class="<?= $inp ?>"></div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                <button type="button" onclick="toggleModal('modal-guru', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
                <button class="px-5 py-2 clay-block-ocean text-white rounded-xl text-xs font-extrabold cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal edit guru -->
<div id="modal-edit-guru" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-lg shadow-2xl border border-slate-200/80 dark:border-white/10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100">Edit Biodata Guru</h3>
            <button type="button" onclick="toggleModal('modal-edit-guru', false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form method="POST" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="edit_guru">
            <input type="hidden" name="Kode_guru_lama" id="edit_kode_lama">
            <input type="hidden" name="buka" id="edit_buka">
            <div><label class="<?= $lbl ?>">Kode Guru</label><input type="text" name="Kode_guru" id="edit_kode" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Nama Lengkap</label><input type="text" name="Nama_guru" id="edit_nama" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">NIP (username login)</label><input type="text" name="NIP" id="edit_nip" required class="<?= $inp ?>"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div><label class="<?= $lbl ?>">Telepon</label><input type="text" name="Telp_guru" id="edit_telp" class="<?= $inp ?>"></div>
                <div><label class="<?= $lbl ?>">Email</label><input type="email" name="Email_guru" id="edit_email" class="<?= $inp ?>"></div>
            </div>
            <div><label class="<?= $lbl ?>">Alamat</label><textarea name="Alamat_guru" id="edit_alamat" rows="2" class="<?= $inp ?>"></textarea></div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                <button type="button" onclick="toggleModal('modal-edit-guru', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
                <button class="px-5 py-2 clay-block-ocean text-white rounded-xl text-xs font-extrabold cursor-pointer">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(id, show) { document.getElementById(id).classList.toggle('hidden', !show); }
async function editGuru(kode) {
    try {
        const res = await fetch('<?= base_url("api/get-guru.php") ?>?kode=' + encodeURIComponent(kode));
        if (!res.ok) throw new Error('Gagal mengambil data guru');
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        document.getElementById('edit_kode_lama').value = data.Kode_guru;
        document.getElementById('edit_buka').value = data.Kode_guru;
        document.getElementById('edit_kode').value = data.Kode_guru || '';
        document.getElementById('edit_nama').value = data.Nama_guru || '';
        document.getElementById('edit_nip').value = data.NIP || '';
        document.getElementById('edit_telp').value = data.Telp_guru || '';
        document.getElementById('edit_email').value = data.Email_guru || '';
        document.getElementById('edit_alamat').value = data.Alamat_guru || '';
        toggleModal('modal-edit-guru', true);
    } catch (err) {
        alert('Error: ' + err.message);
    }
}
function toggleDetail(k) {
    document.getElementById('detail-' + k).classList.toggle('hidden');
    document.getElementById('chev-' + k).classList.toggle('rotate-90');
}
// Opsi mapel mengikuti jurusan yang dipilih
function filterMapel(selKK) {
    const form = selKK.closest('form');
    const sel = form ? form.querySelector('.sel-mapel') : null;
    if (!sel) return;
    terapkanFilterMapel(sel, selKK.value);
}
function terapkanFilterMapel(sel, kodeKK) {
    let first = null, n = 0;
    sel.querySelectorAll('option').forEach(o => {
        if (!o.value) return;
        const cocok = !kodeKK || o.dataset.kk === kodeKK;
        o.hidden = !cocok; o.disabled = !cocok;
        if (cocok && !first) first = o;
        if (cocok) n++;
    });
    let ph = sel.querySelector('option[value=""]');
    if (!n) {
        if (!ph) { ph = document.createElement('option'); ph.value = ''; sel.prepend(ph); }
        ph.textContent = 'Belum ada mata pelajaran untuk KK ini';
        ph.hidden = false; ph.disabled = true;
        sel.value = '';
        const btn = sel.closest('form') ? sel.closest('form').querySelector('button:last-of-type') : null;
        if (btn && btn.textContent.includes('Tambah')) btn.disabled = true;
    } else {
        if (ph) ph.remove();
        if (first) sel.value = first.value;
        const btn = sel.closest('form') ? sel.closest('form').querySelector('button:last-of-type') : null;
        if (btn && btn.textContent.includes('Tambah')) btn.disabled = false;
    }
}
// Satu guru hanya bisa mengajar 1 kompetensi keahlian: tolak submit lintas KK.
function kunciKK(form) {
    const kunci = form.dataset.kunci || '';
    if (!kunci) return true;
    const sel = form.querySelector('.sel-mapel');
    const opt = sel ? sel.selectedOptions[0] : null;
    const kkMapel = opt ? (opt.dataset.kk || '') : '';
    const nama = form.dataset.nama || kunci;
    if (kkMapel && kkMapel !== kunci) {
        tampilNotifKK('Satu guru hanya bisa mengajar 1 kompetensi keahlian (' + nama + ').');
        if (sel) terapkanFilterMapel(sel, kunci);
        return false;
    }
    return true;
}
function tampilNotifKK(pesan) {
    const box = document.getElementById('notif-kk');
    const teks = document.getElementById('notif-kk-pesan');
    if (!box || !teks) return;
    teks.textContent = pesan;
    box.classList.remove('hidden');
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
document.querySelectorAll('select[name=Kode_KK][onchange]').forEach(filterMapel);
document.querySelectorAll('form[data-kunci]').forEach(form => {
    const kunci = form.dataset.kunci || '';
    const sel = form.querySelector('.sel-mapel');
    if (kunci && sel) terapkanFilterMapel(sel, kunci);
});

document.getElementById('cari').addEventListener('input', e => {
    const v = e.target.value.toLowerCase().trim();
    let n = 0;
    document.querySelectorAll('.item-guru').forEach(el => {
        const ok = el.dataset.cari.includes(v);
        el.style.display = ok ? '' : 'none';
        if (ok) n++;
    });
    document.getElementById('kosong').classList.toggle('hidden', n > 0);
});
</script>

<!-- Modal konfirmasi hapus (reusable) -->
<?php include __DIR__ . '/../../components/confirm-hapus.php'; ?>

<?php include __DIR__ . '/../../components/footer.php'; ?>
