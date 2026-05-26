<?php
/**
 * Halaman Katalog Buku Siswa
 * Menampilkan semua buku dengan fitur search, filter kategori, dan modal detail booking
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/business_logic.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('siswa');

// Get all categories
$categories = fetch_all("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

// Get all books
$books = fetch_all(
    "SELECT b.id_buku, b.id_kategori, b.judul, b.penulis, b.penerbit, b.tahun_terbit, b.stok, b.sinopsis, b.cover_buku, k.nama_kategori
     FROM buku b
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     ORDER BY b.judul ASC"
);

$id_user = $_SESSION['id_user'];
$user_active_loans = get_user_active_loans($id_user);
$can_borrow_more = $user_active_loans < MAX_PEMINJAMAN_AKTIF;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - E-Perpus SMEA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    <script src="https://cdn.tailwindcss.com"></script>
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

        .book-card-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Floating background balls -->
    <div id="floating-container"></div>

    <!-- Navbar -->
    <?php render_navbar('Katalog Buku', 'siswa'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_siswa('katalog'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto" x-data="katalogApp()">
            <!-- Page title -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-slate-800"><i class="fas fa-book"></i> Katalog Buku</h2>
                <p class="text-slate-600 mt-1">Temukan buku favorit Anda dan pinjam sekarang</p>
            </div>

            <!-- Search bar -->
            <div class="mb-6 bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-4 shadow-xl shadow-slate-100/50">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari buku berdasarkan judul atau penulis..."
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent"
                >
                <p class="text-xs text-slate-500 mt-2"><i class="fas fa-lightbulb"></i> Mulai ketik untuk mencari buku</p>
            </div>

            <!-- Category filter -->
            <div class="mb-6 bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-4 shadow-xl shadow-slate-100/50">
                <p class="text-sm font-semibold text-slate-700 mb-3">Filter Kategori:</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="selectedCategory = null"
                        :class="selectedCategory === null ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition"
                    >
                        Semua Kategori
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <button
                            @click="selectedCategory = <?php echo $cat['id_kategori']; ?>"
                            :class="selectedCategory === <?php echo $cat['id_kategori']; ?> ? 'bg-[#0E7490] text-white' : 'bg-white/50 hover:bg-white/70 text-slate-700'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition"
                        >
                            <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Books grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <template x-for="book in filteredBooks" :key="book.id_buku">
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl overflow-hidden shadow-xl shadow-slate-100/50 hover:shadow-2xl hover:shadow-slate-200/50 transition transform hover:scale-105">
                        <!-- Book image -->
                        <div class="relative overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 h-48 flex items-center justify-center">
                            <img :src="'/Glassmorphism/assets/img/' + book.cover_buku" :alt="book.judul" class="w-full h-full object-cover">
                            <!-- Stock badge -->
                            <div :class="book.stok > 0 ? 'bg-emerald-500' : 'bg-red-500'" class="absolute top-3 right-3 text-white px-3 py-1 rounded-full text-xs font-bold">
                                <span x-text="book.stok + ' tersedia'"></span>
                            </div>
                        </div>

                        <!-- Book info -->
                        <div class="p-4">
                            <h3 class="font-bold text-slate-800 line-clamp-2" x-text="book.judul"></h3>
                            <p class="text-sm text-slate-600 mt-1" x-text="book.penulis"></p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs bg-blue-100/80 text-blue-800 px-2 py-1 rounded" x-text="book.nama_kategori || 'Uncategorized'"></span>
                                <span class="text-xs text-slate-500" x-text="book.tahun_terbit ? book.tahun_terbit : '-'"></span>
                            </div>

                            <!-- Sinopsis preview -->
                            <p class="text-xs text-slate-600 mt-3 line-clamp-3" x-text="book.sinopsis || 'Tidak ada deskripsi'"></p>

                            <!-- Action button -->
                            <button
                                @click="openDetailModal(book)"
                                :disabled="!canBorrow"
                                class="w-full mt-4 bg-[#0E7490] hover:bg-[#155E75] text-white px-4 py-2 rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
                                :title="!canBorrow ? 'Anda sudah mencapai batas peminjaman (3 buku)' : ''"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No results -->
            <div x-show="filteredBooks.length === 0" class="text-center py-12">
                <p class="text-slate-600 mb-4">Tidak ada buku yang sesuai dengan pencarian Anda</p>
            </div>

            <!-- Detail Modal with Alpine.js -->
            <div
                x-show="showDetailModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @keydown.escape="showDetailModal = false"
                style="display: none;"
            >
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 sm:p-8">
                        <!-- Close button -->
                        <button
                            @click="showDetailModal = false"
                            class="absolute top-4 right-4 text-slate-600 hover:text-slate-800 text-2xl"
                        >
                            <i class="fas fa-times"></i>
                        </button>

                        <!-- Modal content -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-show="selectedBook">
                            <!-- Book image -->
                            <div class="flex items-center justify-center bg-slate-100 rounded-lg overflow-hidden">
                                <img :src="'/Glassmorphism/assets/img/' + selectedBook.cover_buku" :alt="selectedBook.judul" class="w-full h-96 object-cover">
                            </div>

                            <!-- Book details -->
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800 mb-2" x-text="selectedBook.judul"></h2>
                                <p class="text-slate-600 mb-4" x-text="'Penulis: ' + selectedBook.penulis"></p>

                                <!-- Book metadata -->
                                <div class="space-y-3 mb-6 border-t border-slate-200 pt-4">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Penerbit:</span>
                                        <span class="font-medium" x-text="selectedBook.penerbit || '-'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Tahun Terbit:</span>
                                        <span class="font-medium" x-text="selectedBook.tahun_terbit || '-'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Kategori:</span>
                                        <span class="font-medium" x-text="selectedBook.nama_kategori || '-'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Stok Tersedia:</span>
                                        <span :class="selectedBook.stok > 0 ? 'text-emerald-600' : 'text-red-600'" class="font-bold" x-text="selectedBook.stok"></span>
                                    </div>
                                </div>

                                <!-- Sinopsis -->
                                <div class="mb-6">
                                    <h3 class="font-semibold text-slate-800 mb-2">Sinopsis:</h3>
                                    <p class="text-sm text-slate-600 leading-relaxed" x-text="selectedBook.sinopsis || 'Tidak ada deskripsi'"></p>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex gap-3">
                                    <button
                                        @click="bookingBuku(selectedBook.id_buku)"
                                        :disabled="selectedBook.stok <= 0 || !canBorrow"
                                        class="flex-1 bg-[#0E7490] hover:bg-[#155E75] text-white px-4 py-3 rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Pinjam Buku
                                    </button>
                                    <button
                                        @click="showDetailModal = false"
                                        class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-3 rounded-lg font-semibold transition"
                                    >
                                        Tutup
                                    </button>
                                </div>

                                <!-- Warning for max loans -->
                                <div x-show="!canBorrow" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-800">
                                        <i class="fas fa-exclamation-triangle"></i> Anda sudah mencapai batas maksimal peminjaman (3 buku). Kembalikan salah satu buku terlebih dahulu.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Alpine.js app logic -->
    <script>
        function katalogApp() {
            return {
                search: '',
                selectedCategory: null,
                showDetailModal: false,
                selectedBook: null,
                canBorrow: <?php echo $can_borrow_more ? 'true' : 'false'; ?>,
                books: <?php echo json_encode($books); ?>,

                get filteredBooks() {
                    return this.books.filter(book => {
                        const matchesSearch = this.search === '' ||
                            book.judul.toLowerCase().includes(this.search.toLowerCase()) ||
                            book.penulis.toLowerCase().includes(this.search.toLowerCase());

                        const matchesCategory = this.selectedCategory === null ||
                            book.id_kategori == this.selectedCategory;

                        return matchesSearch && matchesCategory;
                    });
                },

                openDetailModal(book) {
                    if (!this.canBorrow) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Peminjaman Tercapai',
                            text: 'Anda sudah mencapai batas maksimal peminjaman (3 buku). Kembalikan salah satu buku terlebih dahulu.',
                            confirmButtonColor: '#0E7490'
                        });
                        return;
                    }
                    this.selectedBook = book;
                    this.showDetailModal = true;
                },

                bookingBuku(id_buku) {
                    if (!this.canBorrow) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Peminjaman Tercapai',
                            text: 'Anda sudah mencapai batas maksimal peminjaman (3 buku).',
                            confirmButtonColor: '#0E7490'
                        });
                        return;
                    }

                    if (this.selectedBook.stok <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok Habis',
                            text: 'Maaf, stok buku ini sedang habis.',
                            confirmButtonColor: '#0E7490'
                        });
                        return;
                    }

                    // Send booking request
                    fetch('/Glassmorphism/api/create_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id_buku: id_buku
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#0E7490'
                            }).then(() => {
                                this.showDetailModal = false;
                                // Refresh page or update state
                                setTimeout(() => location.reload(), 500);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#0E7490'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat memproses booking.',
                            confirmButtonColor: '#0E7490'
                        });
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
