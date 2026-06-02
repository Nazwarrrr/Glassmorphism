-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2026 at 04:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_perpus_smea`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` int(11) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `sinopsis` text DEFAULT NULL,
  `cover_buku` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `id_kategori`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `stok`, `sinopsis`, `cover_buku`, `created_at`, `updated_at`) VALUES
(1, 1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 5, 'Kisah inspiratif tentang anak-anak Indonesia yang berjuang mengejar pendidikan di tengah keterbatasan.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(2, 1, 'Pergi', 'Tere Liye', 'Sabak Grip', 2014, 3, 'Novel petualangan yang menginspirasi tentang perjalanan hidup dan mimpi.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(3, 2, 'Apa Itu Pilihan Karir?', 'John Smith', 'Gramedia', 2010, 4, 'Panduan praktis memilih karir yang sesuai dengan passion dan kemampuan.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(4, 3, 'Dasar-Dasar Pemrograman Python', 'Mark Lutz', 'O\'Reilly Media', 2019, 6, 'Buku referensi lengkap untuk mempelajari Python dari dasar hingga advanced.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(5, 3, 'Web Development dengan PHP & MySQL', 'Budi Sutrisno', 'Informatika', 2018, 4, 'Panduan membuat website dinamis menggunakan PHP dan MySQL untuk pemula.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(6, 4, 'Sejarah Indonesia Lengkap', 'Sartono Kartodirdjo', 'Kompas Media', 2008, 2, 'Referensi sejarah Indonesia dari zaman prasejarah hingga era modern.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(7, 5, 'Steve Jobs: Cara Hidup Visioner', 'Walter Isaacson', 'Gramedia Pustaka Utama', 2011, 3, 'Biografi mendalam tentang pendiri Apple dan filosofi inovasinya.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(8, 7, 'Batik: Seni dan Filosofi', 'Edy Susanto', 'Pusaka', 2015, 2, 'Menjelajahi kekayaan warisan batik Indonesia dan maknanya dalam budaya.', 'default.jpg', '2026-06-02 01:22:08', '2026-06-02 01:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `created_at`) VALUES
(1, 'Fiksi', '2026-06-02 01:22:08'),
(2, 'Non-Fiksi', '2026-06-02 01:22:08'),
(3, 'Sains & Teknologi', '2026-06-02 01:22:08'),
(4, 'Sejarah', '2026-06-02 01:22:08'),
(5, 'Biografi', '2026-06-02 01:22:08'),
(6, 'Referensi', '2026-06-02 01:22:08'),
(7, 'Seni & Budaya', '2026-06-02 01:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tgl_booking` datetime DEFAULT current_timestamp(),
  `tgl_pinjam` datetime DEFAULT NULL,
  `tgl_kembali` datetime DEFAULT NULL,
  `tgl_dikembalikan` datetime DEFAULT NULL,
  `denda` int(11) DEFAULT 0,
  `status` enum('Menunggu Konfirmasi','Sedang Dipinjam','Selesai','Ditolak','Terlambat') DEFAULT 'Menunggu Konfirmasi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `role` enum('siswa','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `kelas`, `role`, `created_at`, `updated_at`) VALUES
(1, 'siswa1', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Budi Santoso', 'XII RPL A', 'siswa', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(2, 'siswa2', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Siti Nurhaliza', 'XII TKJ B', 'siswa', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(3, 'siswa3', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Ahmad Wijaya', 'XI AKL C', 'siswa', '2026-06-02 01:22:08', '2026-06-02 01:22:08'),
(4, 'admin1', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Ibu Perpustakaan', NULL, 'admin', '2026-06-02 01:22:08', '2026-06-02 01:22:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `idx_kategori` (`id_kategori`),
  ADD KEY `idx_judul` (`judul`),
  ADD KEY `idx_stok` (`stok`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `idx_user` (`id_user`),
  ADD KEY `idx_buku` (`id_buku`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tgl_kembali` (`tgl_kembali`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
