-- Add missing pengajaran record for Basis Data class XI
-- This allows teacher G-01 to teach BD (Basis Data 2) to class XI RPL

INSERT INTO pengajaran (Kode_guru, Kode_KK, Kode_mata_pelajaran, Tingkat, Tahun_ajaran, Semester)
VALUES ('G-01', 'KK-RPL', 'BD', 'XI', '2026/2027', 'Ganjil');

-- Verify the insert
SELECT p.*, mp.Nama_mata_pelajaran
FROM pengajaran p
JOIN mata_pelajaran mp ON p.Kode_mata_pelajaran = mp.Kode_mata_pelajaran
WHERE p.Kode_mata_pelajaran = 'BD'
ORDER BY p.Tingkat;
