# E-Rapor Digital — Backend Portal Guru

Dokumentasi perubahan backend (PHP + MySQL/MariaDB) untuk modul **Portal Guru**.

---

## 1. Cara Menjalankan

1. Salin folder `pelaporan_nilai/` ke `htdocs/` (XAMPP) atau `www/` (Laragon).
2. Import database lewat phpMyAdmin:
   - Database **baru / kosong** → import `database/db_rapor_full.sql`
   - Database **db_rapor sudah ada & berisi data** → import `database/upgrade_backend_guru.sql`
3. Sesuaikan kredensial di `config/database.php` bila user/password MySQL berbeda.
4. Buka `http://localhost/pelaporan_nilai/`.

### Akun uji coba

| Peran | Username (NIP/NISN)  | Password   |
|-------|----------------------|------------|
| Guru  | `198501012010011002` | `guru123`  |
| Guru  | `199003152015032005` | `guru123`  |
| Siswa | `0051234501`         | `siswa123` |

Password di-seed sebagai teks biasa, lalu **otomatis dikonversi ke hash
`password_hash()`** saat login pertama kali (lihat `verifikasi_password()` dan
`upgrade_password()` di `config/auth.php`). Jadi setelah login pertama, kolom
`Password` di database sudah berisi hash bcrypt.

---

## 2. Struktur File Baru

```
config/
  database.php           Koneksi PDO + konstanta tahun ajaran aktif
  helpers.php            e(), nilai_ke_huruf(), flash, CSRF, tanggal ID
  auth.php               Session, login, guard halaman, logout, base_url()
  guru-repository.php    Seluruh query modul guru (kelas ajar, SK, nilai, rekap)

auth/
  login.php              Form login (pintas Demo Mode DIHAPUS)
  process-login.php      Validasi kredensial ke database
  logout.php             Hancurkan session

app/guru/
  dashboard.php          Data guru + daftar kelas ajar (real data)
  input-nilai.php        Pilih kelas ajar → form penilaian per SK
  simpan-nilai.php       Handler POST penyimpanan nilai (upsert, transaksi)
  rekap-nilai.php        Rekap per mapel / gabungan
  export-rekap.php       Export CSV (Excel) & tampilan cetak PDF

components/
  flash.php              Komponen notifikasi sukses/gagal

database/
  db_rapor_full.sql          Struktur + data contoh (import sekali jalan)
  upgrade_backend_guru.sql   Migrasi additive untuk DB yang sudah ada
```

---

## 3. Perubahan Skema Database

Skema asli dipertahankan; berikut tambahannya beserta alasannya.

| Perubahan | Alasan |
|---|---|
| `guru.Password`, `siswa.Password` | Tidak ada kolom kredensial di ERD awal, sedangkan login butuh verifikasi password. |
| `guru.Email_guru` | Ditampilkan pada kartu "Detail Pengajar" di dashboard. |
| `guru.NIP` → UNIQUE | NIP dipakai sebagai identitas login, harus unik. |
| Tabel **`pengajaran`** | ERD awal hanya mengizinkan 1 guru = 1 kompetensi keahlian = 1 mapel, sementara UI menampilkan seorang guru mengampu 2 mata pelajaran. Tabel ini merelasikan **guru × kelas (KK) × mata pelajaran × periode**. Satu barisnya = satu "Kelas Ajar" di portal. |
| `standar_kompetensi.Kode_mata_pelajaran` | Agar setiap SK jelas milik mapel mana (SK-PW1…4 untuk Pemrograman Web, SK-BD1…3 untuk Basis Data). |
| `nilai` UNIQUE (`NISN`,`Kode_SK`) | Menjamin satu nilai per siswa per SK, sekaligus memungkinkan `INSERT … ON DUPLICATE KEY UPDATE` saat guru menyimpan ulang. |

---

## 4. Alur Data Portal Guru

**Login** → `auth/process-login.php` mencocokkan NIP ke tabel `guru`
(fallback NISN ke tabel `siswa`), memverifikasi password, `session_regenerate_id()`,
lalu menyimpan identitas ke `$_SESSION['user']`.

**Guard** → setiap halaman guru memanggil `wajib_login('guru')` di baris paling atas
sebelum ada output HTML. Siswa yang memaksa membuka URL guru akan dilempar balik ke
portal siswa, dan pengunjung tanpa session dilempar ke halaman login.

**Dashboard** → `guru_kelas_ajar($kodeGuru)` membaca tabel `pengajaran` untuk periode
aktif, lengkap dengan jumlah SK, jumlah siswa, dan KKM minimal per mapel.

**Input nilai** → `input-nilai.php?kelas=<id_pengajaran>&sk=<Kode_SK>`.
Nilai lama dimuat dari tabel `nilai`; kolom yang dikosongkan berarti "belum dinilai".

**Simpan** → `simpan-nilai.php` memvalidasi berlapis sebelum menulis:

1. session guru aktif;
2. token CSRF cocok;
3. `id_pengajaran` benar milik guru yang login (mencegah edit kelas guru lain lewat URL);
4. `Kode_SK` benar milik mapel tersebut;
5. `NISN` benar anggota kelas tersebut;
6. nilai numerik 0–100.

Penyimpanan dibungkus transaksi — bila satu baris gagal, seluruh perubahan
di-*rollback*. Nilai huruf dihitung di server oleh `nilai_ke_huruf()` dengan skala
yang sama persis dengan `recalculateGrade()` di `assets/scripts/main.js`
(A ≥ 88, B ≥ 78, C ≥ 70, selebihnya D), sehingga badge di layar dan data
tersimpan tidak pernah berbeda.

**Rekap** → `rekap-nilai.php?kelas=all` menampilkan rata-rata per mata pelajaran,
`?kelas=<id>` menampilkan kolom per Standar Kompetensi. Tombol *Export* kini
benar-benar berfungsi: CSV ber-BOM UTF-8 (langsung rapi di Excel) dan tampilan
cetak untuk disimpan sebagai PDF.

**Panel kanan** → progres input nilai dihitung real-time:
`jumlah sel nilai terisi ÷ (jumlah SK × jumlah siswa)` per kelas ajar.

---

## 5. Pembersihan Demo Mode

- Blok "Pintas Akses Langsung (Demo Mode)" pada `auth/login.php` **dihapus**,
  diganti catatan cara login.
- `auth/process-login.php` tidak lagi menebak peran dari teks username; peran
  ditentukan oleh tabel mana yang cocok saat autentikasi.
- Tautan keluar di `components/sidebar.php` yang sebelumnya menuju
  `auth/login.php` sekarang menuju `auth/logout.php`.
- Halaman siswa ikut diberi guard `wajib_login('siswa')` supaya tidak bisa
  diakses langsung setelah pintas demo dihapus.

---

## 6. Catatan Lanjutan

- **Portal siswa** masih memakai data statis di dalam file view. Yang sudah
  berjalan nyata di sisi siswa hanyalah autentikasi dan guard; penggantian data
  dummy-nya berada di luar ruang lingkup pekerjaan ini dan bisa memakai fungsi
  yang sama di `config/guru-repository.php` sebagai acuan.
- Untuk produksi, pertimbangkan menambah pembatasan percobaan login
  (rate limiting) dan halaman khusus reset password oleh admin.
- Jika folder project bukan `pelaporan_nilai`, `base_url()` tetap mendeteksi
  otomatis dari `SCRIPT_NAME`, jadi tidak perlu diubah manual.
