-- =====================================================================
-- MIGRASI TAMBAHAN (untuk database db_rapor yang SUDAH terlanjur ada)
-- ---------------------------------------------------------------------
-- Jalankan file ini bila Anda tidak ingin menghapus data lama.
-- Bila database masih kosong, cukup import `db_rapor_full.sql`.
--
-- Semua perubahan bersifat ADDITIVE: tidak ada tabel/kolom lama
-- yang dihapus atau diubah tipenya.
-- =====================================================================

USE `db_rapor`;

-- 1. Kolom autentikasi & kontak guru --------------------------------
ALTER TABLE `guru`
  ADD COLUMN `Email_guru` varchar(100) DEFAULT NULL AFTER `Telp_guru`,
  ADD COLUMN `Password` varchar(255) NOT NULL DEFAULT '' AFTER `Email_guru`;

ALTER TABLE `guru`
  ADD UNIQUE KEY `uq_guru_nip` (`NIP`);

-- 2. Kolom autentikasi siswa ----------------------------------------
ALTER TABLE `siswa`
  ADD COLUMN `Password` varchar(255) NOT NULL DEFAULT '';

-- 3. SK dipetakan ke mata pelajaran ---------------------------------
ALTER TABLE `standar_kompetensi`
  ADD COLUMN `Kode_mata_pelajaran` varchar(50) DEFAULT NULL AFTER `Kode_KK`,
  ADD KEY `idx_sk_mapel` (`Kode_mata_pelajaran`);

ALTER TABLE `standar_kompetensi`
  ADD CONSTRAINT `standar_kompetensi_ibfk_2` FOREIGN KEY (`Kode_mata_pelajaran`)
    REFERENCES `mata_pelajaran` (`Kode_mata_pelajaran`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Isi otomatis dari mapel bawaan kompetensi keahlian (bila belum diisi).
UPDATE `standar_kompetensi` sk
  JOIN `kompetensi_keahlian` kk ON kk.`Kode_KK` = sk.`Kode_KK`
   SET sk.`Kode_mata_pelajaran` = kk.`Kode_mata_pelajaran`
 WHERE sk.`Kode_mata_pelajaran` IS NULL;

-- 4. Tabel penugasan mengajar (kelas ajar) --------------------------
CREATE TABLE IF NOT EXISTS `pengajaran` (
  `id_pengajaran` int(11) NOT NULL AUTO_INCREMENT,
  `Kode_guru` varchar(50) NOT NULL,
  `Kode_KK` varchar(50) NOT NULL,
  `Kode_mata_pelajaran` varchar(50) NOT NULL,
  `Tahun_ajaran` varchar(20) DEFAULT NULL,
  `Semester` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_pengajaran`),
  UNIQUE KEY `uq_pengajaran` (`Kode_guru`,`Kode_KK`,`Kode_mata_pelajaran`,`Tahun_ajaran`,`Semester`),
  KEY `Kode_KK` (`Kode_KK`),
  KEY `Kode_mata_pelajaran` (`Kode_mata_pelajaran`),
  CONSTRAINT `pengajaran_ibfk_1` FOREIGN KEY (`Kode_guru`)
    REFERENCES `guru` (`Kode_guru`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pengajaran_ibfk_2` FOREIGN KEY (`Kode_KK`)
    REFERENCES `kompetensi_keahlian` (`Kode_KK`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pengajaran_ibfk_3` FOREIGN KEY (`Kode_mata_pelajaran`)
    REFERENCES `mata_pelajaran` (`Kode_mata_pelajaran`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Unique key untuk upsert nilai ----------------------------------
-- Hapus dulu duplikat (NISN, Kode_SK) bila ada, sisakan id terkecil.
DELETE n1 FROM `nilai` n1
  JOIN `nilai` n2
    ON n1.`NISN` = n2.`NISN`
   AND n1.`Kode_SK` = n2.`Kode_SK`
   AND n1.`id_nilai` > n2.`id_nilai`;

ALTER TABLE `nilai`
  ADD UNIQUE KEY `uq_nilai_siswa_sk` (`NISN`,`Kode_SK`);

-- 6. Password awal untuk akun yang sudah ada ------------------------
-- (plaintext; otomatis di-hash aplikasi saat login pertama)
UPDATE `guru`  SET `Password` = 'guru123'  WHERE `Password` = '';
UPDATE `siswa` SET `Password` = 'siswa123' WHERE `Password` = '';

-- 7. Contoh penugasan mengajar --------------------------------------
-- Sesuaikan Kode_guru / Kode_KK / Kode_mata_pelajaran dengan data Anda.
-- INSERT INTO `pengajaran` (`Kode_guru`, `Kode_KK`, `Kode_mata_pelajaran`, `Tahun_ajaran`, `Semester`)
-- VALUES ('G-001', 'KK-RPL', 'MP-PW', '2026/2027', 'Ganjil');
