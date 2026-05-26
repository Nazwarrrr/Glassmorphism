<?php
/**
 * Halaman Login E-Perpus SMEA
 * Login tunggal untuk Siswa dan Admin
 */

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/session_handler.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    redirect_by_role();
}

$error_message = '';
$timeout_message = '';

// Check apakah session timeout
if (isset($_GET['timeout'])) {
    $timeout_message = 'Sesi Anda telah berakhir. Silakan login kembali.';
}

// Process form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi input
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi.';
    } else {
        // Query user berdasarkan username
        $user = fetch_one(
            "SELECT id_user, username, password, nama_lengkap, role, kelas FROM users WHERE username = ?",
            [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            // Password benar, set session
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['kelas'] = $user['kelas'];
            $_SESSION['last_activity'] = time();

            // Redirect ke dashboard sesuai role
            if ($user['role'] === 'siswa') {
                header("Location: /Glassmorphism/siswa/dashboard.php");
            } else {
                header("Location: /Glassmorphism/admin/dashboard.php");
            }
            exit();
        } else {
            $error_message = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login E-Perpus SMEA</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            filter: blur(20px);
            opacity: 0.25;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-white overflow-hidden">
    <!-- Floating background balls -->
    <div id="floating-container"></div>

    <!-- Main content -->
    <div class="min-h-screen flex items-center justify-center relative z-10">
        <div class="w-full max-w-md p-6">
            <!-- Card login dengan glass morphism effect -->
            <div class="bg-white/40 backdrop-blur-lg border border-white/60 rounded-2xl p-8 shadow-xl shadow-slate-100/50 relative">
                <!-- Logo Perpus (Far Left) -->
                <div class="absolute -left-2 -top-2">
                    <img src="/Glassmorphism/images/perpus-logo.png" alt="Perpus Logo" class="h-20 object-contain">
                </div>

                <!-- Title -->
                <div class="text-center mb-8 pt-6">
                    <h1 class="text-3xl font-bold text-[#0E7490] mb-2">E-Perpus SMEA</h1>
                    <p class="text-slate-600 text-sm">Sistem Perpustakaan Digital Sekolah Menengah Kejuruan</p>
                </div>

                <!-- Messages -->
                <?php if (!empty($timeout_message)): ?>
                    <div class="mb-4 p-4 bg-amber-50/80 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-800"><?php echo htmlspecialchars($timeout_message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-4 bg-red-50/80 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800"><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form method="POST" class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent transition"
                            placeholder="Masukkan username"
                            required
                            autocomplete="username"
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0E7490] focus:border-transparent transition"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        class="w-full bg-[#0E7490] hover:bg-[#155E75] text-white font-semibold py-2 px-4 rounded-lg transition duration-200"
                    >
                        Masuk
                    </button>
                </form>

                <!-- Test credentials info -->
                <div class="mt-8 pt-6 border-t border-white/40">
                    <p class="text-xs text-slate-600 mb-3 font-semibold">Akun Test (untuk demo):</p>
                    <div class="space-y-2 text-xs text-slate-600">
                        <p><strong>Siswa:</strong> siswa1 / password</p>
                        <p><strong>Admin:</strong> admin1 / password</p>
                    </div>
                </div>
            </div>

            <!-- Footer info -->
            <div class="text-center mt-6 text-sm text-slate-500">
                <p>&copy; 2026 E-Perpus SMEA | Sistem Informasi Perpustakaan</p>
            </div>
        </div>
    </div>

    <!-- Floating balls animation script -->
    <script>
        function createFloatingBalls() {
            const container = document.getElementById('floating-container');
            const ballCount = Math.random() * 3 + 3; // 3-6 balls
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
                const direction = Math.random() > 0.5 ? 1 : -1;

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

        // Create floating balls on page load
        document.addEventListener('DOMContentLoaded', createFloatingBalls);
    </script>
</body>
</html>
