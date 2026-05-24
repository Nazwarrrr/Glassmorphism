<?php
/**
 * Halaman Dashboard Admin
 * Statistik global sirkulasi dan grafik tren kategori terlaris
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('admin');

// Get statistics
$total_siswa = fetch_one("SELECT COUNT(*) as count FROM users WHERE role = 'siswa'")['count'];
$total_buku = fetch_one("SELECT COUNT(*) as count FROM buku")['count'];
$buku_sedang_dipinjam = fetch_one("SELECT COUNT(*) as count FROM peminjaman WHERE status IN ('Sedang Dipinjam', 'Terlambat')")['count'];
$total_peminjaman = fetch_one("SELECT COUNT(*) as count FROM peminjaman")['count'];

// Get category popularity (top 5)
$category_stats = fetch_all(
    "SELECT k.nama_kategori, COUNT(p.id_peminjaman) as jumlah_pinjam
     FROM kategori k
     LEFT JOIN buku b ON k.id_kategori = b.id_kategori
     LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
     GROUP BY k.id_kategori, k.nama_kategori
     ORDER BY jumlah_pinjam DESC
     LIMIT 5"
);

// Get pending approvals count
$pending_approvals = fetch_one("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'Menunggu Konfirmasi'")['count'];

// Get overdue books
$overdue_books = fetch_one("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'Terlambat'")['count'];

// Get total fines
$total_fines = fetch_one("SELECT SUM(denda) as total FROM peminjaman WHERE denda > 0")['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Perpus SMEA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
<body class="bg-white">
    <!-- Floating background balls -->
    <div id="floating-container"></div>

    <!-- Navbar -->
    <?php render_navbar('Dashboard Admin', 'admin'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_admin('dashboard'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            <!-- Welcome section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-800">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>! 👋</h2>
                <p class="text-slate-600 mt-1">Kelola sirkulasi perpustakaan dan pantau statistik sistem</p>
            </div>

            <!-- Key metrics grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Total Siswa -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Total Siswa</p>
                            <p class="text-4xl font-bold text-[#0E7490] mt-2"><?php echo $total_siswa; ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <span class="text-2xl">👥</span>
                        </div>
                    </div>
                </div>

                <!-- Total Buku -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Total Buku Katalog</p>
                            <p class="text-4xl font-bold text-[#0E7490] mt-2"><?php echo $total_buku; ?></p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <span class="text-2xl">📚</span>
                        </div>
                    </div>
                </div>

                <!-- Buku Sedang Dipinjam -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Buku Sedang Dipinjam</p>
                            <p class="text-4xl font-bold text-orange-600 mt-2"><?php echo $buku_sedang_dipinjam; ?></p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-lg">
                            <span class="text-2xl">📖</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Booking Menunggu</p>
                            <p class="text-4xl font-bold text-amber-600 mt-2"><?php echo $pending_approvals; ?></p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <span class="text-2xl">⏳</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert boxes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <!-- Overdue alert -->
                <?php if ($overdue_books > 0): ?>
                    <div class="bg-red-50/40 border border-red-200 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <span class="text-4xl">⚠️</span>
                            <div>
                                <h3 class="font-bold text-red-900">Buku Terlambat</h3>
                                <p class="text-red-800 text-sm mt-1">Ada <?php echo $overdue_books; ?> peminjaman yang terlambat dikembalikan</p>
                                <a href="/Perpustakaan/admin/sirkulasi.php" class="text-red-700 hover:text-red-900 text-sm font-semibold mt-2 inline-block">
                                    Proses pengembalian →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Total fines alert -->
                <?php if ($total_fines > 0): ?>
                    <div class="bg-orange-50/40 border border-orange-200 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <span class="text-4xl">💰</span>
                            <div>
                                <h3 class="font-bold text-orange-900">Total Denda Terhitung</h3>
                                <p class="text-orange-800 text-sm mt-1"><?php echo format_rupiah($total_fines); ?> dari semua peminjaman terlambat</p>
                                <p class="text-orange-700 text-xs mt-2">Denda otomatis dihitung Rp1.000/hari</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Charts section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Category popularity chart -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">📊 Kategori Buku Paling Diminati</h3>
                    <div class="relative h-80">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <!-- Statistics table -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">📈 Ringkasan Statistik</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-white/30 rounded-lg">
                            <span class="text-slate-700">Total Peminjaman</span>
                            <span class="font-bold text-slate-800"><?php echo $total_peminjaman; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/30 rounded-lg">
                            <span class="text-slate-700">Buku Sedang Dipinjam</span>
                            <span class="font-bold text-orange-600"><?php echo $buku_sedang_dipinjam; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/30 rounded-lg">
                            <span class="text-slate-700">Booking Menunggu Persetujuan</span>
                            <span class="font-bold text-amber-600"><?php echo $pending_approvals; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/30 rounded-lg">
                            <span class="text-slate-700">Buku Terlambat</span>
                            <span class="font-bold text-red-600"><?php echo $overdue_books; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/30 rounded-lg border-t-2 border-slate-200">
                            <span class="text-slate-700 font-semibold">Total Denda Akumulasi</span>
                            <span class="font-bold text-lg text-slate-800"><?php echo format_rupiah($total_fines); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="bg-emerald-50/40 border border-emerald-200 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-emerald-900 mb-4">⚡ Tindakan Cepat</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <a href="/Perpustakaan/admin/sirkulasi.php" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-900 px-4 py-3 rounded-lg font-semibold text-center transition">
                        Proses Booking
                    </a>
                    <a href="/Perpustakaan/admin/katalog.php?action=add" class="bg-blue-100 hover:bg-blue-200 text-blue-900 px-4 py-3 rounded-lg font-semibold text-center transition">
                        Tambah Buku
                    </a>
                    <a href="/Perpustakaan/admin/katalog.php" class="bg-purple-100 hover:bg-purple-200 text-purple-900 px-4 py-3 rounded-lg font-semibold text-center transition">
                        Kelola Katalog
                    </a>
                    <a href="/Perpustakaan/admin/profil.php" class="bg-slate-100 hover:bg-slate-200 text-slate-900 px-4 py-3 rounded-lg font-semibold text-center transition">
                        Pengaturan Akun
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart.js configuration -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category chart data
            const categoryData = <?php echo json_encode($category_stats); ?>;
            
            const labels = categoryData.map(item => item.nama_kategori);
            const data = categoryData.map(item => item.jumlah_pinjam);

            const ctx = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Peminjaman',
                        data: data,
                        backgroundColor: 'rgba(14, 116, 144, 0.7)',
                        borderColor: 'rgba(14, 116, 144, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>

    <!-- Floating balls script -->
    <script>
        function createFloatingBalls() {
            const container = document.getElementById('floating-container');
            const ballCount = Math.random() * 3 + 3;
            const colors = [
                'rgba(135, 206, 250, 0.4)',
                'rgba(100, 200, 255, 0.35)',
                'rgba(120, 210, 250, 0.4)'
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
