<?php
/**
 * Halaman Dashboard Siswa
 * Menampilkan ringkasan akun, status denda, dan buku yang sedang dibawa
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('siswa');

// Get data siswa
$id_user = $_SESSION['id_user'];

// Auto-reject expired bookings
reject_expired_bookings();

// Get statistics
$total_active_loans = get_user_active_loans($id_user);
$total_fine = get_user_total_fine($id_user);

// Get recent active books (sedang dibawa)
$active_books = fetch_all(
    "SELECT p.id_peminjaman, b.judul, b.penulis, p.tgl_pinjam, p.tgl_kembali, p.denda, p.status
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     WHERE p.id_user = ? AND p.status IN ('Sedang Dipinjam', 'Terlambat')
     ORDER BY p.tgl_pinjam DESC
     LIMIT 5",
    [$id_user]
);

// Update status terlambat jika perlu
foreach ($active_books as $book) {
    update_overdue_status($book['id_peminjaman']);
}

// Re-fetch dengan status terbaru
$active_books = fetch_all(
    "SELECT p.id_peminjaman, b.judul, b.penulis, p.tgl_pinjam, p.tgl_kembali, p.denda, p.status
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     WHERE p.id_user = ? AND p.status IN ('Sedang Dipinjam', 'Terlambat')
     ORDER BY p.tgl_pinjam DESC
     LIMIT 5",
    [$id_user]
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Perpus SMEA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            filter: blur(40px);
            opacity: 0.4;
            z-index: 1;
        }

        /* Page transition styles - 500ms smooth */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        body {
            animation: fadeIn 500ms ease-in-out forwards;
        }

        .page-transition-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            z-index: 9998;
            pointer-events: none;
            opacity: 0;
        }

        .page-transition-overlay.active {
            animation: fadeIn 500ms ease-in-out forwards;
        }

        /* Subtle pulse animation for floating balls */
        @keyframes subtlePulse {
            0%, 100% { filter: blur(40px); opacity: 0.4; }
            50% { filter: blur(40px); opacity: 0.45; }
        }

        .float-ball.pulse {
            animation: subtlePulse 4s ease-in-out infinite !important;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Page transition overlay -->
    <div class="page-transition-overlay"></div>

    <!-- Floating background balls -->
    <div id="floating-container"></div>

    <!-- Navbar -->
    <?php render_navbar('Dashboard Siswa', 'siswa'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_siswa('dashboard'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            <!-- Welcome section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-800">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>! <i class="fas fa-hand-paper"></i></h2>
                <p class="text-slate-600 mt-1">Kelas: <?php echo htmlspecialchars($_SESSION['kelas'] ?? '-'); ?></p>
            </div>

            <!-- Metrics grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <!-- Total Peminjaman Aktif -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Total Peminjaman Aktif</p>
                            <p class="text-4xl font-bold text-[#0E7490] mt-2"><?php echo $total_active_loans; ?>/<?php echo MAX_PEMINJAMAN_AKTIF; ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <span class="text-2xl"><i class="fas fa-book"></i></span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-4">Booking + Sedang dibawa</p>
                </div>

                <!-- Total Denda -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Total Denda</p>
                            <p class="text-3xl font-bold <?php echo ($total_fine > 0) ? 'text-red-600' : 'text-emerald-600'; ?> mt-2">
                                <?php echo format_rupiah($total_fine); ?>
                            </p>
                        </div>
                        <div class="<?php echo ($total_fine > 0) ? 'bg-red-100' : 'bg-emerald-100'; ?> p-3 rounded-lg">
                            <?php if ($total_fine > 0): ?>
                                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                            <?php else: ?>
                                <i class="fas fa-check-circle text-2xl text-emerald-600"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-4">
                        <?php echo ($total_fine > 0) ? 'Denda aktif dari buku terlambat' : 'Tidak ada denda saat ini'; ?>
                    </p>
                </div>

                <!-- Books sedang dibawa -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Buku Sedang Dibawa</p>
                            <p class="text-4xl font-bold text-[#0E7490] mt-2"><?php echo count($active_books); ?></p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <span class="text-2xl"><i class="fas fa-book-open"></i></span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-4">Buku aktif yang sedang Anda pinjam</p>
                </div>
            </div>

            <!-- Recent books section -->
            <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span><i class="fas fa-clipboard-list"></i></span> Buku Yang Sedang Dibawa
                    </h3>
                    <a href="/Glassmorphism/siswa/pinjaman.php" class="text-[#0E7490] hover:text-[#155E75] text-sm font-semibold">
                        Lihat semua →
                    </a>
                </div>

                <?php if (empty($active_books)): ?>
                    <div class="text-center py-8">
                        <p class="text-slate-600 mb-4">Anda belum memiliki buku yang sedang dibawa</p>
                        <a href="/Glassmorphism/siswa/katalog.php" class="inline-block bg-[#0E7490] hover:bg-[#155E75] text-white px-6 py-2 rounded-lg font-medium transition">
                            Jelajahi Katalog
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-200">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Judul Buku</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Penulis</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Tgl Kembali</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Denda</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_books as $book): ?>
                                    <tr class="border-b border-slate-100 hover:bg-white/30 transition">
                                        <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($book['judul']); ?></td>
                                        <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($book['penulis']); ?></td>
                                        <td class="py-4 px-4 text-slate-600"><?php echo format_date_id($book['tgl_kembali']); ?></td>
                                        <td class="py-4 px-4">
                                            <?php render_status_badge($book['status'], $book['denda'], $book['tgl_kembali']); ?>
                                        </td>
                                        <td class="py-4 px-4 font-semibold <?php echo ($book['denda'] > 0) ? 'text-red-600' : 'text-slate-600'; ?>">
                                            <?php echo format_rupiah($book['denda']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tips section -->
            <div class="mt-8 bg-emerald-50/40 border border-emerald-200 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-emerald-900 mb-3"><i class="fas fa-lightbulb"></i> Tips Literasi</h3>
                <ul class="space-y-2 text-emerald-800 text-sm">
                    <li><i class="fas fa-check"></i> Kembalikan buku tepat waktu untuk menghindari denda</li>
                    <li><i class="fas fa-check"></i> Anda dapat meminjam maksimal 3 buku dalam satu waktu</li>
                    <li><i class="fas fa-check"></i> Booking akan otomatis dibatalkan jika tidak diambil dalam 2x24 jam</li>
                    <li><i class="fas fa-check"></i> Jelajahi katalog buku dan temukan bacaan favorit Anda!</li>
                </ul>
            </div>
        </main>
    </div>

    <!-- Floating balls and transition script -->
    <script>
        function createFloatingBalls() {
            const container = document.getElementById('floating-container');
            const ballCount = Math.random() * 3 + 3;
            const colors = [
                'rgba(14, 116, 144, 0.8)',
                'rgba(6, 182, 212, 0.75)',
                'rgba(34, 197, 94, 0.7)',
                'rgba(59, 130, 246, 0.75)',
                'rgba(147, 51, 234, 0.7)'
            ];

            for (let i = 0; i < ballCount; i++) {
                const ball = document.createElement('div');
                ball.className = 'float-ball pulse';

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
                    floatX ${duration * 1.5}s ease-in-out ${delay}s infinite,
                    subtlePulse 4s ease-in-out infinite
                `;

                container.appendChild(ball);
            }
        }

        // Handle page navigation with transition
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && link.target !== '_blank') {
                const isInternalLink = link.href.includes(window.location.origin) || link.href.startsWith('/');
                if (isInternalLink) {
                    e.preventDefault();
                    const overlay = document.querySelector('.page-transition-overlay');
                    overlay.classList.add('active');
                    setTimeout(() => {
                        window.location.href = link.href;
                    }, 250);
                }
            }
        });

        document.addEventListener('DOMContentLoaded', createFloatingBalls);
    </script>
</body>
</html>
