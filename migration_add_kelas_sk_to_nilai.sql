-- =====================================================================
-- MIGRATION: Add Kelas_SK to nilai table for composite FK support
-- =====================================================================
-- This fixes the issue where SK-BD1 for XI shows XII students
-- because nilai table can't distinguish between same Kode_SK for different classes
-- =====================================================================

USE db_raport;

-- Step 1: Add Kelas_SK column to nilai table
ALTER TABLE nilai 
ADD COLUMN Kelas_SK ENUM('X','XI','XII') NOT NULL DEFAULT 'XII' AFTER Kode_SK;

-- Step 2: Populate Kelas_SK from siswa.Tingkat for all existing records
UPDATE nilai n
JOIN siswa s ON n.NISN = s.NISN
SET n.Kelas_SK = s.Tingkat;

-- Step 3: Drop old FK constraint (only references Kode_SK)
ALTER TABLE nilai DROP FOREIGN KEY fk_nilai_sk;

-- Step 4: Add composite FK constraint (Kode_SK, Kelas_SK)
ALTER TABLE nilai 
ADD CONSTRAINT fk_nilai_sk_composite 
FOREIGN KEY (Kode_SK, Kelas_SK) 
REFERENCES standar_kompetensi(Kode_SK, Kelas_SK)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- Step 5: Verify the changes
DESCRIBE nilai;

SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'db_raport' 
AND TABLE_NAME = 'nilai'
AND REFERENCED_TABLE_NAME = 'standar_kompetensi';

-- Step 6: Verify SK-BD1 data now correctly filtered
SELECT n.Kode_SK, n.Kelas_SK, n.NISN, s.Nama_siswa, s.Tingkat, sk.Nama_SK
FROM nilai n
JOIN siswa s ON n.NISN = s.NISN
JOIN standar_kompetensi sk ON n.Kode_SK = sk.Kode_SK AND n.Kelas_SK = sk.Kelas_SK
WHERE n.Kode_SK = 'SK-BD1'
ORDER BY n.Kelas_SK, s.Nama_siswa;
