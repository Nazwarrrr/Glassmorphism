<?php
/**
 * Halaman Meja Sirkulasi Admin
 * 2 tab: Antrean Pengajuan (booking menunggu) dan Buku Sedang Dipinjam (proses return)
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('admin');

// Get pending bookings (status Menunggu Konfirmasi)
$pending_bookings = fetch_all(
    "SELECT p.id_peminjaman, u.nama_lengkap, u.kelas, b.judul, b.penulis, p.tgl_booking, k.nama_kategori
     FROM peminjaman p
     JOIN users u ON p.id_user = u.id_user
     JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     WHERE p.status = 'Menunggu Konfirmasi'
     ORDER BY p.tgl_booking ASC"
);

// Get active loans (status Sedang Dipinjam & Terlambat)
$active_loans = fetch_all(
    "SELECT p.id_peminjaman, u.nama_lengkap, u.kelas, b.judul, b.penulis, p.tgl_pinjam, p.tgl_kembali, p.denda, p.status, k.nama_kategori
     FROM peminjaman p
     JOIN users u ON p.id_user = u.id_user
     JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     WHERE p.status IN ('Sedang Dipinjam', 'Terlambat')
     ORDER BY p.tgl_kembali ASC"
);

// Update status terlambat untuk semua active loans
foreach ($active_loans as &$loan) {
    $loan['denda_real_time'] = calculate_fine($loan['id_peminjaman'], $loan['tgl_kembali']);
    update_overdue_status($loan['id_peminjaman']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meja Sirkulasi - E-Perpus SMEA</title>
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
    <?php render_navbar('Meja Sirkulasi', 'admin'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_admin('sirkulasi'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto" x-data="sirkuApp()">
            <!-- Page title -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-slate-800"><i class="fas fa-sync-alt"></i> Meja Sirkulasi</h2>
                <p class="text-slate-600 mt-1">Kelola transaksi booking dan pengembalian buku</p>
            </div>

            <!-- Tabs navigation -->
            <div class="mb-6 bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-2 shadow-xl shadow-slate-100/50 flex flex-wrap gap-2">
                <button
                    @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                    class="flex-1 min-w-[150px] px-4 py-3 rounded-lg font-semibold transition text-center"
                >
                    <i class="fas fa-hourglass-half"></i> Antrean Pengajuan (<?php echo count($pending_bookings); ?>)
                </button>
                <button
                    @click="activeTab = 'active'"
                    :class="activeTab === 'active' ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                    class="flex-1 min-w-[150px] px-4 py-3 rounded-lg font-semibold transition text-center"
                >
                    <i class="fas fa-book-open"></i> Buku Sedang Dipinjam (<?php echo count($active_loans); ?>)
                </button>
            </div>

            <!-- Tab: Antrean Pengajuan -->
            <div x-show="activeTab === 'pending'" class="space-y-4">
                <?php if (empty($pending_bookings)): ?>
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 text-center shadow-xl shadow-slate-100/50">
                        <p class="text-slate-600">Tidak ada booking yang menunggu persetujuan</p>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50/40 border border-blue-200 rounded-2xl p-4 mb-4">
                        <p class="text-sm text-blue-900">
                            <i class="fas fa-info-circle"></i> Ketika siswa datang ke meja, klik tombol <i class="fas fa-check"></i> untuk konfirmasi dan mulai peminjaman.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-white/40 border-b-2 border-white/60 sticky top-0">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Siswa</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Kelas</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Judul Buku</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Booking</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_bookings as $booking): ?>
                                    <tr class="border-b border-slate-200 hover:bg-white/20 transition">
                                        <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($booking['nama_lengkap']); ?></td>
                                        <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($booking['kelas']); ?></td>
                                        <td class="py-4 px-4 text-slate-600">
                                            <div>
                                                <p class="font-medium"><?php echo htmlspecialchars($booking['judul']); ?></p>
                                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($booking['penulis']); ?></p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-xs text-slate-500"><?php echo format_datetime_id($booking['tgl_booking']); ?></td>
                                        <td class="py-4 px-4 text-center">
                                            <button
                                                @click="approveBooking(<?php echo $booking['id_peminjaman']; ?>)"
                                                class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-4 py-2 rounded-lg font-semibold text-sm transition"
                                                title="Approve booking (setujui peminjaman)"
                                            >
                                                <i class="fas fa-check"></i> Setuju
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Buku Sedang Dipinjam -->
            <div x-show="activeTab === 'active'" class="space-y-4">
                <?php if (empty($active_loans)): ?>
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 text-center shadow-xl shadow-slate-100/50">
                        <p class="text-slate-600">Tidak ada buku yang sedang dipinjam</p>
                    </div>
                <?php else: ?>
                    <div class="bg-amber-50/40 border border-amber-200 rounded-2xl p-4 mb-4">
                        <p class="text-sm text-amber-900">
                            <i class="fas fa-info-circle"></i> Ketika siswa mengembalikan buku fisik, klik tombol <i class="fas fa-redo"></i> untuk mencatat pengembalian dan hitung denda otomatis.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-white/40 border-b-2 border-white/60 sticky top-0">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Siswa</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Judul Buku</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Jatuh Tempo</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Denda</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_loans as $loan): ?>
                                    <?php
                                    // Determine status badge
                                    if ($loan['status'] === 'Terlambat') {
                                        $status_badge = 'bg-red-100 text-red-800 border border-red-300';
                                    } else {
                                        $status_badge = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                                    }
                                    ?>
                                    <tr class="border-b border-slate-200 hover:bg-white/20 transition">
                                        <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($loan['nama_lengkap']); ?></td>
                                        <td class="py-4 px-4 text-slate-600">
                                            <div>
                                                <p class="font-medium"><?php echo htmlspecialchars($loan['judul']); ?></p>
                                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($loan['penulis']); ?></p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-slate-600"><?php echo format_date_id($loan['tgl_kembali']); ?></td>
                                        <td class="py-4 px-4">
                                            <span class="<?php echo $status_badge; ?> px-3 py-1 rounded-full text-xs font-bold">
                                                <?php echo htmlspecialchars($loan['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="<?php echo ($loan['denda_real_time'] > 0) ? 'text-red-600 font-bold' : 'text-emerald-600'; ?>">
                                                <?php echo format_rupiah($loan['denda_real_time']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <button
                                                @click="returnBook(<?php echo $loan['id_peminjaman']; ?>, '<?php echo htmlspecialchars(addslashes($loan['judul'])); ?>')"
                                                class="bg-orange-100 hover:bg-orange-200 text-orange-700 px-4 py-2 rounded-lg font-semibold text-sm transition"
                                                title="Return book (catat pengembalian)"
                                            >
                                                ↺ Kembali
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Alpine.js app logic -->
    <script>
        function sirkuApp() {
            return {
                activeTab: 'pending',

                approveBooking(id_peminjaman) {
                    Swal.fire({
                        title: 'Setujui Booking?',
                        text: 'Siswa akan mulai meminjam buku ini. Jatuh tempo otomatis 7 hari ke depan.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0E7490',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Setujui',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/Glassmorphism/api/approve_booking.php', {
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
                                    text: 'Terjadi kesalahan saat memproses.',
                                    confirmButtonColor: '#0E7490'
                                });
                            });
                        }
                    });
                },

                returnBook(id_peminjaman, judul_buku) {
                    Swal.fire({
                        title: 'Catat Pengembalian Buku?',
                        text: 'Buku "' + judul_buku + '" dikembalikan. Denda akan dihitung otomatis jika terlambat.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0E7490',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Catat Pengembalian',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/Glassmorphism/api/return_book.php', {
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
                                const finText = data.denda > 0 ? '\nDenda: ' + data.denda_formatted : '';
                                Swal.fire({
                                    icon: data.success ? 'success' : 'error',
                                    title: data.success ? 'Berhasil!' : 'Gagal!',
                                    text: data.message + finText,
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
                                    text: 'Terjadi kesalahan saat memproses.',
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
