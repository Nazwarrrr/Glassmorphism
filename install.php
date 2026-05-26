<?php
/**
 * Installation Script - E-Perpus SMEA
 * Buat database, table, seed data dalam 1 klik
 * Akses: http://localhost/glassmorphism/install.php
 */

// Koneksi ke MySQL server tanpa database spesifik
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'perpustakaan_smea';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        // 1. Koneksi ke server MySQL
        $conn = new mysqli($db_host, $db_user, $db_pass);
        
        if ($conn->connect_error) {
            throw new Exception('Koneksi MySQL gagal: ' . $conn->connect_error);
        }

        // 2. Buat database jika belum ada
        $conn->query("DROP DATABASE IF EXISTS `$db_name`");
        if (!$conn->query("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new Exception('Gagal membuat database: ' . $conn->error);
        }

        // 3. Select database
        if (!$conn->select_db($db_name)) {
            throw new Exception('Gagal select database: ' . $conn->error);
        }

        // 4. Buat tabel kategori
        $sql_kategori = "
        CREATE TABLE kategori (
            id_kategori INT AUTO_INCREMENT PRIMARY KEY,
            nama_kategori VARCHAR(50) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        if (!$conn->query($sql_kategori)) {
            throw new Exception('Gagal membuat tabel kategori: ' . $conn->error);
        }

        // 5. Buat tabel users
        $sql_users = "
        CREATE TABLE users (
            id_user INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            role ENUM('siswa', 'admin') DEFAULT 'siswa',
            kelas VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        if (!$conn->query($sql_users)) {
            throw new Exception('Gagal membuat tabel users: ' . $conn->error);
        }

        // 6. Buat tabel buku
        $sql_buku = "
        CREATE TABLE buku (
            id_buku INT AUTO_INCREMENT PRIMARY KEY,
            id_kategori INT NOT NULL,
            judul VARCHAR(200) NOT NULL,
            penulis VARCHAR(100) NOT NULL,
            penerbit VARCHAR(100),
            tahun_terbit INT,
            stok INT DEFAULT 0,
            sinopsis TEXT,
            cover_buku VARCHAR(255) DEFAULT 'default.jpg',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE CASCADE,
            INDEX idx_kategori (id_kategori),
            INDEX idx_judul (judul)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        if (!$conn->query($sql_buku)) {
            throw new Exception('Gagal membuat tabel buku: ' . $conn->error);
        }

        // 7. Buat tabel peminjaman
        $sql_peminjaman = "
        CREATE TABLE peminjaman (
            id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
            id_user INT NOT NULL,
            id_buku INT NOT NULL,
            tgl_booking TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            tgl_pinjam DATETIME,
            tgl_kembali DATETIME,
            tgl_dikembalikan DATETIME,
            status ENUM('Menunggu Konfirmasi', 'Sedang Dipinjam', 'Selesai', 'Ditolak') DEFAULT 'Menunggu Konfirmasi',
            denda INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
            FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
            INDEX idx_user (id_user),
            INDEX idx_buku (id_buku),
            INDEX idx_status (status),
            INDEX idx_tgl_kembali (tgl_kembali)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        if (!$conn->query($sql_peminjaman)) {
            throw new Exception('Gagal membuat tabel peminjaman: ' . $conn->error);
        }

        // 8. Seed Kategori (7 kategori)
        $kategori_data = [
            'Fiksi',
            'Non-Fiksi',
            'Referensi',
            'Teknologi',
            'Seni & Budaya',
            'Kesehatan',
            'Pendidikan'
        ];

        foreach ($kategori_data as $nama) {
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->bind_param("s", $nama);
            if (!$stmt->execute()) {
                throw new Exception('Gagal insert kategori: ' . $stmt->error);
            }
            $stmt->close();
        }

        // 9. Seed Users (1 siswa + 1 admin)
        $siswa_pass = password_hash('password', PASSWORD_BCRYPT);
        $admin_pass = password_hash('password', PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, role, kelas) VALUES (?, ?, ?, ?, ?)");
        
        // Insert Siswa
        $username = 'siswa';
        $nama = 'Siswa Demo';
        $role = 'siswa';
        $kelas = 'XII RPL 1';
        $stmt->bind_param("sssss", $username, $siswa_pass, $nama, $role, $kelas);
        if (!$stmt->execute()) {
            throw new Exception('Gagal insert siswa: ' . $stmt->error);
        }

        // Insert Admin
        $username = 'admin';
        $nama = 'Admin Perpustakaan';
        $role = 'admin';
        $kelas = null;
        $stmt->bind_param("sssss", $username, $admin_pass, $nama, $role, $kelas);
        if (!$stmt->execute()) {
            throw new Exception('Gagal insert admin: ' . $stmt->error);
        }
        $stmt->close();

        // 10. Seed Buku (8 sample books)
        $books = [
            ['kategori' => 'Fiksi', 'judul' => 'Laskar Pelangi', 'penulis' => 'Andrea Hirata', 'penerbit' => 'Bentang', 'tahun' => 2005, 'stok' => 5, 'sinopsis' => 'Kisah inspiratif sepuluh anak dari keluarga kurang mampu di Pulau Belitong yang berjuang meraih pendidikan.'],
            ['kategori' => 'Fiksi', 'judul' => 'Negeri Para Bedebah', 'penulis' => 'Tere Liye', 'penerbit' => 'Gramedia', 'tahun' => 2012, 'stok' => 4, 'sinopsis' => 'Perjalanan Alif menemukan makna kehidupan melalui petualangan di berbagai negara.'],
            ['kategori' => 'Non-Fiksi', 'judul' => 'Sejarah Indonesia', 'penulis' => 'Sartono Kartodirdjo', 'penerbit' => 'Gramedia', 'tahun' => 2008, 'stok' => 3, 'sinopsis' => 'Komprehensif sejarah bangsa Indonesia dari pra-sejarah hingga era modern.'],
            ['kategori' => 'Teknologi', 'judul' => 'Clean Code', 'penulis' => 'Robert C. Martin', 'penerbit' => 'Prentice Hall', 'tahun' => 2008, 'stok' => 2, 'sinopsis' => 'Panduan menulis kode yang indah, mudah dibaca, dan dapat dipertahankan.'],
            ['kategori' => 'Referensi', 'judul' => 'Kamus Besar Bahasa Indonesia', 'penulis' => 'Pusat Bahasa', 'penerbit' => 'Kementerian Pendidikan', 'tahun' => 2016, 'stok' => 3, 'sinopsis' => 'Referensi lengkap kosa kata Bahasa Indonesia resmi.'],
            ['kategori' => 'Kesehatan', 'judul' => 'Gaya Hidup Sehat', 'penulis' => 'Dr. Maulana Akbar', 'penerbit' => 'Buku Kesehatan', 'tahun' => 2020, 'stok' => 4, 'sinopsis' => 'Panduan lengkap menjaga kesehatan fisik dan mental dalam kehidupan sehari-hari.'],
            ['kategori' => 'Seni & Budaya', 'judul' => 'Batik Warisan Leluhur', 'penulis' => 'Adi Kusrianto', 'penerbit' => 'Gramedia', 'tahun' => 2010, 'stok' => 2, 'sinopsis' => 'Eksplorasi mendalam tentang seni batik Indonesia dan makna filosofisnya.'],
            ['kategori' => 'Pendidikan', 'judul' => 'Metodologi Penelitian', 'penulis' => 'Prof. Sugiyono', 'penerbit' => 'Alfabeta', 'tahun' => 2019, 'stok' => 3, 'sinopsis' => 'Panduan komprehensif untuk penelitian kuantitatif, kualitatif, dan R&D.']
        ];

        foreach ($books as $book) {
            // Cari id_kategori berdasarkan nama
            $result = $conn->query("SELECT id_kategori FROM kategori WHERE nama_kategori = '{$book['kategori']}'");
            $row = $result->fetch_assoc();
            $id_kategori = $row['id_kategori'];

            $stmt = $conn->prepare("INSERT INTO buku (id_kategori, judul, penulis, penerbit, tahun_terbit, stok, sinopsis) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssii", $id_kategori, $book['judul'], $book['penulis'], $book['penerbit'], $book['tahun'], $book['stok'], $book['sinopsis']);
            if (!$stmt->execute()) {
                throw new Exception('Gagal insert buku: ' . $stmt->error);
            }
            $stmt->close();
        }

        $conn->close();
        $success = true;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - E-Perpus SMEA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes floatY {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        @keyframes floatX {
            0%, 100% { transform: translateX(0px); }
            50% { transform: translateX(30px); }
        }

        .float-ball {
            position: fixed;
            border-radius: 50%;
            filter: blur(20px);
            opacity: 0.25;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
    <!-- Floating balls -->
    <div id="floating-container"></div>

    <!-- Installation card -->
    <div class="w-full max-w-md bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 shadow-xl shadow-slate-100/50 relative z-10">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800"><i class="fas fa-book"></i> E-Perpus SMEA</h1>
            <p class="text-slate-600 mt-2">Setup & Instalasi Database</p>
        </div>

        <?php if ($success): ?>
            <!-- Success State -->
            <div class="space-y-4">
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                    <p class="text-2xl mb-2"><i class="fas fa-check-circle"></i></p>
                    <p class="text-lg font-bold text-emerald-900">Instalasi Berhasil!</p>
                    <p class="text-sm text-emerald-800 mt-2">Database dan seed data telah dibuat.</p>
                </div>

                <!-- Credentials Info -->
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-slate-700"><i class="fas fa-clipboard"></i> Test Credentials:</p>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-xs text-blue-600"><i class="fas fa-user"></i> Siswa:</p>
                        <p class="font-mono text-sm font-bold text-blue-900">username: <span class="text-emerald-600">siswa</span></p>
                        <p class="font-mono text-sm font-bold text-blue-900">password: <span class="text-emerald-600">password</span></p>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                        <p class="text-xs text-purple-600"><i class="fas fa-user-tie"></i> Admin:</p>
                        <p class="font-mono text-sm font-bold text-purple-900">username: <span class="text-emerald-600">admin</span></p>
                        <p class="font-mono text-sm font-bold text-purple-900">password: <span class="text-emerald-600">password</span></p>
                    </div>
                </div>

                <!-- Database Info -->
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-700">
                    <p class="font-semibold mb-2"><i class="fas fa-check"></i> Database Info:</p>
                    <ul class="space-y-1">
                        <li>• Database: <code class="bg-white px-2 py-1 rounded">perpustakaan_smea</code></li>
                        <li>• Host: <code class="bg-white px-2 py-1 rounded">localhost</code></li>
                        <li>• Tabel: 4 (kategori, users, buku, peminjaman)</li>
                        <li>• Seed Data: 7 kategori + 8 buku + 2 users</li>
                    </ul>
                </div>

                <!-- Action buttons -->
                <div class="space-y-2 mt-6">
                    <a href="index.php" class="block w-full bg-[#0E7490] hover:bg-[#155E75] text-white px-4 py-3 rounded-lg font-semibold text-center transition">
                        <i class="fas fa-rocket"></i> Buka Aplikasi
                    </a>
                    <button onclick="location.reload()" class="w-full bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-sync-alt"></i> Install Ulang
                    </button>
                </div>
            </div>

        <?php elseif ($error): ?>
            <!-- Error State -->
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-2xl mb-2"><i class="fas fa-times-circle"></i></p>
                    <p class="text-lg font-bold text-red-900">Instalasi Gagal</p>
                    <p class="text-sm text-red-800 mt-2 font-mono break-words"><?php echo htmlspecialchars($error); ?></p>
                </div>

                <!-- Troubleshooting -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-xs text-yellow-900">
                    <p class="font-semibold mb-2"><i class="fas fa-wrench"></i> Troubleshooting:</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Pastikan MySQL/XAMPP sedang berjalan</li>
                        <li>Cek konfigurasi di file ini (host, user, password)</li>
                        <li>Pastikan folder upload <code>/images/buku</code> ada (atau akan dibuat otomatis)</li>
                    </ul>
                </div>

                <button onclick="location.reload()" class="w-full bg-[#0E7490] hover:bg-[#155E75] text-white px-4 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-sync-alt"></i> Coba Lagi
                </button>
            </div>

        <?php else: ?>
            <!-- Initial State -->
            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-900">
                    <p class="font-semibold mb-2"><i class="fas fa-clipboard-list"></i> Akan Dilakukan:</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Buat database <code>perpustakaan_smea</code></li>
                        <li>Buat 4 tabel (kategori, users, buku, peminjaman)</li>
                        <li>Seed 7 kategori buku</li>
                        <li>Seed 2 test users (siswa + admin)</li>
                        <li>Seed 8 sample buku</li>
                    </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-900">
                    <p class="font-semibold mb-1"><i class="fas fa-exclamation-triangle"></i> Catatan Penting:</p>
                    <p>Jika database sudah ada sebelumnya, data lama akan dihapus!</p>
                </div>

                <form method="POST" class="space-y-3">
                    <input type="hidden" name="install" value="1">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-check-circle"></i> Mulai Instalasi
                    </button>
                </form>

                <a href="index.php" class="block w-full bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-3 rounded-lg font-semibold text-center transition">
                    <i class="fas fa-times-circle"></i> Batal
                </a>
            </div>

        <?php endif; ?>
    </div>

    <!-- Floating balls script -->
    <script>
        function createFloatingBalls() {
            const container = document.getElementById('floating-container');
            const ballCount = 4;
            const colors = [
                'rgba(14, 116, 144, 0.2)',
                'rgba(100, 200, 255, 0.25)',
                'rgba(14, 165, 233, 0.2)'
            ];

            for (let i = 0; i < ballCount; i++) {
                const ball = document.createElement('div');
                ball.className = 'float-ball';

                const size = Math.random() * 200 + 150;
                const duration = Math.random() * 15 + 20;
                const delay = Math.random() * 5;
                const xStart = Math.random() * window.innerWidth;
                const yStart = Math.random() * window.innerHeight;

                ball.style.width = size + 'px';
                ball.style.height = size + 'px';
                ball.style.left = xStart + 'px';
                ball.style.top = yStart + 'px';
                ball.style.background = colors[Math.floor(Math.random() * colors.length)];
                ball.style.animation = `
                    floatY ${duration}s ease-in-out ${delay}s infinite,
                    floatX ${duration * 1.5}s ease-in-out ${delay}s infinite
                `;

                container.appendChild(ball);
            }
        }

        document.addEventListener('DOMContentLoaded', createFloatingBalls);
    </script>
</body>
</html>
