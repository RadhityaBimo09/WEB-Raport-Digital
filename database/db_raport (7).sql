-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 04:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_raport`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$6ajIk9Mpm8WPAOXynMrPU.WUD20EGUR.YAKneysuts5UlvK4a8rC6');

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `Kode_guru` varchar(50) NOT NULL,
  `Kode_KK` varchar(50) NOT NULL,
  `Nama_guru` varchar(100) NOT NULL,
  `NIP` varchar(30) DEFAULT NULL,
  `Alamat_guru` varchar(255) DEFAULT NULL,
  `Telp_guru` varchar(20) DEFAULT NULL,
  `Email_guru` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`Kode_guru`, `Kode_KK`, `Nama_guru`, `NIP`, `Alamat_guru`, `Telp_guru`, `Email_guru`, `Password`) VALUES
('G-01', 'KK-RPL', 'Budi Santoso, S.Kom', '198501012010011002', 'Jl. Merdeka No. 12, Tangerang Selatan', '081234567890', 'budi.santoso@gmail.com', '$2y$10$tmZHDyIhrPeP4SHeyXJVBOEH1n0YHHeDSmwiMCm4wIggN7vEu.7VC'),
('G-02', 'KK-DKV', 'Sagita Nuraiq, S.Pd', '199003152015032005', 'Jl. Pahlawan No. 8, Tangerang', '081298765432', 'siti.aminah@smkbi.sch.id', '$2y$10$TCevLvoSEWBmVDsXNhXTkeuwRZtOn4alyLM3FQmMu4nwek3W3TE3q'),
('G-03', 'KK-RPL', 'Hasan Zulkifli, S.Pd. Kom', '16898989898989', '', '', 'hasanm$@gmail.com', '$2y$10$ov0ApAHHFdvOzeysa/7UaulmiNNJgTWJ3yHXQEDCi6YrFQvO10HyW'),
('G-04', 'KK-ANM', 'Santosa Wijayanto, M.Pd. Kom', '16898989898987', '', '', 'santosawijayanto@gmail.com', '$2y$10$vpdicIfWaGcTQLGBAKzWAOa2mjlmIhry9pYHlMUbXm50jul4iUA5K');

-- --------------------------------------------------------

--
-- Table structure for table `kompetensi_keahlian`
--

CREATE TABLE `kompetensi_keahlian` (
  `Kode_KK` varchar(50) NOT NULL,
  `Nama_kompetensi_keahlian` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kompetensi_keahlian`
--

INSERT INTO `kompetensi_keahlian` (`Kode_KK`, `Nama_kompetensi_keahlian`) VALUES
('KK-ANM', 'Animasi'),
('KK-BC', 'Broadcasting'),
('KK-DKV', 'Desain Komunikasi Visual'),
('KK-GIM', 'Pengembangan Game'),
('KK-RPL', 'Rekayasa Perangkat Lunak'),
('KK-TKJ', 'Teknik Komputer dan Jaringan');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `Kode_mata_pelajaran` varchar(50) NOT NULL,
  `Kode_KK` varchar(50) NOT NULL,
  `Nama_mata_pelajaran` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`Kode_mata_pelajaran`, `Kode_KK`, `Nama_mata_pelajaran`) VALUES
('BD', 'KK-RPL', 'Basis Data 2'),
('ORDAS', 'KK-RPL', 'Orientasi Dasar Software'),
('PBTG', 'KK-RPL', 'Pemrograman Berbasis Teks & Grafis 1'),
('PEMWEB', 'KK-RPL', 'Pemrograman Web & Perangkat Bergerak'),
('TLPDF', 'KK-BC', 'Tata Letak Produk Dan Film'),
('TV', 'KK-DKV', 'Tipografi Visual');

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id_nilai` int(11) NOT NULL,
  `NISN` varchar(20) NOT NULL,
  `Kode_guru` varchar(50) NOT NULL,
  `Kode_SK` varchar(50) NOT NULL,
  `Nilai_angka` float DEFAULT NULL,
  `Nilai_huruf` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai`
--

INSERT INTO `nilai` (`id_nilai`, `NISN`, `Kode_guru`, `Kode_SK`, `Nilai_angka`, `Nilai_huruf`) VALUES
(85, '24180001', 'G-01', 'SK-BD1', 50, 'D'),
(86, '24180002', 'G-01', 'SK-BD1', 90, 'A'),
(87, '24180003', 'G-01', 'SK-BD1', 90, 'A'),
(88, '24180004', 'G-01', 'SK-BD1', 90, 'A'),
(89, '24180001', 'G-01', 'SK-BD3', 80, 'B'),
(90, '24180002', 'G-01', 'SK-BD3', 80, 'B'),
(91, '24180003', 'G-01', 'SK-BD3', 80, 'B'),
(92, '24180004', 'G-01', 'SK-BD3', 80, 'B'),
(93, '24180006', 'G-01', 'SK-BD3', 80, 'B'),
(94, '24180005', 'G-01', 'SK-BD3', 80, 'B'),
(95, '24180007', 'G-01', 'SK-BD3', 80, 'B'),
(96, '24180008', 'G-01', 'SK-BD3', 80, 'B'),
(97, '24180009', 'G-01', 'SK-BD3', 80, 'B'),
(103, '24180006', 'G-01', 'SK-BD1', 90, 'A'),
(104, '24180005', 'G-01', 'SK-BD1', 90, 'A'),
(105, '24180007', 'G-01', 'SK-BD1', 90, 'A'),
(106, '24180008', 'G-01', 'SK-BD1', 90, 'A'),
(107, '24180009', 'G-01', 'SK-BD1', 90, 'A'),
(109, '24180001', 'G-01', 'SK-BD2', 75, 'C'),
(110, '24180002', 'G-01', 'SK-BD2', 75, 'C'),
(111, '24180003', 'G-01', 'SK-BD2', 75, 'C'),
(112, '24180004', 'G-01', 'SK-BD2', 75, 'C'),
(113, '24180006', 'G-01', 'SK-BD2', 75, 'C'),
(114, '24180005', 'G-01', 'SK-BD2', 75, 'C'),
(115, '24180007', 'G-01', 'SK-BD2', 75, 'C'),
(116, '24180008', 'G-01', 'SK-BD2', 75, 'C'),
(117, '24180009', 'G-01', 'SK-BD2', 75, 'C'),
(119, '24180017', 'G-02', 'SK-TV1', 90, 'A'),
(120, '24180001', 'G-01', 'SK-PBTG1', 80, 'B'),
(121, '24180002', 'G-01', 'SK-PBTG1', 80, 'B'),
(122, '24180003', 'G-01', 'SK-PBTG1', 80, 'B'),
(123, '24180004', 'G-01', 'SK-PBTG1', 80, 'B'),
(124, '24180006', 'G-01', 'SK-PBTG1', 80, 'B'),
(125, '24180005', 'G-01', 'SK-PBTG1', 80, 'B'),
(126, '24180007', 'G-01', 'SK-PBTG1', 80, 'B'),
(127, '24180008', 'G-01', 'SK-PBTG1', 80, 'B'),
(128, '24180009', 'G-01', 'SK-PBTG1', 80, 'B'),
(129, '24180001', 'G-01', 'SK-PW1', 80, 'B'),
(130, '24180002', 'G-01', 'SK-PW1', 96, 'A'),
(131, '24180003', 'G-01', 'SK-PW1', 78, 'B'),
(132, '24180004', 'G-01', 'SK-PW1', 78, 'B'),
(133, '24180006', 'G-01', 'SK-PW1', 78, 'B'),
(134, '24180005', 'G-01', 'SK-PW1', 78, 'B'),
(135, '24180007', 'G-01', 'SK-PW1', 78, 'B'),
(136, '24180008', 'G-01', 'SK-PW1', 78, 'B'),
(137, '24180009', 'G-01', 'SK-PW1', 78, 'B'),
(138, '24180001', 'G-01', 'SK-PW2', 78, 'B'),
(139, '24180002', 'G-01', 'SK-PW2', 78, 'B'),
(140, '24180003', 'G-01', 'SK-PW2', 78, 'B'),
(141, '24180004', 'G-01', 'SK-PW2', 89, 'A'),
(142, '24180006', 'G-01', 'SK-PW2', 89, 'A'),
(143, '24180005', 'G-01', 'SK-PW2', 89, 'A'),
(144, '24180007', 'G-01', 'SK-PW2', 89, 'A'),
(145, '24180008', 'G-01', 'SK-PW2', 89, 'A'),
(146, '24180009', 'G-01', 'SK-PW2', 89, 'A'),
(147, '24180001', 'G-01', 'SK-PW3', 89, 'A'),
(148, '24180002', 'G-01', 'SK-PW3', 89, 'A'),
(149, '24180003', 'G-01', 'SK-PW3', 89, 'A'),
(150, '24180004', 'G-01', 'SK-PW3', 86, 'B'),
(151, '24180006', 'G-01', 'SK-PW3', 86, 'B'),
(152, '24180005', 'G-01', 'SK-PW3', 86, 'B'),
(153, '24180007', 'G-01', 'SK-PW3', 86, 'B'),
(154, '24180008', 'G-01', 'SK-PW3', 86, 'B'),
(155, '24180009', 'G-01', 'SK-PW3', 86, 'B'),
(156, '24180001', 'G-01', 'SK-PW4', 82, 'B'),
(157, '24180002', 'G-01', 'SK-PW4', 82, 'B'),
(158, '24180003', 'G-01', 'SK-PW4', 82, 'B'),
(159, '24180004', 'G-01', 'SK-PW4', 82, 'B'),
(160, '24180006', 'G-01', 'SK-PW4', 96, 'A'),
(161, '24180005', 'G-01', 'SK-PW4', 96, 'A'),
(162, '24180007', 'G-01', 'SK-PW4', 96, 'A'),
(163, '24180008', 'G-01', 'SK-PW4', 96, 'A'),
(164, '24180009', 'G-01', 'SK-PW4', 96, 'A');

-- --------------------------------------------------------

--
-- Table structure for table `pengajaran`
--

CREATE TABLE `pengajaran` (
  `id_pengajaran` int(11) NOT NULL,
  `Kode_guru` varchar(50) NOT NULL,
  `Kode_KK` varchar(50) NOT NULL,
  `Kode_mata_pelajaran` varchar(50) NOT NULL,
  `Tingkat` enum('X','XI','XII') NOT NULL DEFAULT 'XII',
  `Tahun_ajaran` varchar(20) DEFAULT NULL,
  `Semester` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajaran`
--

INSERT INTO `pengajaran` (`id_pengajaran`, `Kode_guru`, `Kode_KK`, `Kode_mata_pelajaran`, `Tingkat`, `Tahun_ajaran`, `Semester`) VALUES
(2, 'G-01', 'KK-RPL', 'BD', 'XII', '2026/2027', 'Ganjil'),
(15, 'G-01', 'KK-RPL', 'ORDAS', 'X', '2026/2027', 'Ganjil'),
(5, 'G-01', 'KK-RPL', 'PBTG', 'XII', '2026/2027', 'Ganjil'),
(1, 'G-01', 'KK-RPL', 'PEMWEB', 'XII', '2026/2027', 'Ganjil'),
(3, 'G-02', 'KK-DKV', 'TV', 'XII', '2026/2027', 'Ganjil'),
(14, 'G-03', 'KK-RPL', 'PBTG', 'XII', '2026/2027', 'Ganjil');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `NISN` varchar(20) NOT NULL,
  `Kode_wali` varchar(50) DEFAULT NULL,
  `Kode_KK` varchar(50) NOT NULL,
  `Tingkat` enum('X','XI','XII') NOT NULL DEFAULT 'XII',
  `Jurusan` enum('DKV','ANM','BC','RPL','TKJ','GIM') NOT NULL DEFAULT 'RPL',
  `Nama_siswa` varchar(100) NOT NULL,
  `Alamat_siswa` varchar(255) DEFAULT NULL,
  `Tgl_lahir` varchar(50) DEFAULT NULL,
  `Password` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`NISN`, `Kode_wali`, `Kode_KK`, `Tingkat`, `Jurusan`, `Nama_siswa`, `Alamat_siswa`, `Tgl_lahir`, `Password`) VALUES
('24180001', 'W-001', 'KK-RPL', 'XII', 'RPL', 'Abdul Ghaniyy Albar', 'Jl. Contoh No. 1', '2008-01-01', '$2y$10$nbcIcSH/H1Zc5ozkQB5QXedyGCZKOspl/gnHtbyd7ID9lCfF4XbqS'),
('24180002', 'W-002', 'KK-RPL', 'XII', 'RPL', 'Adam Mahardika Bachtiar', 'Jl. Contoh No. 2', '2008-01-01', 'siswa123'),
('24180003', 'W-003', 'KK-RPL', 'XII', 'RPL', 'Ahmed Saka Kammadeva', 'Jl. Contoh No. 3', '2008-01-01', 'siswa123'),
('24180004', 'W-004', 'KK-RPL', 'XII', 'RPL', 'Alvinno Ghany Prayuda', 'Jl. Contoh No. 4', '2008-01-01', 'siswa123'),
('24180005', 'W-005', 'KK-RPL', 'XII', 'RPL', 'Dizly Aprilia Novantria', 'Jl. Contoh No. 5', '2008-01-01', 'siswa123'),
('24180006', 'W-006', 'KK-RPL', 'XII', 'RPL', 'Dinnar Putri Maulana', 'Jl. Contoh No. 6', '2008-01-01', 'siswa123'),
('24180007', 'W-007', 'KK-RPL', 'XII', 'RPL', 'Dzaky Irtiza Mumtaz', 'Jl. Contoh No. 7', '2008-01-01', 'siswa123'),
('24180008', 'W-008', 'KK-RPL', 'XII', 'RPL', 'Muhammad Daffa Atalah', 'Jl. Contoh No. 8', '2008-01-01', 'siswa123'),
('24180009', 'W-009', 'KK-RPL', 'XII', 'RPL', 'Muhammad Ferdiansah', 'Jl. Contoh No. 9', '2008-01-01', 'siswa123'),
('24180017', 'W-010', 'KK-DKV', 'XII', 'DKV', 'Muhammad Ibadurrahman', 'Jl. Contoh No. 10', '2008-01-01', '$2y$10$3zlVjal4QFSsdwCz/WUkE.gZp.jgKhwJsZ9D0JbxSFj52c24n1n8.'),
('24180101', NULL, 'KK-ANM', 'XII', 'ANM', 'Andi Saputra', 'Jl. Merdeka No. 101', '2008-02-01', 'siswa123'),
('24180102', NULL, 'KK-ANM', 'XII', 'ANM', 'Budi Hartono', 'Jl. Merdeka No. 102', '2008-02-02', 'siswa123'),
('24180103', NULL, 'KK-ANM', 'XII', 'ANM', 'Citra Lestari', 'Jl. Merdeka No. 103', '2008-02-03', 'siswa123'),
('24180104', NULL, 'KK-ANM', 'XII', 'ANM', 'Dian Pratama', 'Jl. Merdeka No. 104', '2008-02-04', 'siswa123'),
('24180105', NULL, 'KK-ANM', 'XII', 'ANM', 'Eka Putra', 'Jl. Merdeka No. 105', '2008-02-05', 'siswa123'),
('24180201', NULL, 'KK-BC', 'XII', 'BC', 'Kevin Sanjaya', 'Jl. Pahlawan No. 201', '2008-03-01', 'siswa123'),
('24180202', NULL, 'KK-BC', 'XII', 'BC', 'Lintang Kejora', 'Jl. Pahlawan No. 202', '2008-03-02', 'siswa123'),
('24180203', NULL, 'KK-BC', 'XII', 'BC', 'Muhammad Rizky', 'Jl. Pahlawan No. 203', '2008-03-03', 'siswa123'),
('24180204', NULL, 'KK-BC', 'XII', 'BC', 'Nanda Amelia', 'Jl. Pahlawan No. 204', '2008-03-04', 'siswa123'),
('24180205', NULL, 'KK-BC', 'XII', 'BC', 'Oki Setiawan', 'Jl. Pahlawan No. 205', '2008-03-05', 'siswa123'),
('24180301', NULL, 'KK-DKV', 'XII', 'DKV', 'Ujang Suryana', 'Jl. Kenanga No. 301', '2008-04-01', 'siswa123'),
('24180302', NULL, 'KK-DKV', 'XII', 'DKV', 'Vina Melati', 'Jl. Kenanga No. 302', '2008-04-02', 'siswa123'),
('24180303', NULL, 'KK-DKV', 'XII', 'DKV', 'Wawan Hermawan', 'Jl. Kenanga No. 303', '2008-04-03', 'siswa123'),
('24180304', NULL, 'KK-DKV', 'XII', 'DKV', 'Xena Wijaya', 'Jl. Kenanga No. 304', '2008-04-04', 'siswa123'),
('24180305', NULL, 'KK-DKV', 'XII', 'DKV', 'Yudi Herlambang', 'Jl. Kenanga No. 305', '2008-04-05', 'siswa123'),
('24180401', NULL, 'KK-GIM', 'XII', 'GIM', 'Eka Kurniawan', 'Jl. Melati No. 401', '2008-05-01', 'siswa123'),
('24180402', NULL, 'KK-GIM', 'XII', 'GIM', 'Feri Gunawan', 'Jl. Melati No. 402', '2008-05-02', 'siswa123'),
('24180403', NULL, 'KK-GIM', 'XII', 'GIM', 'Gilang Pratama', 'Jl. Melati No. 403', '2008-05-03', 'siswa123'),
('24180404', NULL, 'KK-GIM', 'XII', 'GIM', 'Hesti Rahayu', 'Jl. Melati No. 404', '2008-05-04', 'siswa123'),
('24180405', NULL, 'KK-GIM', 'XII', 'GIM', 'Irfan Maulana', 'Jl. Melati No. 405', '2008-05-05', 'siswa123'),
('24180501', NULL, 'KK-TKJ', 'XII', 'TKJ', 'Omar Hasyim', 'Jl. Mawar No. 501', '2008-06-01', 'siswa123'),
('24180502', NULL, 'KK-TKJ', 'XII', 'TKJ', 'Prilly Ananda', 'Jl. Mawar No. 502', '2008-06-02', 'siswa123'),
('24180503', NULL, 'KK-TKJ', 'XII', 'TKJ', 'Qori Firmansyah', 'Jl. Mawar No. 503', '2008-06-03', 'siswa123'),
('24180504', NULL, 'KK-TKJ', 'XII', 'TKJ', 'Raffi Ramadhan', 'Jl. Mawar No. 504', '2008-06-04', 'siswa123'),
('24180505', NULL, 'KK-TKJ', 'XII', 'TKJ', 'Siti Syahrani', 'Jl. Mawar No. 505', '2008-06-05', 'siswa123'),
('24181101', NULL, 'KK-ANM', 'XI', 'ANM', 'Bagus Prasetyo', 'Jl. Sudirman No. 11', '2009-01-10', 'siswa123'),
('24181102', NULL, 'KK-ANM', 'XI', 'ANM', 'Cahaya Putri', 'Jl. Sudirman No. 12', '2009-01-11', 'siswa123'),
('24181103', NULL, 'KK-ANM', 'XI', 'ANM', 'Dika Alfian', 'Jl. Sudirman No. 13', '2009-01-12', 'siswa123'),
('24181104', NULL, 'KK-ANM', 'XI', 'ANM', 'Elisa Novita', 'Jl. Sudirman No. 14', '2009-01-13', 'siswa123'),
('24181105', NULL, 'KK-ANM', 'XI', 'ANM', 'Fadli Rahman', 'Jl. Sudirman No. 15', '2009-01-14', 'siswa123'),
('24181201', NULL, 'KK-BC', 'XI', 'BC', 'Gatot Subroto', 'Jl. Thamrin No. 21', '2009-02-10', 'siswa123'),
('24181202', NULL, 'KK-BC', 'XI', 'BC', 'Hana Saraswati', 'Jl. Thamrin No. 22', '2009-02-11', 'siswa123'),
('24181203', NULL, 'KK-BC', 'XI', 'BC', 'Indra Maulana', 'Jl. Thamrin No. 23', '2009-02-12', 'siswa123'),
('24181204', NULL, 'KK-BC', 'XI', 'BC', 'Jihan Fahira', 'Jl. Thamrin No. 24', '2009-02-13', 'siswa123'),
('24181205', NULL, 'KK-BC', 'XI', 'BC', 'Kurniawan Dwi', 'Jl. Thamrin No. 25', '2009-02-14', 'siswa123'),
('24181301', NULL, 'KK-DKV', 'XI', 'DKV', 'Larasati Ayu', 'Jl. Diponegoro No. 31', '2009-03-10', 'siswa123'),
('24181302', NULL, 'KK-DKV', 'XI', 'DKV', 'Muhammad Ilham', 'Jl. Diponegoro No. 32', '2009-03-11', 'siswa123'),
('24181303', NULL, 'KK-DKV', 'XI', 'DKV', 'Nadia Safitri', 'Jl. Diponegoro No. 33', '2009-03-12', 'siswa123'),
('24181304', NULL, 'KK-DKV', 'XI', 'DKV', 'Oka Antara', 'Jl. Diponegoro No. 34', '2009-03-13', 'siswa123'),
('24181305', NULL, 'KK-DKV', 'XI', 'DKV', 'Putra Pratama', 'Jl. Diponegoro No. 35', '2009-03-14', 'siswa123'),
('24181401', NULL, 'KK-GIM', 'XI', 'GIM', 'Qanita Aulia', 'Jl. Gatot Subroto No. 41', '2009-04-10', 'siswa123'),
('24181402', NULL, 'KK-GIM', 'XI', 'GIM', 'Reza Rahardian', 'Jl. Gatot Subroto No. 42', '2009-04-11', 'siswa123'),
('24181403', NULL, 'KK-GIM', 'XI', 'GIM', 'Salsabila Putri', 'Jl. Gatot Subroto No. 43', '2009-04-12', 'siswa123'),
('24181404', NULL, 'KK-GIM', 'XI', 'GIM', 'Taufik Hidayat', 'Jl. Gatot Subroto No. 44', '2009-04-13', 'siswa123'),
('24181405', NULL, 'KK-GIM', 'XI', 'GIM', 'Utami Dewi', 'Jl. Gatot Subroto No. 45', '2009-04-14', 'siswa123'),
('24181501', NULL, 'KK-TKJ', 'XI', 'TKJ', 'Vino Bastian', 'Jl. Asia Afrika No. 51', '2009-05-10', 'siswa123'),
('24181502', NULL, 'KK-TKJ', 'XI', 'TKJ', 'Wulan Guritno', 'Jl. Asia Afrika No. 52', '2009-05-11', 'siswa123'),
('24181503', NULL, 'KK-TKJ', 'XI', 'TKJ', 'Yoga Syahputra', 'Jl. Asia Afrika No. 53', '2009-05-12', 'siswa123'),
('24181504', NULL, 'KK-TKJ', 'XI', 'TKJ', 'Zahra Larasati', 'Jl. Asia Afrika No. 54', '2009-05-13', 'siswa123'),
('24181505', NULL, 'KK-TKJ', 'XI', 'TKJ', 'Ahmad Dani', 'Jl. Asia Afrika No. 55', '2009-05-14', 'siswa123'),
('24181601', NULL, 'KK-RPL', 'XI', 'RPL', 'Bunga Citra', 'Jl. Veteran No. 61', '2009-06-10', 'siswa123'),
('24181602', NULL, 'KK-RPL', 'XI', 'RPL', 'Coki Pardede', 'Jl. Veteran No. 62', '2009-06-11', 'siswa123'),
('24181603', NULL, 'KK-RPL', 'XI', 'RPL', 'Dede Sunandar', 'Jl. Veteran No. 63', '2009-06-12', 'siswa123'),
('24181604', NULL, 'KK-RPL', 'XI', 'RPL', 'Euis Darliah', 'Jl. Veteran No. 64', '2009-06-13', 'siswa123'),
('24181605', NULL, 'KK-RPL', 'XI', 'RPL', 'Farhan Ramadhan', 'Jl. Veteran No. 65', '2009-06-14', 'siswa123'),
('24182101', NULL, 'KK-ANM', 'X', 'ANM', 'Galang Setiawan', 'Jl. Anggrek No. 1', '2011-01-15', 'siswa123'),
('24182102', NULL, 'KK-ANM', 'X', 'ANM', 'Hesti Wulandari', 'Jl. Anggrek No. 2', '2011-01-16', 'siswa123'),
('24182103', NULL, 'KK-ANM', 'X', 'ANM', 'Iqbal Ramadhan', 'Jl. Anggrek No. 3', '2011-01-17', 'siswa123'),
('24182104', NULL, 'KK-ANM', 'X', 'ANM', 'Jihan Kirana', 'Jl. Anggrek No. 4', '2011-01-18', 'siswa123'),
('24182105', NULL, 'KK-ANM', 'X', 'ANM', 'Kevin Sanjaya', 'Jl. Anggrek No. 5', '2011-01-19', 'siswa123'),
('24182201', NULL, 'KK-BC', 'X', 'BC', 'Lutfi Hakim', 'Jl. Dahlia No. 11', '2011-02-15', 'siswa123'),
('24182202', NULL, 'KK-BC', 'X', 'BC', 'Maudy Ayunda', 'Jl. Dahlia No. 12', '2011-02-16', 'siswa123'),
('24182203', NULL, 'KK-BC', 'X', 'BC', 'Nabil Fikri', 'Jl. Dahlia No. 13', '2011-02-17', 'siswa123'),
('24182204', NULL, 'KK-BC', 'X', 'BC', 'Oktavia Ramadhani', 'Jl. Dahlia No. 14', '2011-02-18', 'siswa123'),
('24182205', NULL, 'KK-BC', 'X', 'BC', 'Panji Pragiwaksono', 'Jl. Dahlia No. 15', '2011-02-19', 'siswa123'),
('24182301', NULL, 'KK-DKV', 'X', 'DKV', 'Qory Sandioriva', 'Jl. Cempaka No. 21', '2011-03-15', 'siswa123'),
('24182302', NULL, 'KK-DKV', 'X', 'DKV', 'Rizky Febian', 'Jl. Cempaka No. 22', '2011-03-16', 'siswa123'),
('24182303', NULL, 'KK-DKV', 'X', 'DKV', 'Siti Badriah', 'Jl. Cempaka No. 23', '2011-03-17', 'siswa123'),
('24182304', NULL, 'KK-DKV', 'X', 'DKV', 'Tulus Riyadi', 'Jl. Cempaka No. 24', '2011-03-18', 'siswa123'),
('24182305', NULL, 'KK-DKV', 'X', 'DKV', 'Umay Shahab', 'Jl. Cempaka No. 25', '2011-03-19', 'siswa123'),
('24182401', NULL, 'KK-GIM', 'X', 'GIM', 'Vidi Aldiano', 'Jl. Teratai No. 31', '2011-04-15', 'siswa123'),
('24182402', NULL, 'KK-GIM', 'X', 'GIM', 'Wika Salim', 'Jl. Teratai No. 32', '2011-04-16', 'siswa123'),
('24182403', NULL, 'KK-GIM', 'X', 'GIM', 'Xavier Yusuf', 'Jl. Teratai No. 33', '2011-04-17', 'siswa123'),
('24182404', NULL, 'KK-GIM', 'X', 'GIM', 'Yura Yunita', 'Jl. Teratai No. 34', '2011-04-18', 'siswa123'),
('24182405', NULL, 'KK-GIM', 'X', 'GIM', 'Ziva Magnolya', 'Jl. Teratai No. 35', '2011-04-19', 'siswa123'),
('24182501', NULL, 'KK-TKJ', 'X', 'TKJ', 'Ahmad Albar', 'Jl. Kamboja No. 41', '2011-05-15', 'siswa123'),
('24182502', NULL, 'KK-TKJ', 'X', 'TKJ', 'Bunga Jelitha', 'Jl. Kamboja No. 42', '2011-05-16', 'siswa123'),
('24182503', NULL, 'KK-TKJ', 'X', 'TKJ', 'Chico Jericho', 'Jl. Kamboja No. 43', '2011-05-17', 'siswa123'),
('24182504', NULL, 'KK-TKJ', 'X', 'TKJ', 'Dian Sastrowardoyo', 'Jl. Kamboja No. 44', '2011-05-18', 'siswa123'),
('24182505', NULL, 'KK-TKJ', 'X', 'TKJ', 'Ernest Prakasa', 'Jl. Kamboja No. 45', '2011-05-19', 'siswa123'),
('24182601', NULL, 'KK-RPL', 'X', 'RPL', 'Fiersa Besari', 'Jl. Flamboyan No. 51', '2011-06-15', 'siswa123'),
('24182602', NULL, 'KK-RPL', 'X', 'RPL', 'Gisella Anastasia', 'Jl. Flamboyan No. 52', '2011-06-16', 'siswa123'),
('24182603', NULL, 'KK-RPL', 'X', 'RPL', 'Hardi Fadhillah', 'Jl. Flamboyan No. 53', '2011-06-17', 'siswa123'),
('24182604', NULL, 'KK-RPL', 'X', 'RPL', 'Isyana Sarasvati', 'Jl. Flamboyan No. 54', '2011-06-18', 'siswa123'),
('24182605', NULL, 'KK-RPL', 'X', 'RPL', 'Jefri Nichol', 'Jl. Flamboyan No. 55', '2011-06-19', 'siswa123'),
('24183050', 'W-011', 'KK-RPL', 'X', 'RPL', 'Bimo Suhadi', 'Jl sawah lama', NULL, '$2y$10$df8vvXwUTEk6pdoNesGRHeY4neDRkfFiX.scsbvxilS5cRyNaY5qe');

-- --------------------------------------------------------

--
-- Table structure for table `standar_kompetensi`
--

CREATE TABLE `standar_kompetensi` (
  `Kode_SK` varchar(50) NOT NULL,
  `Kode_KK` varchar(50) NOT NULL,
  `Kode_mata_pelajaran` varchar(50) DEFAULT NULL,
  `Nama_SK` varchar(100) NOT NULL,
  `Kelas_SK` enum('X','XI','XII') NOT NULL DEFAULT 'XII',
  `Nilai_kkm` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `standar_kompetensi`
--

INSERT INTO `standar_kompetensi` (`Kode_SK`, `Kode_KK`, `Kode_mata_pelajaran`, `Nama_SK`, `Kelas_SK`, `Nilai_kkm`) VALUES
('SK-BD1', 'KK-RPL', 'BD', 'SK 1: Memahami Fundamental Akhir MYSQL', 'XI', 78),
('SK-BD1', 'KK-RPL', 'BD', 'SK 1: Konsep Normalisasi Data & ERD', 'XII', 75),
('SK-BD2', 'KK-RPL', 'BD', 'SK 2: DDL & DML SQL Statement', 'XII', 75),
('SK-BD3', 'KK-RPL', 'BD', 'SK 3: Stored Procedure & Trigger', 'XII', 75),
('SK-OD1', 'KK-RPL', 'ORDAS', 'SK 1: Memahami Desain CSS', 'X', 78),
('SK-OD1', 'KK-RPL', 'ORDAS', 'SK 1: Deep Learning Figma', 'XII', 78),
('SK-OD2', 'KK-RPL', 'ORDAS', 'SK 2: Figma dan Adobe Illustrator\'', 'X', 78),
('SK-PBTG1', 'KK-RPL', 'PBTG', 'SK 1 : Memahami Fondasi OOP', 'XII', 78),
('SK-PW1', 'KK-RPL', 'PEMWEB', 'SK 1: Memahami Dasar-Dasar Pemrograman Web (HTML/CSS)', 'XII', 75),
('SK-PW2', 'KK-RPL', 'PEMWEB', 'SK 2: Menerapkan Scripting Server-Side dengan PHP', 'XII', 75),
('SK-PW3', 'KK-RPL', 'PEMWEB', 'SK 3: Pengelolaan Database MySQL & Query SQL', 'XII', 75),
('SK-PW4', 'KK-RPL', 'PEMWEB', 'SK 4: Integrasi Form Backend & Authentication Session', 'XII', 75),
('SK-TLPDF', 'KK-BC', 'TLPDF', 'SK 1: Memahami Dasar Layout Kamera', 'XII', 78),
('SK-TV1', 'KK-DKV', 'TV', 'SK 1: Dasar Tipografi Visual', 'XII', 78);

-- --------------------------------------------------------

--
-- Table structure for table `wali_murid`
--

CREATE TABLE `wali_murid` (
  `Kode_wali` varchar(50) NOT NULL,
  `Nama_ayah` varchar(100) DEFAULT NULL,
  `Pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `Nama_ibu` varchar(100) DEFAULT NULL,
  `Pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `Alamat_wali` varchar(255) DEFAULT NULL,
  `Telp_wali` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wali_murid`
--

INSERT INTO `wali_murid` (`Kode_wali`, `Nama_ayah`, `Pekerjaan_ayah`, `Nama_ibu`, `Pekerjaan_ibu`, `Alamat_wali`, `Telp_wali`) VALUES
('W-001', 'Rachmat A', 'Wiraswasta', 'Siti Nurhaliza', 'Ibu Rumah Tangga', 'Jl. Contoh No. 1', '081300000001'),
('W-002', 'Hendra Bachtiar', 'PNS', 'Dewi Kartika', 'Guru', 'Jl. Contoh No. 2', '081300000002'),
('W-003', 'Agus Kammadeva', 'Karyawan Swasta', 'Rina Wulandari', 'Wiraswasta', 'Jl. Contoh No. 3', '081300000003'),
('W-004', 'Bambang Prayuda', 'TNI', 'Sri Wahyuni', 'Ibu Rumah Tangga', 'Jl. Contoh No. 4', '081300000004'),
('W-005', 'Dedi Novantria', 'Pedagang', 'Aisyah Putri', 'Karyawan Swasta', 'Jl. Contoh No. 5', '081300000005'),
('W-006', 'Fajar Maulana', 'Karyawan Swasta', 'Nurul Hidayah', 'Dokter', 'Jl. Contoh No. 6', '081300000006'),
('W-007', 'Irfan Mumtaz', 'Dosen', 'Lestari Handayani', 'Ibu Rumah Tangga', 'Jl. Contoh No. 7', '081300000007'),
('W-008', 'Rizky Atalah', 'Polri', 'Mega Suryani', 'Apoteker', 'Jl. Contoh No. 8', '081300000008'),
('W-009', 'Joko Ferdiansah', 'Wiraswasta', 'Yuni Rahayu', 'Guru', 'Jl. Contoh No. 9', '081300000009'),
('W-010', 'Soleh Ibadurrahman', 'PNS', 'Fatimah Azzahra', 'Ibu Rumah Tangga', 'Jl. Contoh No. 10', '081300000010'),
('W-011', 'Alex', 'Polisi', 'Evelene', 'Pegawai', 'Jl sawah lama', '08990229020');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`Kode_guru`),
  ADD UNIQUE KEY `uq_guru_nip` (`NIP`),
  ADD KEY `Kode_KK` (`Kode_KK`);

--
-- Indexes for table `kompetensi_keahlian`
--
ALTER TABLE `kompetensi_keahlian`
  ADD PRIMARY KEY (`Kode_KK`);

--
-- Indexes for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`Kode_mata_pelajaran`),
  ADD KEY `Kode_KK` (`Kode_KK`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id_nilai`),
  ADD UNIQUE KEY `uq_nilai_siswa_sk` (`NISN`,`Kode_SK`),
  ADD KEY `NISN` (`NISN`),
  ADD KEY `Kode_guru` (`Kode_guru`),
  ADD KEY `Kode_SK` (`Kode_SK`);

--
-- Indexes for table `pengajaran`
--
ALTER TABLE `pengajaran`
  ADD PRIMARY KEY (`id_pengajaran`),
  ADD UNIQUE KEY `uq_pengajaran` (`Kode_guru`,`Kode_KK`,`Kode_mata_pelajaran`,`Tingkat`,`Tahun_ajaran`,`Semester`),
  ADD KEY `Kode_guru` (`Kode_guru`),
  ADD KEY `Kode_KK` (`Kode_KK`),
  ADD KEY `Kode_mata_pelajaran` (`Kode_mata_pelajaran`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`NISN`),
  ADD KEY `Kode_KK` (`Kode_KK`),
  ADD KEY `idx_siswa_wali` (`Kode_wali`);

--
-- Indexes for table `standar_kompetensi`
--
ALTER TABLE `standar_kompetensi`
  ADD PRIMARY KEY (`Kode_SK`,`Kelas_SK`),
  ADD KEY `Kode_KK` (`Kode_KK`),
  ADD KEY `Kode_mata_pelajaran` (`Kode_mata_pelajaran`);

--
-- Indexes for table `wali_murid`
--
ALTER TABLE `wali_murid`
  ADD PRIMARY KEY (`Kode_wali`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `pengajaran`
--
ALTER TABLE `pengajaran`
  MODIFY `id_pengajaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_kk` FOREIGN KEY (`Kode_KK`) REFERENCES `kompetensi_keahlian` (`Kode_KK`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD CONSTRAINT `fk_mapel_kk` FOREIGN KEY (`Kode_KK`) REFERENCES `kompetensi_keahlian` (`Kode_KK`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `fk_nilai_guru` FOREIGN KEY (`Kode_guru`) REFERENCES `guru` (`Kode_guru`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_siswa` FOREIGN KEY (`NISN`) REFERENCES `siswa` (`NISN`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_sk` FOREIGN KEY (`Kode_SK`) REFERENCES `standar_kompetensi` (`Kode_SK`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pengajaran`
--
ALTER TABLE `pengajaran`
  ADD CONSTRAINT `fk_pengajaran_guru` FOREIGN KEY (`Kode_guru`) REFERENCES `guru` (`Kode_guru`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengajaran_kk` FOREIGN KEY (`Kode_KK`) REFERENCES `kompetensi_keahlian` (`Kode_KK`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengajaran_mapel` FOREIGN KEY (`Kode_mata_pelajaran`) REFERENCES `mata_pelajaran` (`Kode_mata_pelajaran`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_kk` FOREIGN KEY (`Kode_KK`) REFERENCES `kompetensi_keahlian` (`Kode_KK`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_wali` FOREIGN KEY (`Kode_wali`) REFERENCES `wali_murid` (`Kode_wali`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `standar_kompetensi`
--
ALTER TABLE `standar_kompetensi`
  ADD CONSTRAINT `fk_sk_kk` FOREIGN KEY (`Kode_KK`) REFERENCES `kompetensi_keahlian` (`Kode_KK`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sk_mapel` FOREIGN KEY (`Kode_mata_pelajaran`) REFERENCES `mata_pelajaran` (`Kode_mata_pelajaran`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
