-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2026 at 02:26 AM
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
-- Database: `perpustakaan_smea`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `sinopsis` text DEFAULT NULL,
  `cover_buku` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `id_kategori`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `stok`, `sinopsis`, `cover_buku`, `created_at`) VALUES
(1, 1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang', 2005, 5, 'Mengisahkan perjuangan hidup 10 anak dari keluarga miskin di Pulau Belitung (disebut Laskar Pelangi) yang bersekolah di sebuah sekolah Muhammadiyah yang nyaris roboh. Lewat sudut pandang Ikal, kita akan melihat keajaiban persahabatan, ketulusan Ibu Guru Muslimah, kejeniusan Lintang, serta bagaimana keterbatasan ekonomi tidak mampu mematikan mimpi besar mereka untuk mengubah nasib.', 'buku_1.jpg', '2026-06-02 01:30:20'),
(2, 1, 'Negeri Para Bedebah', 'Tere Liye', 'Gramedia', 2012, 4, 'Sebuah novel thriller finansial dan politik yang berpusat pada tokoh Thomas, seorang konsultan keuangan muda yang sangat cerdas dan sinis. Thomas terjebak dalam pusaran konspirasi besar ketika ia harus menyelamatkan Bank Semesta milik pamannya dari kebangkrutan sistemik dalam waktu 48 jam. Di dunia di mana hukum bisa dibeli, Thomas harus bertarung melawan para \"bedebah\" (penguasa dan pengusaha korup) demi bertahan hidup.', 'buku_2.jpg', '2026-06-02 01:30:20'),
(3, 2, 'Sejarah Indonesia', 'Sartono Kartodirdjo', 'Gramedia', 2008, 3, 'Ditulis oleh begawan sejarah Indonesia, buku ini membedah perjalanan panjang Nusantara tidak sekadar dari sudut pandang kedatangan penjajah, melainkan dari kacamata masyarakat lokal (indonesiasentris). Mulai dari masa kerajaan kuno, jalur perdagangan maritim, masa kolonialisme, hingga pergerakan nasional yang memicu lahirnya kemerdekaan Indonesia.', 'buku_3.jpg', '2026-06-02 01:30:20'),
(4, 4, 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 2, 'Merupakan \"kitab suci\" bagi para software engineer dan programmer. Robert C. Martin membongkar perbedaan antara kode yang sekadar \"jalan\" dengan kode yang \"bersih\" (clean code). Buku ini memberikan panduan praktis tentang cara menulis kode yang efisien, mudah dibaca, mudah dirawat (maintainable), serta bagaimana melakukan refactoring kode yang berantakan.', 'buku_4.jpg', '2026-06-02 01:30:20'),
(10, 1, 'Hujan', 'Tere Liye', 'Gramedia', 2016, 4, 'Mengambil latar masa depan (tahun 2042–2050), dunia sudah dipenuhi teknologi canggih hingga peran manusia banyak digantikan oleh mesin. Kisah berpusat pada Lail, seorang gadis yang kehilangan kedua orang tuanya akibat bencana gunung meletus dan gempa dahsyat. Saat bencana terjadi, ia diselamatkan oleh seorang anak laki-laki jenius bernama Esok. Mereka tumbuh bersama di pengungsian hingga akhirnya terpisah karena keadaan. Cerita ini berfokus pada tema kehilangan, persahabatan, cinta, dan teknologi masa depan, di mana Lail pada akhirnya harus berhadapan dengan sebuah teknologi modifikasi ingatan untuk menghapus luka masa lalunya.', 'buku_10.jpg', '2026-06-02 01:51:40'),
(11, 1, 'Bumi', 'Tere Liye', 'Gramedia', 2014, 2, 'Mengisahkan Raib, seorang remaja perempuan berusia 15 tahun yang tampak biasa tetapi memiliki rahasia besar: ia bisa menghilang hanya dengan menutup wajahnya dengan kedua telapak tangan. Kehidupan normalnya berubah total ketika sesosok makhluk misterius bernama Tamus muncul dari dalam cermin kamarnya. Bersama dua teman sekelasnya—Seli (yang ternyata bisa mengeluarkan petir) dan Ali (si genius yang tahu banyak hal)—Raib terseret ke dalam petualangan melintasi portal menuju Klan Bulan, sebuah dunia paralel yang hidup berdampingan dengan Bumi.', 'buku_11.jpg', '2026-06-02 01:52:27'),
(12, 1, 'Matahari', 'Tere Liye', 'Gramedia', 2016, 1, 'Setelah petualangan menegangkan di Klan Bulan dan Klan Matahari, suasana duka sempat menyelimuti Raib, Seli, dan Ali atas tewasnya sahabat mereka, Ily. Namun, rasa penasaran Ali yang luar biasa membawanya menemukan petunjuk tentang keberadaan klan misterius lainnya, yaitu Klan Bintang, yang posisinya berada jauh di dalam perut bumi. Menggunakan kapsul terbang canggih buatan Ali bernama ILY, ketiga sahabat ini nekat melakukan penjelahan ke bawah tanah tanpa izin. Petualangan ini membawa mereka ke sebuah peradaban yang sangat maju namun menyimpan bahaya besar bagi kestabilan seluruh dunia paralel.', 'buku_12.jpg', '2026-06-02 01:53:44'),
(13, 1, 'Bintang', 'Tere Liye', 'Gramedia', 2017, 3, 'Kelanjutan langsung dari Matahari. Raib, Seli, dan Ali membawa kabar buruk dari klan kutub/bawah tanah: Sekretaris Dewan Kota Zaramaraz di Klan Bintang berencana menghancurkan pasak-pasak bumi untuk meruntuhkan klan permukaan. Bersama pasukan gabungan dari Klan Bulan dan Klan Matahari, mereka mengemban misi berbahaya untuk menemukan dan menyegel pasak bumi tersebut sebelum terlambat. Namun, di tengah pertarungan dan rintangan alam yang ekstrem, sebuah kecerobohan justru membuat mereka tidak sengaja melepaskan \"musuh besar\" yang paling ditakuti di dunia paralel.', 'buku_13.jpg', '2026-06-02 01:54:45'),
(14, 1, 'Ceros dan Bartozar', 'Tere Liye', 'Gramedia', 2018, 4, 'Buku ini merupakan spin-off atau buku sisipan yang memuat dua cerita berbeda.  \r\n\r\nCerita Pertama (Ceros): Mengisahkan Ali yang mendeteksi adanya kekuatan besar saat mereka sedang melakukan karyawisata sekolah ke situs kuno. Mereka menemukan dunia bawah tanah tersembunyi yang dihuni oleh dua makhluk raksasa berkepala badak (Ceros) yang mengamuk saat siang hari namun menjadi manusia biasa saat malam.\r\n\r\nCerita Kedua (Bartozar): Fokus pada pelarian Bartozar, seorang kriminal bertato paling berbahaya sekaligus master pembuat kapal dari Klan Bulan yang kabur dari penjara dan bersembunyi di Bumi. Raib, Seli, dan Ali terpaksa berurusan dengannya demi menyelamatkan guru mereka, Miss Selena.', 'buku_14.jpg', '2026-06-02 01:55:51'),
(15, 1, 'Komet', 'Tere Liye', 'Gramedia', 2018, 6, 'Setelah Si Tanpa Mahkota (musuh utama mereka) berhasil lolos, dunia paralel berada dalam siaga satu. Ali dengan nekat melompat ke sebuah portal kuno, yang membuat Raib dan Seli terpaksa ikut mengejarnya hingga mereka semua terdampar di Klan Komet. Klan ini sangat berbeda karena tidak memiliki teknologi canggih seperti klan lainnya. Di sini, mereka harus mengarungi lautan dan berpindah dari pulau ke pulau untuk menyelesaikan teka-teki demi menemukan tanaman pusaka paling hebat sebelum benda tersebut jatuh ke tangan Si Tanpa Mahkota.', 'buku_15.jpg', '2026-06-02 01:56:35');

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
(1, 'Fiksi', '2026-06-02 01:30:20'),
(2, 'Non-Fiksi', '2026-06-02 01:30:20'),
(3, 'Referensi', '2026-06-02 01:30:20'),
(4, 'Teknologi', '2026-06-02 01:30:20'),
(5, 'Seni & Budaya', '2026-06-02 01:30:20'),
(6, 'Kesehatan', '2026-06-02 01:30:20'),
(7, 'Pendidikan', '2026-06-02 01:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tgl_booking` timestamp NOT NULL DEFAULT current_timestamp(),
  `tgl_pinjam` datetime DEFAULT NULL,
  `tgl_kembali` datetime DEFAULT NULL,
  `tgl_dikembalikan` datetime DEFAULT NULL,
  `status` enum('Menunggu Konfirmasi','Sedang Dipinjam','Selesai','Ditolak') DEFAULT 'Menunggu Konfirmasi',
  `denda` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `role` enum('siswa','admin') DEFAULT 'siswa',
  `kelas` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`, `kelas`, `created_at`) VALUES
(1, 'siswa', '$2y$10$40mKMCQMHi9NOPkRo1kOaezXlzFql9UgcmDPmG2BFVOPCniBJlcAW', 'Siswa Demo', 'siswa', 'XII RPL 1', '2026-06-02 01:30:20'),
(2, 'admin', '$2y$10$EC1wpMsbOBzGCTRJpsnlC.nJjVxJebixalECP6YHby1CPK05S7Edu', 'Admin Perpustakaan', 'admin', NULL, '2026-06-02 01:30:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `idx_kategori` (`id_kategori`),
  ADD KEY `idx_judul` (`judul`);

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
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE;

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
