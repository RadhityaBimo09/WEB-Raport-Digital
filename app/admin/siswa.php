<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin-repository.php';

$admin = wajib_login('admin');

$filterJurusan = trim($_GET['jurusan'] ?? '');
$filterTingkat = trim($_GET['tingkat'] ?? '');

$qs = array_filter(['jurusan' => $filterJurusan, 'tingkat' => $filterTingkat], fn($v) => $v !== '' && $v !== 'semua');
$queryString = $qs ? '?' . http_build_query($qs) : '';
$self = base_url('app/admin/siswa.php') . $queryString;

$daftarKK = admin_semua_kk();

// ---------- POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_token'] ?? null)) {
        set_flash('error', 'Token CSRF tidak valid.');
        redirect($self);
    }

    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'tambah') {
            admin_tambah_siswa($_POST);
            set_flash('success', 'Data siswa berhasil ditambahkan.');
        } elseif ($aksi === 'edit') {
            admin_update_siswa($_POST);
            set_flash('success', 'Data siswa berhasil diperbarui.');
        } elseif ($aksi === 'hapus') {
            $nisn = trim($_POST['nisn'] ?? '');
            if ($nisn !== '') {
                admin_hapus_siswa($nisn);
                set_flash('success', 'Data siswa berhasil dihapus.');
            }
        }
    } catch (Throwable $e) {
        set_flash('error', 'Gagal memproses data siswa: ' . $e->getMessage());
    }
    redirect($self);
}

$daftarSiswa = admin_semua_siswa($filterJurusan, $filterTingkat, 'nama_asc');
$flash       = get_flash();

$pageTitle = "Data Siswa - Panel Admin";
include __DIR__ . '/../../components/header.php';
include __DIR__ . '/../../components/sidebar-admin.php';

$inp = 'w-full px-4 py-2.5 clay-inset rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none';
$sel = 'px-3 py-2 clay-inset rounded-xl text-xs font-extrabold text-slate-700 dark:text-slate-200 focus:outline-none cursor-pointer bg-white/50 dark:bg-slate-800/50';
$lbl = 'block text-[11px] font-extrabold uppercase text-slate-400 mb-1 pl-1';
?>

<main class="flex-1 flex flex-col gap-5 lg:gap-6 min-w-0">

    <?php include __DIR__ . '/../../components/flash.php'; ?>

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-slate-100">Data Siswa</h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">Siswa terdaftar &amp; akun login</p>
        </div>
        <button type="button" onclick="toggleModal(true)"
            class="px-4 py-2.5 clay-block-ocean text-white rounded-2xl text-xs sm:text-sm font-extrabold flex items-center gap-2 shadow-sm hover:opacity-90 transition-all cursor-pointer">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Siswa</span>
        </button>
    </div>

    <div class="clay-card rounded-[28px] p-5 lg:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <h2 class="font-extrabold text-slate-800 dark:text-slate-100 text-lg">Daftar Siswa</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black text-emerald-600 bg-emerald-500/10 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <?= count($daftarSiswa) ?> Siswa
                </span>
            </div>

            <form method="GET" class="flex items-center gap-2 flex-wrap">
                <input type="text" id="cari" placeholder="Cari nama, NIS, kelas..."
                       class="w-full sm:w-52 px-3.5 py-2 rounded-xl clay-inset text-xs font-bold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none bg-white/50 dark:bg-slate-800/50">
                <select name="tingkat" onchange="this.form.submit()" class="<?= $sel ?>">
                    <option value="">Semua Kelas</option>
                    <?php foreach (['X', 'XI', 'XII'] as $t): ?>
                        <option value="<?= $t ?>" <?= $filterTingkat === $t ? 'selected' : '' ?>>Kelas <?= $t ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="jurusan" onchange="this.form.submit()" class="<?= $sel ?>">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($daftarKK as $kk): ?>
                        <option value="<?= e($kk['Kode_KK']) ?>" <?= $filterJurusan === $kk['Kode_KK'] ? 'selected' : '' ?>>
                            <?= e($kk['Nama_kompetensi_keahlian']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto pr-1">
            <table class="w-full text-left border-collapse min-w-[640px]" id="tabel">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 border-b border-slate-200 dark:border-slate-700/80">
                        <th class="pb-3 pl-2 w-12">NO</th>
                        <th class="pb-3 w-32">NIS</th>
                        <th class="pb-3">NAMA</th>
                        <th class="pb-3">KELAS &amp; JURUSAN</th>
                        <th class="pb-3 w-40">ALAMAT</th>
                        <th class="pb-3 text-center w-20">AKSI</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200/60 dark:divide-slate-700/60 font-medium text-slate-700 dark:text-slate-200">
                    <?php if (!$daftarSiswa): ?>
                        <tr><td colspan="6" class="py-10 text-center text-xs font-bold text-slate-400">Tidak ada siswa untuk filter ini.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($daftarSiswa as $i => $s): ?>
                        <tr class="hover:bg-slate-100/50 dark:hover:bg-slate-800/40 transition-colors baris">
                            <td class="py-3.5 pl-2 text-slate-400 font-bold"><?= $i + 1 ?></td>
                            <td class="py-3.5 font-bold text-brand-primary dark:text-sky-400 font-mono"><?= e($s['NISN']) ?></td>
                            <td class="py-3.5 font-extrabold text-slate-800 dark:text-slate-100"><?= e($s['Nama_siswa']) ?></td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-600 dark:text-rose-400 font-extrabold text-[11px] border border-rose-500/20 whitespace-nowrap">
                                    <?= e(($s['Tingkat'] ?? 'XII') . ' ' . ($s['Jurusan'] ?? '')) ?>
                                </span>
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 ml-1"><?= e($s['Nama_kompetensi_keahlian'] ?? $s['Kode_KK']) ?></span>
                            </td>
                            <td class="py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 truncate max-w-[160px]"><?= e($s['Alamat_siswa'] ?: '-') ?></td>
                            <td class="py-3.5 text-center">
                                <button onclick="editSiswa('<?= e($s['NISN']) ?>')" class="p-1.5 rounded-lg text-sky-500 hover:bg-sky-500/10 cursor-pointer mr-1" title="Edit siswa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="<?= e($self) ?>" class="inline-block js-confirm-hapus" data-nama="<?= e($s['Nama_siswa'] . ' (' . $s['NISN'] . ')') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="nisn" value="<?= e($s['NISN']) ?>">
                                    <button class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-500/10 cursor-pointer" title="Hapus siswa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="kosong" style="display:none"><td colspan="6" class="py-10 text-center text-xs font-bold text-slate-400">Tidak ada siswa yang cocok.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal tambah siswa -->
<div id="modal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-lg shadow-2xl border border-slate-200/80 dark:border-white/10">
        <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100">Tambah Siswa Baru</h3>
            <button type="button" onclick="toggleModal(false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form method="POST" action="<?= e($self) ?>" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah">
            <div>
                <label class="<?= $lbl ?>">NIS (maks. 8 digit, username login)</label>
                <input type="text" name="NISN" required maxlength="8" inputmode="numeric"
                       oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" placeholder="24180001" class="<?= $inp ?>">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="<?= $lbl ?>">Tingkat Kelas</label>
                    <select name="Tingkat" required class="<?= $inp ?> cursor-pointer">
                        <option value="X">Kelas X</option><option value="XI">Kelas XI</option><option value="XII" selected>Kelas XII</option>
                    </select>
                </div>
                <div>
                    <label class="<?= $lbl ?>">Jurusan (KK)</label>
                    <select name="Kode_KK" required class="<?= $inp ?> cursor-pointer">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($daftarKK as $kk): ?>
                            <option value="<?= e($kk['Kode_KK']) ?>"><?= e($kk['Nama_kompetensi_keahlian']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div><label class="<?= $lbl ?>">Nama Lengkap</label><input type="text" name="Nama_siswa" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Alamat</label><textarea name="Alamat_siswa" rows="2" class="<?= $inp ?>"></textarea></div>
            <div><label class="<?= $lbl ?>">Password Awal</label><input type="text" name="Password" value="siswa123" required class="<?= $inp ?>"></div>
            
            <div class="border-t border-slate-200/60 dark:border-slate-700/60 pt-3 mt-2">
                <h4 class="text-xs font-extrabold uppercase text-slate-500 mb-3">Data Orang Tua/Wali (Opsional)</h4>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="<?= $lbl ?>">Nama Ayah</label><input type="text" name="Nama_ayah" class="<?= $inp ?>"></div>
                        <div><label class="<?= $lbl ?>">Pekerjaan Ayah</label><input type="text" name="Pekerjaan_ayah" class="<?= $inp ?>"></div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="<?= $lbl ?>">Nama Ibu</label><input type="text" name="Nama_ibu" class="<?= $inp ?>"></div>
                        <div><label class="<?= $lbl ?>">Pekerjaan Ibu</label><input type="text" name="Pekerjaan_ibu" class="<?= $inp ?>"></div>
                    </div>
                    <div><label class="<?= $lbl ?>">Alamat Wali</label><textarea name="Alamat_wali" rows="2" class="<?= $inp ?>"></textarea></div>
                    <div><label class="<?= $lbl ?>">No. Telepon Wali</label><input type="text" name="Telp_wali" class="<?= $inp ?>"></div>
                </div>
            </div>
            
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                <button type="button" onclick="toggleModal(false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
                <button class="px-5 py-2 clay-block-ocean text-white rounded-xl text-xs font-extrabold cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal edit siswa -->
<div id="modalEdit" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="clay-card bg-white dark:bg-slate-900 rounded-[28px] p-6 w-full max-w-lg shadow-2xl border border-slate-200/80 dark:border-white/10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-3 mb-4">
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-slate-100">Edit Data Siswa</h3>
            <button type="button" onclick="toggleModalEdit(false)" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form method="POST" action="<?= e($self) ?>" class="space-y-3.5">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="NISN_lama" id="edit_nisn_lama">
            <div>
                <label class="<?= $lbl ?>">NIS (maks. 8 digit, username login)</label>
                <input type="text" name="NISN" id="edit_nisn" required maxlength="8" inputmode="numeric"
                       oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" placeholder="24180001" class="<?= $inp ?>">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="<?= $lbl ?>">Tingkat Kelas</label>
                    <select name="Tingkat" id="edit_tingkat" required class="<?= $inp ?> cursor-pointer">
                        <option value="X">Kelas X</option><option value="XI">Kelas XI</option><option value="XII">Kelas XII</option>
                    </select>
                </div>
                <div>
                    <label class="<?= $lbl ?>">Jurusan (KK)</label>
                    <select name="Kode_KK" id="edit_kk" required class="<?= $inp ?> cursor-pointer">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($daftarKK as $kk): ?>
                            <option value="<?= e($kk['Kode_KK']) ?>"><?= e($kk['Nama_kompetensi_keahlian']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div><label class="<?= $lbl ?>">Nama Lengkap</label><input type="text" name="Nama_siswa" id="edit_nama" required class="<?= $inp ?>"></div>
            <div><label class="<?= $lbl ?>">Alamat</label><textarea name="Alamat_siswa" id="edit_alamat" rows="2" class="<?= $inp ?>"></textarea></div>
            
            <div class="border-t border-slate-200/60 dark:border-slate-700/60 pt-3 mt-2">
                <h4 class="text-xs font-extrabold uppercase text-slate-500 mb-3">Data Orang Tua/Wali (Opsional)</h4>
                <input type="hidden" name="Kode_wali" id="edit_kode_wali">
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="<?= $lbl ?>">Nama Ayah</label><input type="text" name="Nama_ayah" id="edit_ayah" class="<?= $inp ?>"></div>
                        <div><label class="<?= $lbl ?>">Pekerjaan Ayah</label><input type="text" name="Pekerjaan_ayah" id="edit_pek_ayah" class="<?= $inp ?>"></div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="<?= $lbl ?>">Nama Ibu</label><input type="text" name="Nama_ibu" id="edit_ibu" class="<?= $inp ?>"></div>
                        <div><label class="<?= $lbl ?>">Pekerjaan Ibu</label><input type="text" name="Pekerjaan_ibu" id="edit_pek_ibu" class="<?= $inp ?>"></div>
                    </div>
                    <div><label class="<?= $lbl ?>">Alamat Wali</label><textarea name="Alamat_wali" id="edit_alamat_wali" rows="2" class="<?= $inp ?>"></textarea></div>
                    <div><label class="<?= $lbl ?>">No. Telepon Wali</label><input type="text" name="Telp_wali" id="edit_telp_wali" class="<?= $inp ?>"></div>
                </div>
            </div>
            
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                <button type="button" onclick="toggleModalEdit(false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Batal</button>
                <button class="px-5 py-2 clay-block-ocean text-white rounded-xl text-xs font-extrabold cursor-pointer">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(show) { document.getElementById('modal').classList.toggle('hidden', !show); }
function toggleModalEdit(show) { document.getElementById('modalEdit').classList.toggle('hidden', !show); }

async function editSiswa(nisn) {
    try {
        const res = await fetch('<?= base_url("api/get-siswa.php") ?>?nisn=' + encodeURIComponent(nisn));
        if (!res.ok) throw new Error('Gagal mengambil data siswa');
        const data = await res.json();
        
        // Populate form siswa
        document.getElementById('edit_nisn_lama').value = data.NISN;
        document.getElementById('edit_nisn').value = data.NISN || '';
        document.getElementById('edit_tingkat').value = data.Tingkat || 'XII';
        document.getElementById('edit_kk').value = data.Kode_KK;
        document.getElementById('edit_nama').value = data.Nama_siswa;
        document.getElementById('edit_alamat').value = data.Alamat_siswa || '';
        
        // Populate form wali
        document.getElementById('edit_kode_wali').value = data.Kode_wali || '';
        document.getElementById('edit_ayah').value = data.Nama_ayah || '';
        document.getElementById('edit_pek_ayah').value = data.Pekerjaan_ayah || '';
        document.getElementById('edit_ibu').value = data.Nama_ibu || '';
        document.getElementById('edit_pek_ibu').value = data.Pekerjaan_ibu || '';
        document.getElementById('edit_alamat_wali').value = data.Alamat_wali || '';
        document.getElementById('edit_telp_wali').value = data.Telp_wali || '';
        
        toggleModalEdit(true);
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

document.getElementById('cari').addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });
document.getElementById('cari').addEventListener('input', e => {
    const v = e.target.value.toLowerCase().trim();
    let n = 0;
    document.querySelectorAll('#tabel .baris').forEach(r => {
        const ok = r.textContent.toLowerCase().includes(v);
        r.style.display = ok ? '' : 'none';
        if (ok) n++;
    });
    document.getElementById('kosong').style.display = (n === 0 && v !== '') ? '' : 'none';
});
</script>

<!-- Modal konfirmasi hapus (reusable) -->
<?php include __DIR__ . '/../../components/confirm-hapus.php'; ?>

<?php include __DIR__ . '/../../components/footer.php'; ?>
