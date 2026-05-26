<?php
/**
 * Halaman Pinjaman Saya (Siswa)
 * 3 tab: Menunggu Konfirmasi, Sedang Dibawa, Riwayat Selesai
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('siswa');

$id_user = $_SESSION['id_user'];

// Auto-reject expired bookings
reject_expired_bookings();

// Update status terlambat
$all_peminjaman = fetch_all(
    "SELECT id_peminjaman, tgl_kembali FROM peminjaman WHERE id_user = ? AND status IN ('Sedang Dipinjam', 'Terlambat')",
    [$id_user]
);
foreach ($all_peminjaman as $p) {
    update_overdue_status($p['id_peminjaman']);
}

// Get data per tab
$menunggu = fetch_all(
    "SELECT p.id_peminjaman, b.id_buku, b.judul, b.penulis, p.tgl_booking, k.nama_kategori
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     WHERE p.id_user = ? AND p.status = 'Menunggu Konfirmasi'
     ORDER BY p.tgl_booking DESC",
    [$id_user]
);

$sedang_dibawa = fetch_all(
    "SELECT p.id_peminjaman, b.judul, b.penulis, p.tgl_pinjam, p.tgl_kembali, p.denda, p.status, k.nama_kategori
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     WHERE p.id_user = ? AND p.status IN ('Sedang Dipinjam', 'Terlambat')
     ORDER BY p.tgl_kembali ASC",
    [$id_user]
);

// Update denda untuk sedang dibawa
foreach ($sedang_dibawa as &$book) {
    $book['denda'] = calculate_fine($book['id_peminjaman'], $book['tgl_kembali']);
}

$riwayat = fetch_all(
    "SELECT p.id_peminjaman, b.judul, b.penulis, p.tgl_pinjam, p.tgl_dikembalikan, p.denda, p.status, k.nama_kategori
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     WHERE p.id_user = ? AND p.status IN ('Selesai', 'Ditolak')
     ORDER BY p.tgl_dikembalikan DESC",
    [$id_user]
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman Saya - E-Perpus SMEA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    <?php render_navbar('Pinjaman Saya', 'siswa'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_siswa('pinjaman'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto" x-data="pinjamanApp()">
            <!-- Page title -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-slate-800"><i class="fas fa-clipboard-list"></i> Pinjaman Saya</h2>
                <p class="text-slate-600 mt-1">Kelola semua peminjaman buku Anda</p>
            </div>

            <!-- Tabs navigation -->
            <div class="mb-6 bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-2 shadow-xl shadow-slate-100/50 flex flex-wrap gap-2">
                <button
                    @click="activeTab = 'menunggu'"
                    :class="activeTab === 'menunggu' ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                    class="flex-1 min-w-[140px] px-4 py-3 rounded-lg font-semibold transition text-center"
                >
                    <i class="fas fa-hourglass-half"></i> Menunggu (<?php echo count($menunggu); ?>)
                </button>
                <button
                    @click="activeTab = 'sedang'"
                    :class="activeTab === 'sedang' ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                    class="flex-1 min-w-[140px] px-4 py-3 rounded-lg font-semibold transition text-center"
                >
                    <i class="fas fa-book-open"></i> Sedang Dibawa (<?php echo count($sedang_dibawa); ?>)
                </button>
                <button
                    @click="activeTab = 'riwayat'"
                    :class="activeTab === 'riwayat' ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                    class="flex-1 min-w-[140px] px-4 py-3 rounded-lg font-semibold transition text-center"
                >
                    <i class="fas fa-check"></i> Riwayat (<?php echo count($riwayat); ?>)
                </button>
            </div>

            <!-- Tab: Menunggu Konfirmasi -->
            <div x-show="activeTab === 'menunggu'" class="space-y-4">
                <?php if (empty($menunggu)): ?>
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 text-center shadow-xl shadow-slate-100/50">
                        <p class="text-slate-600 mb-4">Anda belum memiliki booking yang menunggu konfirmasi</p>
                        <a href="/Glassmorphism/siswa/katalog.php" class="inline-block bg-[#0E7490] hover:bg-[#155E75] text-white px-6 py-2 rounded-lg font-medium transition">
                            Jelajahi Katalog
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($menunggu as $item): ?>
                        <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50 hover:shadow-2xl transition">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($item['judul']); ?></h3>
                                    <p class="text-sm text-slate-600 mt-1">Penulis: <?php echo htmlspecialchars($item['penulis']); ?></p>
                                    <p class="text-xs text-slate-500 mt-2">Kategori: <?php echo htmlspecialchars($item['nama_kategori'] ?? '-'); ?></p>
                                    <p class="text-xs text-amber-700 mt-3 font-semibold">
                                        <i class="fas fa-hourglass-half"></i> Booking sejak <?php echo format_datetime_id($item['tgl_booking']); ?>
                                    </p>
                                </div>
                                <button
                                    @click="cancelBooking(<?php echo $item['id_peminjaman']; ?>)"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 px-6 py-2 rounded-lg font-medium text-sm transition whitespace-nowrap"
                                >
                                    Batalkan
                                </button>
                            </div>
                            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <p class="text-xs text-amber-800">
                                    <i class="fas fa-lightbulb"></i> Ambil buku ini dalam waktu 2x24 jam dari waktu booking. Jika tidak diambil, booking akan otomatis dibatalkan dan stok dipulihkan.
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Tab: Sedang Dibawa -->
            <div x-show="activeTab === 'sedang'" class="space-y-4">
                <?php if (empty($sedang_dibawa)): ?>
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 text-center shadow-xl shadow-slate-100/50">
                        <p class="text-slate-600 mb-4">Anda belum memiliki buku yang sedang dibawa</p>
                        <a href="/Glassmorphism/siswa/katalog.php" class="inline-block bg-[#0E7490] hover:bg-[#155E75] text-white px-6 py-2 rounded-lg font-medium transition">
                            Pinjam Sekarang
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($sedang_dibawa as $item): ?>
                        <?php
                        // Determine time status
                        $now = time();
                        $due_time = strtotime($item['tgl_kembali']);
                        $diff_seconds = $due_time - $now;
                        $diff_hours = $diff_seconds / 3600;
                        
                        if ($diff_hours < 0) {
                            $status_text = 'TERLAMBAT ' . abs(ceil($diff_hours / 24)) . ' HARI';
                            $status_color = 'red';
                        } elseif ($diff_hours < 24) {
                            $status_text = 'JATUH TEMPO DALAM < 24 JAM';
                            $status_color = 'orange';
                        } else {
                            $status_text = 'AMAN';
                            $status_color = 'green';
                        }
                        ?>
                        <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50 hover:shadow-2xl transition">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($item['judul']); ?></h3>
                                    <p class="text-sm text-slate-600 mt-1">Penulis: <?php echo htmlspecialchars($item['penulis']); ?></p>
                                    <p class="text-xs text-slate-500 mt-2">Kategori: <?php echo htmlspecialchars($item['nama_kategori'] ?? '-'); ?></p>

                                    <!-- Timeline info -->
                                    <div class="mt-3 space-y-1 text-sm">
                                        <p class="text-slate-600">📅 Dipinjam: <?php echo format_date_id($item['tgl_pinjam']); ?></p>
                                        <p class="text-slate-600">📅 Jatuh Tempo: <?php echo format_date_id($item['tgl_kembali']); ?></p>
                                    </div>
                                </div>

                                <!-- Status & Fine section -->
                                <div class="text-right">
                                    <?php
                                    $badge_color_map = ['red' => 'bg-red-100 text-red-800', 'orange' => 'bg-orange-100 text-orange-800', 'green' => 'bg-emerald-100 text-emerald-800'];
                                    ?>
                                    <div class="<?php echo $badge_color_map[$status_color]; ?> px-4 py-2 rounded-lg font-bold text-sm">
                                        <?php echo $status_text; ?>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-xs text-slate-600">Denda:</p>
                                        <p class="text-2xl font-bold <?php echo ($item['denda'] > 0) ? 'text-red-600' : 'text-emerald-600'; ?>">
                                            <?php echo format_rupiah($item['denda']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Tab: Riwayat -->
            <div x-show="activeTab === 'riwayat'" class="space-y-4">
                <?php if (empty($riwayat)): ?>
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 text-center shadow-xl shadow-slate-100/50">
                        <p class="text-slate-600">Anda belum memiliki riwayat peminjaman</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($riwayat as $item): ?>
                        <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($item['judul']); ?></h3>
                                    <p class="text-sm text-slate-600 mt-1">Penulis: <?php echo htmlspecialchars($item['penulis']); ?></p>
                                    <p class="text-xs text-slate-500 mt-2">Kategori: <?php echo htmlspecialchars($item['nama_kategori'] ?? '-'); ?></p>

                                    <!-- Timeline -->
                                    <div class="mt-3 space-y-1 text-sm">
                                        <p class="text-slate-600">📅 Dipinjam: <?php echo format_date_id($item['tgl_pinjam']); ?></p>
                                        <p class="text-slate-600">📅 Dikembalikan: <?php echo format_date_id($item['tgl_dikembalikan']); ?></p>
                                    </div>
                                </div>

                                <!-- Status & Fine -->
                                <div class="text-right">
                                    <?php
                                    if ($item['status'] === 'Selesai') {
                                        $status_badge = 'bg-blue-100 text-blue-800';
                                        $status_icon = '<i class="fas fa-check"></i>';
                                    } else {
                                        $status_badge = 'bg-slate-100 text-slate-800';
                                        $status_icon = '<i class="fas fa-times"></i>';
                                    }
                                    ?>
                                    <div class="<?php echo $status_badge; ?> px-4 py-2 rounded-lg font-bold text-sm inline-block">
                                        <?php echo $status_icon . ' ' . htmlspecialchars($item['status']); ?>
                                    </div>
                                    <?php if ($item['denda'] > 0): ?>
                                        <div class="mt-3">
                                            <p class="text-xs text-slate-600">Denda akhir:</p>
                                            <p class="text-xl font-bold text-red-600"><?php echo format_rupiah($item['denda']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Alpine.js app logic -->
    <script>
        function pinjamanApp() {
            return {
                activeTab: 'sedang',

                cancelBooking(id_peminjaman) {
                    Swal.fire({
                        title: 'Batalkan Booking?',
                        text: 'Apakah Anda yakin ingin membatalkan booking ini? Stok buku akan dipulihkan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0E7490',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Batalkan',
                        cancelButtonText: 'Tidak'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/Glassmorphism/api/cancel_booking.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    id_peminjaman: id_peminjaman
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.fire({
                                    icon: data.success ? 'success' : 'error',
                                    title: data.success ? 'Berhasil!' : 'Gagal!',
                                    text: data.message,
                                    confirmButtonColor: '#0E7490'
                                }).then(() => {
                                    if (data.success) {
                                        location.reload();
                                    }
                                });
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Terjadi kesalahan.',
                                    confirmButtonColor: '#0E7490'
                                });
                            });
                        }
                    });
                }
            };
        }
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
