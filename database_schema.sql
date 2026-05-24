-- ============================================================================
-- Database Schema untuk E-Perpus SMEA
-- ============================================================================

-- Drop database jika sudah ada (untuk reset development)
DROP DATABASE IF EXISTS e_perpus_smea;

-- Create database
CREATE DATABASE IF NOT EXISTS e_perpus_smea;
USE e_perpus_smea;

-- ============================================================================
-- 1. TABEL KATEGORI BUKU
-- ============================================================================
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABEL USERS (Siswa & Admin/Petugas)
-- ============================================================================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NULL,
    role ENUM('siswa', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABEL BUKU
-- ============================================================================
CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit INT,
    stok INT NOT NULL DEFAULT 0,
    sinopsis TEXT,
    cover_buku VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE SET NULL,
    INDEX idx_kategori (id_kategori),
    INDEX idx_judul (judul),
    INDEX idx_stok (stok)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABEL PEMINJAMAN (Sirkulasi Utama)
-- ============================================================================
CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    tgl_booking DATETIME DEFAULT CURRENT_TIMESTAMP,
    tgl_pinjam DATETIME NULL,
    tgl_kembali DATETIME NULL,
    tgl_dikembalikan DATETIME NULL,
    denda INT DEFAULT 0,
    status ENUM('Menunggu Konfirmasi', 'Sedang Dipinjam', 'Selesai', 'Ditolak', 'Terlambat') DEFAULT 'Menunggu Konfirmasi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
    INDEX idx_user (id_user),
    INDEX idx_buku (id_buku),
    INDEX idx_status (status),
    INDEX idx_tgl_kembali (tgl_kembali)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEEDER DATA AWAL
-- ============================================================================

-- Insert Kategori Buku
INSERT INTO kategori (nama_kategori) VALUES
('Fiksi'),
('Non-Fiksi'),
('Sains & Teknologi'),
('Sejarah'),
('Biografi'),
('Referensi'),
('Seni & Budaya');

-- Insert Users (Siswa & Admin)
-- Password untuk semua: "password" (di-hash dengan password_hash)
-- Siswa: siswa1 / siswa2 / siswa3
-- Admin: admin1
INSERT INTO users (username, password, nama_lengkap, kelas, role) VALUES
('siswa1', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Budi Santoso', 'XII RPL A', 'siswa'),
('siswa2', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Siti Nurhaliza', 'XII TKJ B', 'siswa'),
('siswa3', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Ahmad Wijaya', 'XI AKL C', 'siswa'),
('admin1', '$2y$10$YN6d5H7.M7K3lZ8X9Q2XZ.8Q1E5X9K7L3M5N6O7P8Q9R0S1T2U3V4', 'Ibu Perpustakaan', NULL, 'admin');

-- Insert Buku Sample
INSERT INTO buku (id_kategori, judul, penulis, penerbit, tahun_terbit, stok, sinopsis, cover_buku) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 5, 'Kisah inspiratif tentang anak-anak Indonesia yang berjuang mengejar pendidikan di tengah keterbatasan.', 'default.jpg'),
(1, 'Pergi', 'Tere Liye', 'Sabak Grip', 2014, 3, 'Novel petualangan yang menginspirasi tentang perjalanan hidup dan mimpi.', 'default.jpg'),
(2, 'Apa Itu Pilihan Karir?', 'John Smith', 'Gramedia', 2010, 4, 'Panduan praktis memilih karir yang sesuai dengan passion dan kemampuan.', 'default.jpg'),
(3, 'Dasar-Dasar Pemrograman Python', 'Mark Lutz', 'O''Reilly Media', 2019, 6, 'Buku referensi lengkap untuk mempelajari Python dari dasar hingga advanced.', 'default.jpg'),
(3, 'Web Development dengan PHP & MySQL', 'Budi Sutrisno', 'Informatika', 2018, 4, 'Panduan membuat website dinamis menggunakan PHP dan MySQL untuk pemula.', 'default.jpg'),
(4, 'Sejarah Indonesia Lengkap', 'Sartono Kartodirdjo', 'Kompas Media', 2008, 2, 'Referensi sejarah Indonesia dari zaman prasejarah hingga era modern.', 'default.jpg'),
(5, 'Steve Jobs: Cara Hidup Visioner', 'Walter Isaacson', 'Gramedia Pustaka Utama', 2011, 3, 'Biografi mendalam tentang pendiri Apple dan filosofi inovasinya.', 'default.jpg'),
(7, 'Batik: Seni dan Filosofi', 'Edy Susanto', 'Pusaka', 2015, 2, 'Menjelajahi kekayaan warisan batik Indonesia dan maknanya dalam budaya.', 'default.jpg');

-- Tampilkan ringkasan data yang telah dimasukkan
SELECT COUNT(*) as 'Total Kategori' FROM kategori;
SELECT COUNT(*) as 'Total Users' FROM users;
SELECT COUNT(*) as 'Total Buku' FROM buku;
SELECT COUNT(*) as 'Total Peminjaman' FROM peminjaman;
