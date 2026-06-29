-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 29, 2026 at 03:14 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `sesi_id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `waktu_absen` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('hadir','izin','alfa') DEFAULT 'hadir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `sesi_id`, `mahasiswa_id`, `waktu_absen`, `status`) VALUES
(4, 13, 8, '2026-05-17 17:03:46', 'hadir'),
(6, 13, 9, '2026-05-17 23:09:20', 'alfa'),
(7, 13, 10, '2026-05-17 23:09:20', 'alfa'),
(8, 13, 11, '2026-05-17 23:09:20', 'alfa'),
(9, 13, 12, '2026-05-17 23:09:20', 'alfa'),
(10, 14, 11, '2026-05-18 15:32:27', 'hadir'),
(11, 15, 11, '2026-05-18 15:33:24', 'hadir'),
(12, 15, 8, '2026-05-18 23:33:35', 'alfa'),
(13, 15, 9, '2026-05-18 23:33:35', 'alfa'),
(14, 15, 10, '2026-05-18 23:33:35', 'alfa'),
(15, 15, 12, '2026-05-18 23:33:35', 'alfa'),
(16, 16, 8, '2026-05-18 23:43:35', 'alfa'),
(17, 16, 9, '2026-05-18 23:43:35', 'alfa'),
(18, 16, 10, '2026-05-18 23:43:35', 'alfa'),
(19, 16, 11, '2026-05-18 23:43:35', 'alfa'),
(20, 16, 12, '2026-05-18 23:43:35', 'alfa'),
(21, 18, 11, '2026-05-18 15:44:45', 'hadir'),
(22, 18, 8, '2026-05-18 23:44:52', 'alfa'),
(23, 18, 9, '2026-05-18 23:44:52', 'alfa'),
(24, 18, 10, '2026-05-18 23:44:52', 'alfa'),
(25, 18, 12, '2026-05-18 23:44:52', 'alfa'),
(26, 19, 8, '2026-05-19 00:48:13', 'alfa'),
(27, 19, 9, '2026-05-19 00:48:13', 'alfa'),
(28, 19, 10, '2026-05-19 00:48:13', 'alfa'),
(29, 19, 11, '2026-05-19 00:48:13', 'alfa'),
(30, 19, 12, '2026-05-19 00:48:13', 'alfa'),
(31, 20, 8, '2026-05-19 00:56:40', 'alfa'),
(32, 20, 9, '2026-05-19 00:56:40', 'alfa'),
(33, 20, 10, '2026-05-19 00:56:40', 'alfa'),
(34, 20, 11, '2026-05-19 00:56:40', 'alfa'),
(35, 20, 12, '2026-05-19 00:56:40', 'alfa'),
(36, 21, 11, '2026-05-18 16:58:46', 'hadir'),
(37, 21, 8, '2026-05-19 00:58:59', 'alfa'),
(38, 21, 9, '2026-05-19 00:58:59', 'alfa'),
(39, 21, 10, '2026-05-19 00:58:59', 'alfa'),
(40, 21, 12, '2026-05-19 00:58:59', 'alfa'),
(41, 22, 9, '2026-05-18 17:02:01', 'hadir'),
(42, 22, 8, '2026-05-19 01:07:54', 'alfa'),
(43, 22, 10, '2026-05-19 01:07:54', 'alfa'),
(44, 22, 11, '2026-05-19 01:07:54', 'alfa'),
(45, 22, 12, '2026-05-19 01:07:54', 'alfa'),
(46, 23, 9, '2026-05-18 17:09:12', 'hadir'),
(47, 23, 11, '2026-05-18 17:10:03', 'hadir'),
(48, 23, 8, '2026-05-19 01:10:22', 'alfa'),
(49, 23, 10, '2026-05-19 01:10:22', 'alfa'),
(50, 23, 12, '2026-05-19 01:10:22', 'alfa'),
(52, 27, 11, '2026-05-20 10:50:43', 'hadir'),
(53, 27, 8, '2026-05-20 18:50:52', 'alfa'),
(54, 27, 9, '2026-05-20 18:50:52', 'alfa'),
(55, 27, 10, '2026-05-20 18:50:52', 'alfa'),
(56, 27, 12, '2026-05-20 18:50:52', 'alfa'),
(57, 28, 11, '2026-05-20 10:51:25', 'hadir'),
(58, 28, 8, '2026-05-20 18:51:31', 'alfa'),
(59, 28, 9, '2026-05-20 18:51:31', 'alfa'),
(60, 28, 10, '2026-05-20 18:51:31', 'alfa'),
(61, 28, 12, '2026-05-20 18:51:31', 'alfa'),
(62, 29, 11, '2026-05-20 11:47:30', 'hadir'),
(63, 29, 8, '2026-05-20 19:47:37', 'alfa'),
(64, 29, 9, '2026-05-20 19:47:37', 'alfa'),
(65, 29, 10, '2026-05-20 19:47:37', 'alfa'),
(66, 29, 12, '2026-05-20 19:47:37', 'alfa'),
(67, 30, 8, '2026-05-21 19:15:27', 'alfa'),
(68, 30, 9, '2026-05-21 19:15:27', 'alfa'),
(69, 30, 10, '2026-05-21 19:15:27', 'alfa'),
(70, 30, 11, '2026-05-21 19:15:27', 'alfa'),
(71, 30, 12, '2026-05-21 19:15:27', 'alfa'),
(72, 31, 8, '2026-05-21 19:19:57', 'alfa'),
(73, 31, 9, '2026-05-21 19:19:57', 'alfa'),
(74, 31, 10, '2026-05-21 19:19:57', 'alfa'),
(75, 31, 11, '2026-05-21 19:19:57', 'alfa'),
(76, 31, 12, '2026-05-21 19:19:57', 'alfa'),
(77, 32, 8, '2026-05-21 19:27:03', 'alfa'),
(78, 32, 9, '2026-05-21 19:27:03', 'alfa'),
(79, 32, 10, '2026-05-21 19:27:03', 'alfa'),
(80, 32, 11, '2026-05-21 19:27:03', 'alfa'),
(81, 32, 12, '2026-05-21 19:27:03', 'alfa'),
(82, 33, 11, '2026-05-21 11:28:47', 'hadir'),
(83, 33, 8, '2026-05-21 19:28:59', 'alfa'),
(84, 33, 9, '2026-05-21 19:28:59', 'alfa'),
(85, 33, 10, '2026-05-21 19:28:59', 'alfa'),
(86, 33, 12, '2026-05-21 19:28:59', 'alfa'),
(87, 34, 11, '2026-05-21 19:32:30', 'hadir'),
(88, 34, 8, '2026-05-21 19:32:37', 'alfa'),
(89, 34, 9, '2026-05-21 19:32:37', 'alfa'),
(90, 34, 10, '2026-05-21 19:32:37', 'alfa'),
(91, 34, 12, '2026-05-21 19:32:37', 'alfa'),
(92, 36, 11, '2026-06-02 14:19:51', 'hadir'),
(93, 36, 8, '2026-06-02 14:19:58', 'hadir'),
(94, 36, 9, '2026-06-02 14:20:01', 'hadir'),
(95, 36, 10, '2026-06-02 14:20:12', 'alfa'),
(96, 36, 12, '2026-06-02 14:20:12', 'alfa'),
(97, 35, 8, '2026-06-02 14:23:01', 'alfa'),
(98, 35, 9, '2026-06-02 14:23:01', 'alfa'),
(99, 35, 10, '2026-06-02 14:23:01', 'alfa'),
(100, 35, 11, '2026-06-02 14:23:01', 'alfa'),
(101, 35, 12, '2026-06-02 14:23:01', 'alfa'),
(102, 37, 11, '2026-06-02 14:23:42', 'hadir'),
(103, 37, 8, '2026-06-02 14:23:43', 'hadir'),
(104, 37, 9, '2026-06-02 14:24:04', 'alfa'),
(105, 37, 10, '2026-06-02 14:24:04', 'alfa'),
(106, 37, 12, '2026-06-02 14:24:04', 'alfa'),
(107, 38, 8, '2026-06-02 14:34:41', 'alfa'),
(108, 38, 9, '2026-06-02 14:34:41', 'alfa'),
(109, 38, 10, '2026-06-02 14:34:41', 'alfa'),
(110, 38, 11, '2026-06-02 14:34:41', 'alfa'),
(111, 38, 12, '2026-06-02 14:34:41', 'alfa');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nidn` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `user_id`, `nidn`, `nama_lengkap`) VALUES
(1, 2, '0001019001', 'I Gusti Agung Gede Arya Kadyanan'),
(2, 11, '009019401404210', 'bu agung');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_kuliah`
--

CREATE TABLE `jadwal_kuliah` (
  `id` int(11) NOT NULL,
  `mata_kuliah_id` int(11) NOT NULL,
  `dosen_id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal_kuliah`
--

INSERT INTO `jadwal_kuliah` (`id`, `mata_kuliah_id`, `dosen_id`, `kelas_id`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(1, 1, 1, 2, 'Senin', '08:00:00', '10:30:00'),
(2, 3, 1, 2, 'Rabu', '13:00:00', '15:30:00'),
(3, 1, 2, 6, 'Rabu', '03:03:00', '03:05:00'),
(4, 2, 1, 4, 'Senin', '02:32:00', '05:06:00'),
(5, 4, 1, 3, 'Senin', '03:01:00', '23:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`) VALUES
(1, 'A'),
(2, 'B'),
(3, 'C'),
(4, 'D'),
(5, 'E'),
(6, 'F');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `angkatan` year(4) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `user_id`, `nim`, `nama_lengkap`, `program_studi`, `angkatan`, `kelas_id`) VALUES
(8, 19, '2408561112', 'Made Krishna Dafa Janata', 'Informatika', '2024', 2),
(9, 20, '2408561087', 'Gomgom Samuel Harapan Eakbasa Lumbantobing', 'Informatika', '2024', 2),
(10, 21, '2408561113', 'Kadek Idul Putra', 'informatika', '2024', 2),
(11, 22, '2408561116', 'Nereus Blessio Reward Valentriatma Sipayung', 'informatika', '2024', 2),
(12, 23, '2408561125', 'Candra Islami Rasya', 'Informatika', '2024', 2);

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa_jadwal`
--

CREATE TABLE `mahasiswa_jadwal` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `jadwal_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` tinyint(4) NOT NULL,
  `semester` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id`, `kode_mk`, `nama_mk`, `sks`, `semester`) VALUES
(1, 'IF2101', 'Sistem Informasi', 3, 4),
(2, 'IF2102', 'Basis Data', 3, 3),
(3, 'IF2103', 'Pemrograman Web', 3, 4),
(5, 'IF2123', 'Probabilitas', 10, 4),
(6, 'IF2104', 'Metode Penelitian', 2, 4),
(7, 'IF2105', 'Analisis dan Desain Sistem', 3, 4),
(8, 'IF2106', 'Pengantar Kecerdasan Buatan', 3, 4),
(9, 'IF2107', 'Pengantar Pemrosesan Data Multimedia', 3, 4),
(10, 'IF2108', 'Keamanan Jaringan', 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sesi_absensi`
--

CREATE TABLE `sesi_absensi` (
  `id` int(11) NOT NULL,
  `jadwal_id` int(11) NOT NULL,
  `pertemuan_ke` varchar(100) NOT NULL,
  `topik` text DEFAULT NULL,
  `tipe_kelas` enum('offline','online') NOT NULL DEFAULT 'offline',
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `session_token` varchar(255) NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_berakhir` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sesi_absensi`
--

INSERT INTO `sesi_absensi` (`id`, `jadwal_id`, `pertemuan_ke`, `topik`, `tipe_kelas`, `latitude`, `longitude`, `session_token`, `waktu_mulai`, `waktu_berakhir`, `is_active`, `created_at`) VALUES
(13, 2, '', NULL, 'offline', NULL, NULL, 'SESI-D82B0182', '2026-05-17 17:02:28', '2026-05-17 23:09:20', 0, '2026-05-17 15:02:28'),
(14, 1, '', NULL, 'offline', NULL, NULL, 'SESI-D2D48D24', '2026-05-18 14:39:25', '2026-05-18 23:32:43', 0, '2026-05-18 14:39:25'),
(15, 2, '', NULL, 'offline', NULL, NULL, 'SESI-4918F41F', '2026-05-18 15:33:22', '2026-05-18 23:33:35', 0, '2026-05-18 15:33:22'),
(16, 2, '', NULL, 'offline', NULL, NULL, 'SESI-8CB993B7', '2026-05-18 15:41:19', '2026-05-18 23:43:35', 0, '2026-05-18 15:41:19'),
(17, 1, '', NULL, 'offline', NULL, NULL, 'SESI-2C08594B', '2026-05-18 15:43:40', '2026-05-18 23:44:27', 0, '2026-05-18 15:43:40'),
(18, 2, '', NULL, 'offline', NULL, NULL, 'SESI-ADA84B21', '2026-05-18 15:44:40', '2026-05-18 23:44:52', 0, '2026-05-18 15:44:40'),
(19, 2, '', NULL, 'offline', NULL, NULL, 'SESI-06792C4D', '2026-05-18 16:36:50', '2026-05-19 00:48:13', 0, '2026-05-18 16:36:50'),
(20, 2, '', NULL, 'offline', NULL, NULL, 'SESI-4E92D8C3', '2026-05-18 16:48:18', '2026-05-19 00:56:40', 0, '2026-05-18 16:48:18'),
(21, 2, '', NULL, 'offline', NULL, NULL, 'SESI-0577A7EA', '2026-05-18 16:56:43', '2026-05-19 00:58:59', 0, '2026-05-18 16:56:43'),
(22, 2, '', NULL, 'offline', NULL, NULL, 'SESI-CAA25C1B', '2026-05-18 16:59:21', '2026-05-19 01:07:54', 0, '2026-05-18 16:59:21'),
(23, 2, '', NULL, 'offline', NULL, NULL, 'SESI-799ED081', '2026-05-18 17:08:04', '2026-05-19 01:10:22', 0, '2026-05-18 17:08:04'),
(27, 2, '', NULL, 'offline', NULL, NULL, 'SESI-CA674E4C', '2026-05-20 10:50:38', '2026-05-20 18:50:52', 0, '2026-05-20 10:50:38'),
(28, 1, '', NULL, 'offline', NULL, NULL, 'SESI-6B454184', '2026-05-20 10:51:19', '2026-05-20 18:51:31', 0, '2026-05-20 10:51:19'),
(29, 1, '6', 'test', 'offline', '-8.799704484729544', '', 'SESI-6BEF8C2E', '2026-05-20 11:47:18', '2026-05-20 19:47:37', 0, '2026-05-20 11:47:18'),
(30, 1, '6,7', 'test', 'offline', '-8.799731960074201', '', 'SESI-8FA3063F', '2026-05-21 11:07:04', '2026-05-21 19:15:27', 0, '2026-05-21 11:07:04'),
(31, 1, '6,7', 'test', 'offline', '-8.799679518537955', '', 'SESI-C8354988', '2026-05-21 19:15:39', '2026-05-21 19:19:57', 0, '2026-05-21 11:15:39'),
(32, 2, '14', 'test', 'offline', '-8.799692752579379', '', 'SESI-794B8A28', '2026-05-21 19:21:40', '2026-05-21 19:27:03', 0, '2026-05-21 11:21:40'),
(33, 1, '15', 'test', 'offline', '-8.799692752579379', '', 'SESI-A3625719', '2026-05-21 19:28:41', '2026-05-21 19:28:59', 0, '2026-05-21 11:28:41'),
(34, 1, '16', 'test', 'offline', '-8.799692752579379', '', 'SESI-58734FAF', '2026-05-21 19:32:26', '2026-05-21 19:32:37', 0, '2026-05-21 11:32:26'),
(35, 1, '11,12', 'test', 'offline', '-8.799161', '', 'SESI-3A401D28', '2026-06-02 14:13:15', '2026-06-02 14:23:01', 0, '2026-06-02 06:13:15'),
(36, 2, '2', 'test', 'offline', '-8.79925503792996', '', 'SESI-7A7F04BA', '2026-06-02 14:19:04', '2026-06-02 14:20:12', 0, '2026-06-02 06:19:04'),
(37, 2, '1', 'tes', 'offline', '-8.799271716790033', '', 'SESI-613D3A15', '2026-06-02 14:23:23', '2026-06-02 14:24:04', 0, '2026-06-02 06:23:23'),
(38, 2, '1', 'tes', 'offline', '-8.799283209189314', '115.16960836253428', 'SESI-CC6678D7', '2026-06-02 14:34:34', '2026-06-02 14:34:41', 0, '2026-06-02 06:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'dosen1', '123', 'dosen', '2026-05-15 11:47:36'),
(6, 'dosendua', '123', 'dosen', '2026-05-15 13:34:59'),
(8, 'admin Dafa', '$2y$10$bW7dAQo1h2OGK/IXspcTV..Jn9tEgpRQ7Jq8JHiMqyIvJlqj6zdo6', 'admin', '2026-05-15 13:53:50'),
(9, 'dafaJanata', '$2y$10$4UTH.vrYgIatQ2RecYi5.OYLmJu7mZW8FpEPw6b/oNZ1L.GAuRxOm', 'mahasiswa', '2026-05-17 11:44:19'),
(10, '2408561115', '2408561115', 'mahasiswa', '2026-05-17 11:54:54'),
(11, '009019401404210', '009019401404210', 'dosen', '2026-05-17 11:56:24'),
(12, '24087774744', '24087774744', 'mahasiswa', '2026-05-17 11:56:36'),
(13, '123213321', '123213321', 'mahasiswa', '2026-05-17 12:06:40'),
(14, '123321321', '123321321', 'mahasiswa', '2026-05-17 12:07:22'),
(19, '2408561112', '$2y$10$m0k8olZU85UDGZDSgLnGKuANwV2F1wgmuxRohZ.UCR1gwsMScHR2O', 'mahasiswa', '2026-05-17 14:59:03'),
(20, '2408561087', '$2y$10$LIVSxLcAYCVQC4VuQx4CQePBg2L0YvNClgx5NytOGpqZyl4hWNYyi', 'mahasiswa', '2026-05-17 14:59:52'),
(21, '2408561113', '$2y$10$GM/EqpZpHL1r9uEG3h97ieBHIcXmzLYP1B/rsSgCOzNkWXOSIa27m', 'mahasiswa', '2026-05-17 15:00:22'),
(22, '2408561116', '$2y$10$ZIMnG7fTd9KjqBRZCFcBheZ0nCyQHyW3Z67IIfuK4LU6nFbfUTMdC', 'mahasiswa', '2026-05-17 15:00:57'),
(23, '2408561125', '$2y$10$P2dajG8t1GvcF3B0CJAOAu9QShQ0k54FIMoYxWOAYMwobUn6B8uki', 'mahasiswa', '2026-05-17 15:01:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sesi_mahasiswa` (`sesi_id`,`mahasiswa_id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `nidn` (`nidn`);

--
-- Indexes for table `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mata_kuliah_id` (`mata_kuliah_id`),
  ADD KEY `dosen_id` (`dosen_id`),
  ADD KEY `fk_jadwal_kelas` (`kelas_id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `fk_mahasiswa_kelas` (`kelas_id`);

--
-- Indexes for table `mahasiswa_jadwal`
--
ALTER TABLE `mahasiswa_jadwal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mahasiswa_jadwal` (`mahasiswa_id`,`jadwal_id`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mk` (`kode_mk`);

--
-- Indexes for table `sesi_absensi`
--
ALTER TABLE `sesi_absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mahasiswa_jadwal`
--
ALTER TABLE `mahasiswa_jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sesi_absensi`
--
ALTER TABLE `sesi_absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`sesi_id`) REFERENCES `sesi_absensi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dosen`
--
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
