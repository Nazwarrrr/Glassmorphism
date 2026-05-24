<?php
/**
 * Layout Components & Helper Functions
 * Komponen UI yang reusable untuk semua halaman
 */

/**
 * Render navbar dengan tombol logout
 * @param string $page_title Judul halaman
 * @param string $role User role (siswa/admin)
 */
function render_navbar($page_title, $role = null) {
    if ($role === null) {
        $role = $_SESSION['role'] ?? 'siswa';
    }
    ?>
    <nav class="bg-white/40 backdrop-blur-lg border-b border-white/60 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Title -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#0E7490] rounded-lg flex items-center justify-center text-white font-bold text-lg">
                        E
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-[#0E7490]">E-Perpus SMEA</h1>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($page_title); ?></p>
                    </div>
                </div>

                <!-- User info & logout -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?></p>
                        <p class="text-xs text-slate-500 capitalize"><?php echo htmlspecialchars($role); ?><?php echo ($role === 'siswa' && isset($_SESSION['kelas'])) ? ' - ' . htmlspecialchars($_SESSION['kelas']) : ''; ?></p>
                    </div>
                    <a href="/Perpustakaan/auth/logout.php" class="bg-red-100/80 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php
}

/**
 * Render sidebar navigation untuk siswa
 * @param string $active_menu Menu yang aktif (dashboard, katalog, pinjaman, profil)
 */
function render_sidebar_siswa($active_menu) {
    $menus = [
        'dashboard' => ['label' => 'Dashboard', 'url' => '/Perpustakaan/siswa/dashboard.php', 'icon' => '📊'],
        'katalog' => ['label' => 'Katalog Buku', 'url' => '/Perpustakaan/siswa/katalog.php', 'icon' => '📚'],
        'pinjaman' => ['label' => 'Pinjaman Saya', 'url' => '/Perpustakaan/siswa/pinjaman.php', 'icon' => '📋'],
        'profil' => ['label' => 'Profil', 'url' => '/Perpustakaan/siswa/profil.php', 'icon' => '👤'],
    ];
    ?>
    <aside class="w-full md:w-64 bg-white/40 backdrop-blur-lg border-r border-white/60 md:sticky md:top-16 md:h-[calc(100vh-64px)]">
        <nav class="flex md:flex-col gap-2 p-4 overflow-x-auto md:overflow-visible">
            <?php foreach ($menus as $key => $menu): ?>
                <a href="<?php echo $menu['url']; ?>" class="
                    flex-shrink-0 md:flex-shrink px-4 py-3 rounded-lg whitespace-nowrap md:whitespace-normal
                    <?php echo ($active_menu === $key) 
                        ? 'bg-[#0E7490] text-white font-semibold' 
                        : 'bg-white/50 hover:bg-white/70 text-slate-700 font-medium'; ?>
                    transition duration-200 flex items-center gap-2
                ">
                    <span><?php echo $menu['icon']; ?></span>
                    <span><?php echo $menu['label']; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <?php
}

/**
 * Render sidebar navigation untuk admin
 * @param string $active_menu Menu yang aktif
 */
function render_sidebar_admin($active_menu) {
    $menus = [
        'dashboard' => ['label' => 'Dashboard', 'url' => '/Perpustakaan/admin/dashboard.php', 'icon' => '📊'],
        'sirkulasi' => ['label' => 'Meja Sirkulasi', 'url' => '/Perpustakaan/admin/sirkulasi.php', 'icon' => '🔄'],
        'katalog' => ['label' => 'Katalog Buku', 'url' => '/Perpustakaan/admin/katalog.php', 'icon' => '📚'],
        'profil' => ['label' => 'Profil', 'url' => '/Perpustakaan/admin/profil.php', 'icon' => '👤'],
    ];
    ?>
    <aside class="w-full md:w-64 bg-white/40 backdrop-blur-lg border-r border-white/60 md:sticky md:top-16 md:h-[calc(100vh-64px)]">
        <nav class="flex md:flex-col gap-2 p-4 overflow-x-auto md:overflow-visible">
            <?php foreach ($menus as $key => $menu): ?>
                <a href="<?php echo $menu['url']; ?>" class="
                    flex-shrink-0 md:flex-shrink px-4 py-3 rounded-lg whitespace-nowrap md:whitespace-normal
                    <?php echo ($active_menu === $key) 
                        ? 'bg-[#0E7490] text-white font-semibold' 
                        : 'bg-white/50 hover:bg-white/70 text-slate-700 font-medium'; ?>
                    transition duration-200 flex items-center gap-2
                ">
                    <span><?php echo $menu['icon']; ?></span>
                    <span><?php echo $menu['label']; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <?php
}

/**
 * Render status badge untuk peminjaman
 * @param string $status Status peminjaman
 * @param int $denda Jumlah denda (optional)
 * @param string $tgl_kembali Tanggal kembali (optional)
 */
function render_status_badge($status, $denda = 0, $tgl_kembali = null) {
    $badge_class = '';
    $icon = '';

    switch ($status) {
        case 'Menunggu Konfirmasi':
            $badge_class = 'bg-amber-100/80 text-amber-800 border border-amber-300';
            $icon = '⏳';
            break;
        case 'Sedang Dipinjam':
            // Check if approaching due date
            if ($tgl_kembali) {
                $due_time = strtotime($tgl_kembali);
                $now = time();
                $diff = $due_time - $now;
                $hours_left = $diff / 3600;

                if ($hours_left < 24) {
                    $badge_class = 'bg-orange-100/80 text-orange-800 border border-orange-300';
                    $icon = '⚠️';
                } else {
                    $badge_class = 'bg-emerald-100/80 text-emerald-800 border border-emerald-300';
                    $icon = '✅';
                }
            } else {
                $badge_class = 'bg-emerald-100/80 text-emerald-800 border border-emerald-300';
                $icon = '✅';
            }
            break;
        case 'Terlambat':
            $badge_class = 'bg-red-100/80 text-red-800 border border-red-300';
            $icon = '❌';
            break;
        case 'Selesai':
            $badge_class = 'bg-blue-100/80 text-blue-800 border border-blue-300';
            $icon = '✓';
            break;
        case 'Ditolak':
            $badge_class = 'bg-slate-100/80 text-slate-800 border border-slate-300';
            $icon = '✕';
            break;
        default:
            $badge_class = 'bg-slate-100/80 text-slate-800 border border-slate-300';
            $icon = '•';
    }

    echo '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium ' . $badge_class . '">';
    echo $icon . ' ' . htmlspecialchars($status);
    if ($denda > 0 && ($status === 'Sedang Dipinjam' || $status === 'Terlambat')) {
        echo ' - Rp' . number_format($denda, 0, ',', '.');
    }
    echo '</span>';
}

/**
 * Format currency Indonesia
 * @param int $value
 * @return string
 */
function format_rupiah($value) {
    return 'Rp' . number_format($value, 0, ',', '.');
}

/**
 * Format date Indonesia
 * @param string $date
 * @return string
 */
function format_date_id($date) {
    if (empty($date)) return '-';
    
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $month . ' ' . $year;
}

/**
 * Format datetime dengan jam
 * @param string $datetime
 * @return string
 */
function format_datetime_id($datetime) {
    if (empty($datetime)) return '-';
    
    $date_part = format_date_id($datetime);
    $time_part = date('H:i', strtotime($datetime));
    
    return $date_part . ' pukul ' . $time_part;
}
?>
