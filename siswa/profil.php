<?php
/**
 * Halaman Profil Siswa
 * Update data pribadi dan ubah password
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../helpers/layout.php';

// Check role
check_role('siswa');

$id_user = $_SESSION['id_user'];
$success_message = '';
$error_message = '';

// Get current user data
$user = fetch_one(
    "SELECT id_user, nama_lengkap, kelas, username FROM users WHERE id_user = ?",
    [$id_user]
);

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');

        if (empty($nama_lengkap)) {
            $error_message = 'Nama lengkap tidak boleh kosong.';
        } else if (strlen($nama_lengkap) > 100) {
            $error_message = 'Nama lengkap maksimal 100 karakter.';
        } else {
            try {
                execute_action(
                    "UPDATE users SET nama_lengkap = ? WHERE id_user = ?",
                    [$nama_lengkap, $id_user]
                );
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $user['nama_lengkap'] = $nama_lengkap;
                $success_message = 'Profil berhasil diperbarui.';
            } catch (Exception $e) {
                $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate input
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Semua field password harus diisi.';
        } else if (strlen($new_password) < 6) {
            $error_message = 'Password baru minimal 6 karakter.';
        } else if ($new_password !== $confirm_password) {
            $error_message = 'Password baru dan konfirmasi password tidak cocok.';
        } else {
            // Verify old password
            $current_user = fetch_one(
                "SELECT password FROM users WHERE id_user = ?",
                [$id_user]
            );

            if (!password_verify($old_password, $current_user['password'])) {
                $error_message = 'Password lama tidak sesuai.';
            } else {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    execute_action(
                        "UPDATE users SET password = ? WHERE id_user = ?",
                        [$hashed_password, $id_user]
                    );
                    $success_message = 'Password berhasil diubah.';
                    // Clear error from previous section
                    $error_message = '';
                } catch (Exception $e) {
                    $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - E-Perpus SMEA</title>
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
    </style>
</head>
<body class="bg-white">
    <!-- Floating background balls -->
    <div id="floating-container"></div>

    <!-- Navbar -->
    <?php render_navbar('Profil', 'siswa'); ?>

    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)] relative z-10">
        <!-- Sidebar -->
        <?php render_sidebar_siswa('profil'); ?>

        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            <!-- Page title -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-slate-800"><i class="fas fa-user"></i> Profil Saya</h2>
                <p class="text-slate-600 mt-1">Kelola informasi pribadi dan keamanan akun Anda</p>
            </div>

            <!-- Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="mb-6 p-4 bg-emerald-50/80 border border-emerald-200 rounded-lg">
                    <p class="text-sm text-emerald-800"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="mb-6 p-4 bg-red-50/80 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Content grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Update Profil Section -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <h3 class="text-xl font-bold text-slate-800 mb-6"><i class="fas fa-clipboard-list"></i> Informasi Pribadi</h3>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_profil">

                        <!-- Username (read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                            <input
                                type="text"
                                value="<?php echo htmlspecialchars($user['username']); ?>"
                                disabled
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 text-slate-600 cursor-not-allowed"
                            >
                            <p class="text-xs text-slate-500 mt-1">Username tidak dapat diubah</p>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                            <input
                                type="text"
                                id="nama_lengkap"
                                name="nama_lengkap"
                                value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                maxlength="100"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent"
                                required
                            >
                        </div>

                        <!-- Kelas (read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                            <input
                                type="text"
                                value="<?php echo htmlspecialchars($user['kelas']); ?>"
                                disabled
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 text-slate-600 cursor-not-allowed"
                            >
                            <p class="text-xs text-slate-500 mt-1">Hubungi admin untuk mengubah kelas</p>
                        </div>

                        <!-- Submit button -->
                        <button
                            type="submit"
                            class="w-full bg-[#0E7490] hover:bg-[#155E75] text-white px-4 py-3 rounded-lg font-semibold transition"
                        >
                            Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-6 shadow-xl shadow-slate-100/50">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">🔐 Ubah Password</h3>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="change_password">

                        <!-- Old Password -->
                        <div>
                            <label for="old_password" class="block text-sm font-medium text-slate-700 mb-2">Password Lama</label>
                            <input
                                type="password"
                                id="old_password"
                                name="old_password"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent"
                                required
                                autocomplete="new-password"
                                minlength="6"
                            >
                            <p class="text-xs text-slate-500 mt-1">Minimal 6 karakter</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent"
                                required
                                autocomplete="new-password"
                                minlength="6"
                            >
                        </div>

                        <!-- Submit button -->
                        <button
                            type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-semibold transition"
                        >
                            Ubah Password
                        </button>
                    </form>

                    <!-- Security tips -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs font-semibold text-blue-900 mb-2"><i class="fas fa-lightbulb"></i> Tips Keamanan:</p>
                        <ul class="text-xs text-blue-800 space-y-1">
                            <li><i class="fas fa-check"></i> Gunakan password yang kuat (huruf + angka + simbol)</li>
                            <li><i class="fas fa-check"></i> Jangan bagikan password kepada siapa pun</li>
                            <li><i class="fas fa-check"></i> Gunakan password unik untuk setiap akun</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Account info section -->
            <div class="mt-6 bg-slate-50/40 border border-slate-200 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4"><i class="fas fa-info-circle"></i> Informasi Akun</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-600">ID User:</p>
                        <p class="font-mono text-slate-800"><?php echo htmlspecialchars($user['id_user']); ?></p>
                    </div>
                    <div>
                        <p class="text-slate-600">Role:</p>
                        <p class="font-semibold text-slate-800">Siswa</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

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
