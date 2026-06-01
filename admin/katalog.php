<?php
/**
 * Halaman Katalog Admin
 * Tampilan katalog + CRUD buku dengan modal forms
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('admin');

// Get all categories
$categories = fetch_all("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

// Get all books
$books = fetch_all(
    "SELECT b.id_buku, b.id_kategori, b.judul, b.penulis, b.penerbit, b.tahun_terbit, b.stok, b.sinopsis, b.cover_buku, k.nama_kategori
     FROM buku b
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     ORDER BY b.judul ASC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Admin - E-Perpus SMEA</title>
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
            filter: blur(40px);
            opacity: 0.4;
            z-index: 1;
        }

        .book-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background-color: #f3f4f6;
        }

        .book-image-container {
            aspect-ratio: 2 / 3;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        }
    </style>
</head>
<body class="bg-white">
    <!-- Floating background balls -->
    <div id="floating-container"></div>

    <!-- Navbar -->
    <?php render_navbar('Katalog Buku', 'admin'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_admin('katalog'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto" x-data="katalogApp()">
            <!-- Page title & action buttons -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800"><i class="fas fa-book"></i> Katalog Buku</h2>
                    <p class="text-slate-600 mt-1">Kelola koleksi buku perpustakaan</p>
                </div>
                <button
                    @click="openAddModal()"
                    class="bg-[#0E7490] hover:bg-[#155E75] text-white px-6 py-3 rounded-lg font-semibold transition"
                >
                    + Tambah Buku
                </button>
            </div>

            <!-- Search bar -->
            <div class="mb-6 bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-4 shadow-xl shadow-slate-100/50">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari buku berdasarkan judul atau penulis..."
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent"
                >
            </div>

            <!-- Books grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <template x-for="book in filteredBooks" :key="book.id_buku">
                    <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl overflow-hidden shadow-xl shadow-slate-100/50 hover:shadow-2xl transition">
                        <!-- Book image -->
                        <div class="book-image-container relative">
                            <img :src="'/Glassmorphism/assets/img/' + book.cover_buku" :alt="book.judul" class="book-card-image">
                            <!-- Stock badge -->
                            <div :class="book.stok > 0 ? 'bg-emerald-500' : 'bg-red-500'" class="absolute top-3 right-3 text-white px-3 py-1 rounded-full text-xs font-bold">
                                <span x-text="book.stok + ' stok'"></span>
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

                            <!-- Action buttons -->
                            <div class="mt-4 flex gap-2">
                                <button
                                    @click="openEditModal(book)"
                                    class="flex-1 bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-2 rounded-lg text-sm font-medium transition"
                                    title="Edit buku"
                                >
                                    ✏️ Edit
                                </button>
                                <button
                                    @click="deleteBook(book.id_buku, book.judul)"
                                    class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition"
                                    title="Hapus buku"
                                >
                                    🗑️ Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No results -->
            <div x-show="filteredBooks.length === 0" class="text-center py-12">
                <p class="text-slate-600 mb-4">Tidak ada buku yang ditemukan</p>
            </div>

            <!-- Add/Edit Modal -->
            <div
                x-show="showModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @keydown.escape="showModal = false"
                style="display: none;"
            >
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 sm:p-8">
                        <!-- Close button -->
                        <button
                            @click="showModal = false"
                            class="absolute top-4 right-4 text-slate-600 hover:text-slate-800 text-2xl"
                        >
                            <i class="fas fa-times"></i>
                        </button>

                        <!-- Form title -->
                        <h2 class="text-2xl font-bold text-slate-800 mb-6" x-text="isEditMode ? 'Edit Buku' : 'Tambah Buku Baru'"></h2>

                        <!-- Form -->
                        <form @submit.prevent="saveBook" class="space-y-4">
                            <!-- Kategori -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Kategori *</label>
                                <select
                                    x-model="formData.id_kategori"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    required
                                >
                                    <option value="">-- Pilih Kategori --</option>
                                    <template x-for="cat in categories" :key="cat.id_kategori">
                                        <option :value="cat.id_kategori" x-text="cat.nama_kategori"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Judul -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Judul Buku *</label>
                                <input
                                    type="text"
                                    x-model="formData.judul"
                                    maxlength="255"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    required
                                >
                            </div>

                            <!-- Penulis -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Penulis *</label>
                                <input
                                    type="text"
                                    x-model="formData.penulis"
                                    maxlength="100"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    required
                                >
                            </div>

                            <!-- Penerbit & Tahun -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Penerbit</label>
                                    <input
                                        type="text"
                                        x-model="formData.penerbit"
                                        maxlength="100"
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Tahun Terbit</label>
                                    <input
                                        type="number"
                                        x-model="formData.tahun_terbit"
                                        min="1900"
                                        :max="new Date().getFullYear() + 1"
                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    >
                                </div>
                            </div>

                            <!-- Stok -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Stok *</label>
                                <input
                                    type="number"
                                    x-model.number="formData.stok"
                                    min="0"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    required
                                >
                            </div>

                            <!-- Sinopsis -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Sinopsis</label>
                                <textarea
                                    x-model="formData.sinopsis"
                                    rows="4"
                                    maxlength="1000"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490]"
                                    placeholder="Deskripsi singkat buku..."
                                ></textarea>
                            </div>

                            <!-- Cover image upload -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Cover Buku (JPG/PNG, max 2MB)</label>
                                <input
                                    type="file"
                                    @change="handleImageSelect"
                                    accept="image/jpeg,image/png"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg"
                                >
                                <p class="text-xs text-slate-500 mt-1">Format: JPG atau PNG. Ukuran maksimal 2MB. PNG akan otomatis dikonversi ke JPG.</p>
                                
                                <!-- Image preview -->
                                <div x-show="formData.cover_preview" class="mt-3">
                                    <img :src="formData.cover_preview" alt="Preview" class="h-32 object-cover rounded-lg">
                                </div>
                            </div>

                            <!-- Form actions -->
                            <div class="flex gap-3 mt-6 pt-4 border-t border-slate-200">
                                <button
                                    type="submit"
                                    class="flex-1 bg-[#0E7490] hover:bg-[#155E75] text-white px-4 py-3 rounded-lg font-semibold transition"
                                >
                                    <span x-text="isEditMode ? 'Perbarui Buku' : 'Tambah Buku'"></span>
                                </button>
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-3 rounded-lg font-semibold transition"
                                >
                                    Batal
                                </button>
                            </div>
                        </form>
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
                showModal: false,
                isEditMode: false,
                books: <?php echo json_encode($books); ?>,
                categories: <?php echo json_encode($categories); ?>,
                formData: {
                    id_buku: null,
                    id_kategori: '',
                    judul: '',
                    penulis: '',
                    penerbit: '',
                    tahun_terbit: new Date().getFullYear(),
                    stok: 1,
                    sinopsis: '',
                    cover_buku: 'default.jpg',
                    cover_preview: '',
                    cover_file: null
                },

                get filteredBooks() {
                    return this.books.filter(book => {
                        return this.search === '' ||
                            book.judul.toLowerCase().includes(this.search.toLowerCase()) ||
                            book.penulis.toLowerCase().includes(this.search.toLowerCase());
                    });
                },

                openAddModal() {
                    this.isEditMode = false;
                    this.formData = {
                        id_buku: null,
                        id_kategori: '',
                        judul: '',
                        penulis: '',
                        penerbit: '',
                        tahun_terbit: new Date().getFullYear(),
                        stok: 1,
                        sinopsis: '',
                        cover_buku: 'default.jpg',
                        cover_preview: '',
                        cover_file: null
                    };
                    this.showModal = true;
                },

                openEditModal(book) {
                    this.isEditMode = true;
                    this.formData = {
                        id_buku: book.id_buku,
                        id_kategori: book.id_kategori || '',
                        judul: book.judul,
                        penulis: book.penulis,
                        penerbit: book.penerbit || '',
                        tahun_terbit: book.tahun_terbit || new Date().getFullYear(),
                        stok: book.stok,
                        sinopsis: book.sinopsis || '',
                        cover_buku: book.cover_buku,
                        cover_preview: '/Glassmorphism/assets/img/' + book.cover_buku,
                        cover_file: null
                    };
                    this.showModal = true;
                },

                handleImageSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.formData.cover_file = file;

                    // Preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData.cover_preview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                saveBook() {
                    const formDataObj = new FormData();
                    formDataObj.append('id_buku', this.formData.id_buku || '');
                    formDataObj.append('id_kategori', this.formData.id_kategori);
                    formDataObj.append('judul', this.formData.judul);
                    formDataObj.append('penulis', this.formData.penulis);
                    formDataObj.append('penerbit', this.formData.penerbit);
                    formDataObj.append('tahun_terbit', this.formData.tahun_terbit);
                    formDataObj.append('stok', this.formData.stok);
                    formDataObj.append('sinopsis', this.formData.sinopsis);

                    if (this.formData.cover_file) {
                        formDataObj.append('cover_buku', this.formData.cover_file);
                    }

                    const endpoint = this.isEditMode ? '/Glassmorphism/api/edit_book.php' : '/Glassmorphism/api/add_book.php';

                    fetch(endpoint, {
                        method: 'POST',
                        body: formDataObj
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
                                location.reload();
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
                            text: 'Terjadi kesalahan saat menyimpan.',
                            confirmButtonColor: '#0E7490'
                        });
                    });
                },

                deleteBook(id_buku, judul) {
                    Swal.fire({
                        title: 'Hapus Buku?',
                        text: 'Apakah Anda yakin ingin menghapus "' + judul + '"? Semua riwayat peminjaman juga akan terhapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/Glassmorphism/api/delete_book.php', {
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
                                Swal.fire({
                                    icon: data.success ? 'success' : 'error',
                                    title: data.success ? 'Terhapus!' : 'Gagal!',
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
                'rgba(14, 116, 144, 0.8)',
                'rgba(6, 182, 212, 0.75)',
                'rgba(34, 197, 94, 0.7)',
                'rgba(59, 130, 246, 0.75)',
                'rgba(147, 51, 234, 0.7)'
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
