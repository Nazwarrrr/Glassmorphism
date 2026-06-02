# 📚 E-Perpus SMEA - Sistem Manajemen Perpustakaan Digital

> Aplikasi perpustakaan digital modern dengan interface glassmorphism yang elegan dan responsif, dirancang khusus untuk SMEA (Sekolah Menengah Atas) dengan sistem peminjaman buku berbasis web.

---

## 📋 Daftar Isi

1. [Overview Sistem](#overview-sistem)
2. [Instalasi & Setup](#instalasi--setup)
3. [Struktur File & Fungsi](#struktur-file--fungsi)
4. [Fitur Utama](#fitur-utama)
5. [Database Schema](#database-schema)
6. [API Endpoints](#api-endpoints)
7. [Business Logic](#business-logic)
8. [User Credentials](#user-credentials)
9. [Teknologi & Library](#teknologi--library)
10. [Development Notes](#development-notes)

---

## 🎯 Overview Sistem

**E-Perpus SMEA** adalah sistem informasi perpustakaan digital yang memungkinkan:

- ✅ **Admin/Petugas** mengelola katalog buku, menerima peminjaman, dan memproses pengembalian
- ✅ **Siswa** melihat katalog, melakukan booking buku, dan melacak status peminjaman
- ✅ **Sistem Otomatis** untuk perhitungan denda keterlambatan
- ✅ **Interface Responsif** dengan animasi smooth transitions dan floating background effects
- ✅ **Security** dengan session management dan password hashing

**Stack Teknologi:**
- Backend: PHP 7.4+ dengan PDO (Database abstraction)
- Frontend: HTML5, CSS3, Tailwind CSS, Alpine.js 3.x
- Database: MySQL 5.7+
- Server: Apache (XAMPP)

---

## 🚀 Instalasi & Setup

### Prasyarat

```
✓ XAMPP (Apache + MySQL + PHP)
✓ MySQL running
✓ PHP 7.4 atau lebih tinggi
```

### Langkah 1: Clone/Copy Project

```bash
# Copy folder ke htdocs
cp -r Glassmorphism /xampppp/htdocs/
```

### Langkah 2: Setup Database

**Opsi A - Menggunakan Installation Script (Recommended):**

1. Buka browser: `http://localhost/Glassmorphism/install.php`
2. Klik tombol "Install Database"
3. Database dan seeding data otomatis terbuat

**Opsi B - Manual SQL Execution:**

1. Buka MySQL
2. Copy isi dari `database_schema.sql`
3. Jalankan script di MySQL client

### Langkah 3: Verifikasi Koneksi

File: [`config/koneksi.php`](config/koneksi.php)

```php
define('DB_HOST', 'localhost');    // Host MySQL
define('DB_USER', 'root');         // User MySQL
define('DB_PASS', '');             // Password MySQL (kosong default)
define('DB_NAME', 'perpustakaan_smea');  // Nama database
```

> **Jika database tidak terhubung:** Sesuaikan credentials di atas dengan setting MySQL Anda

### Langkah 4: Akses Aplikasi

```
http://localhost/Glassmorphism/
```

Sistem akan redirect ke login page otomatis.

---

## 📁 Struktur File & Fungsi

### Root Level Files

```
Glassmorphism/
├── index.php                 # Router utama - redirect ke login/dashboard
├── install.php              # Script instalasi database & seeding
├── database_schema.sql      # SQL schema lengkap
├── README.md                # File dokumentasi ini
│
├── 📂 config/               # Konfigurasi sistem
│   ├── koneksi.php          # Database connection (PDO) & helper functions
│   └── session_handler.php  # Session management & authentication logic
│
├── 📂 auth/                 # Authentication
│   ├── login.php            # Halaman login (shared untuk admin & siswa)
│   └── logout.php           # Logout handler
│
├── 📂 admin/                # Admin/Petugas Dashboard
│   ├── dashboard.php        # Statistik utama & quick actions
│   ├── katalog.php          # Manajemen katalog buku (CRUD)
│   ├── sirkulasi.php        # Pemrosesan peminjaman & pengembalian
│   └── profil.php           # Profil admin & password management
│
├── 📂 siswa/                # Siswa Dashboard
│   ├── dashboard.php        # Dashboard siswa & loan summary
│   ├── katalog.php          # Browse katalog & booking buku
│   ├── pinjaman.php         # History peminjaman (3 tabs: pending/active/completed)
│   └── profil.php           # Profil siswa & password management
│
├── 📂 api/                  # API Endpoints (AJAX handlers)
│   ├── add_book.php         # Add buku baru (admin)
│   ├── edit_book.php        # Edit buku (admin)
│   ├── delete_book.php      # Hapus buku (admin)
│   ├── create_booking.php   # Buat booking baru (siswa)
│   ├── cancel_booking.php   # Batalkan booking (siswa)
│   ├── approve_booking.php  # Approve booking & mulai peminjaman (admin)
│   └── return_book.php      # Proses pengembalian buku (admin)
│
├── 📂 helpers/              # Helper functions & utilities
│   ├── business_logic.php   # Kalkulasi denda, validasi booking, dll
│   ├── image_handler.php    # Upload & manage cover buku
│   └── layout.php           # Reusable UI components (navbar, sidebar, dll)
│
├── 📂 assets/               # Static assets
│   └── img/                 # Logo, icon, default images
│
├── 📂 images/               # Upload folder untuk book covers
│   └── siswa/               # Folder untuk student data (future)
│
└── 📂 .git/                 # Version control (Git)
```

---

## 🎨 Fitur Utama

### 1. **ADMIN / PETUGAS**

#### Dashboard Admin (`admin/dashboard.php`)
```
📊 Statistik Utama
├─ Total Buku dalam katalog
├─ Total Peminjaman aktif
├─ Booking menunggu konfirmasi
└─ Total denda yang belum dibayar

📈 Analisis Data
├─ Buku paling sering dipinjam
├─ Top peminjam siswa
└─ Status sirkulasi

⚡ Tindakan Cepat
├─ Tambah buku baru (navigate ke katalog)
├─ Proses peminjaman (navigate ke sirkulasi)
└─ Lihat laporan (quick stats)
```

#### Katalog Buku (`admin/katalog.php`)
```
Grid Layout: 5 buku per row (desktop lg/xl screens)

Fitur:
✓ List semua buku dengan cover & info
✓ Tombol EDIT - modify judul, penulis, stok, dll
✓ Tombol HAPUS - hapus buku dari katalog
✓ Tombol "Tambah Buku" - form popup untuk buku baru

Informasi Buku:
├─ Judul
├─ Penulis
├─ Kategori
├─ Stok tersedia
├─ Tahun terbit
└─ Sinopsis

Action Edit (via API):
→ calls: api/edit_book.php
  ├─ Update judul, penulis, penerbit
  ├─ Update kategori
  ├─ Update stok
  ├─ Update sinopsis
  └─ Update cover image

Action Hapus (via API):
→ calls: api/delete_book.php
  ├─ Soft delete atau hard delete buku
  └─ Cascade delete related peminjaman (optional)
```

#### Sirkulasi Buku (`admin/sirkulasi.php`)
```
Proses peminjaman & pengembalian buku

Status Alur Peminjaman:
1. Siswa buat BOOKING
   └─ Status: "Menunggu Konfirmasi"

2. Admin APPROVE BOOKING
   ├─ Approval via api/approve_booking.php
   ├─ Set tgl_pinjam (tanggal hari ini)
   ├─ Set tgl_kembali (hari ini + 7 hari default)
   ├─ Update status ke "Sedang Dipinjam"
   └─ Kurangi stok buku (-1)

3. Siswa KEMBALIKAN buku
   ├─ Admin proses return via api/return_book.php
   ├─ Set tgl_dikembalikan (hari ini)
   ├─ Calculate denda (jika terlambat)
   ├─ Update status sesuai kondisi:
   │  ├─ "Selesai" (tepat waktu)
   │  └─ "Terlambat" (lebih dari tgl_kembali)
   └─ Tambah stok buku (+1)

View di Sirkulasi:
├─ Tab: Menunggu Konfirmasi
│  └─ List booking pending dengan tombol APPROVE
├─ Tab: Sedang Dipinjam
│  └─ List active loans dengan tombol RETURN
├─ Tab: Completed
│  └─ History peminjaman selesai
└─ Tab: Denda
   └─ List siswa dengan denda belum dibayar
```

#### Profil Admin (`admin/profil.php`)
```
✓ View informasi admin (nama, username, dll)
✓ Form ubah password
✓ Informasi sistem (PHP version, MySQL version, dll)
```

---

### 2. **SISWA**

#### Dashboard Siswa (`siswa/dashboard.php`)
```
📋 Ringkasan Peminjaman Aktif
├─ Jumlah buku sedang dipinjam
├─ Buku akan jatuh tempo (upcoming)
├─ Total denda belum dibayar
└─ Notifikasi terlambat (jika ada)

📚 Info Buku Aktif
├─ List buku yang sedang dipinjam
├─ Tanggal kembali
├─ Sisa hari peminjaman
└─ Status denda

⚡ Quick Action
├─ Browse katalog (go to katalog.php)
├─ Lihat history peminjaman (go to pinjaman.php)
└─ Kelola profil (go to profil.php)
```

#### Katalog Buku (`siswa/katalog.php`)
```
Grid Layout: 5 buku per row (desktop lg/xl screens)

Fitur:
✓ Browse semua buku available
✓ Filter by kategori
✓ Search by judul/penulis
✓ Lihat detail buku (modal popup)
✓ Tombol BOOKING - reserve buku

Modal Detail Buku:
├─ Cover image
├─ Judul & Penulis
├─ Kategori
├─ Penerbit & Tahun terbit
├─ Stok tersedia
├─ Sinopsis panjang
└─ Tombol "BOOKING SEKARANG"

Action Booking (via API):
→ calls: api/create_booking.php
  ├─ Validate: siswa tidak exceed MAX_PEMINJAMAN_AKTIF (default 3)
  ├─ Validate: stok buku > 0
  ├─ Create peminjaman record
  │  ├─ id_user = current siswa
  │  ├─ id_buku = selected book
  │  ├─ status = "Menunggu Konfirmasi"
  │  └─ tgl_booking = NOW()
  └─ Response: success/error message
```

#### History Pinjaman (`siswa/pinjaman.php`)
```
3 TAB UTAMA:

TAB 1: Menunggu Konfirmasi
├─ List booking pending yang dibuat siswa
├─ Show: tgl_booking, judul buku, penulis
└─ Tombol BATALKAN - cancel booking
   └─ calls: api/cancel_booking.php

TAB 2: Sedang Dipinjam (Active)
├─ List peminjaman status "Sedang Dipinjam"
├─ Show: tgl_pinjam, tgl_kembali, sisa hari
├─ Visual indicator: warna merah jika terlambat
└─ Info denda (jika terlambat)

TAB 3: Selesai (Completed/History)
├─ List peminjaman selesai atau ditolak
├─ Show: tgl_booking, tgl_kembali, total denda (jika ada)
└─ Sorting: newest first
```

#### Profil Siswa (`siswa/profil.php`)
```
✓ View informasi siswa (nama, kelas, NIS, dll)
✓ Form ubah password
✓ Info akun & aktivitas
```

---

## 💾 Database Schema

### Tabel 1: `kategori` (Kategori Buku)
```sql
id_kategori (INT) - Primary Key
nama_kategori (VARCHAR 50) - Unique
created_at (TIMESTAMP)

Data Default:
- Fiksi
- Non-Fiksi
- Sains & Teknologi
- Sejarah
- Biografi
- Referensi
- Seni & Budaya
```

### Tabel 2: `users` (Admin & Siswa)
```sql
id_user (INT) - Primary Key
username (VARCHAR 50) - Unique
password (VARCHAR 255) - Hashed bcrypt
nama_lengkap (VARCHAR 100)
kelas (VARCHAR 20) - Only for siswa (NULL untuk admin)
role (ENUM) - 'siswa' atau 'admin'
created_at, updated_at (TIMESTAMP)

Indexes:
- idx_username (untuk login)
- idx_role (untuk filter berdasarkan role)
```

### Tabel 3: `buku` (Katalog Buku)
```sql
id_buku (INT) - Primary Key
id_kategori (INT) - FK to kategori
judul (VARCHAR 255)
penulis (VARCHAR 100)
penerbit (VARCHAR 100)
tahun_terbit (INT)
stok (INT) - Jumlah buku tersedia
sinopsis (TEXT) - Deskripsi buku
cover_buku (VARCHAR 255) - Path ke image file
created_at, updated_at (TIMESTAMP)

Indexes:
- idx_kategori
- idx_judul (untuk search)
- idx_stok (untuk filter stok > 0)
```

### Tabel 4: `peminjaman` (Sirkulasi/Peminjaman)
```sql
id_peminjaman (INT) - Primary Key
id_user (INT) - FK to users (siswa)
id_buku (INT) - FK to buku
tgl_booking (DATETIME) - Tanggal booking dibuat
tgl_pinjam (DATETIME) - Tanggal mulai peminjaman (set saat approved)
tgl_kembali (DATETIME) - Tanggal target pengembalian (booking_date + 7 hari)
tgl_dikembalikan (DATETIME) - Tanggal buku benar-benar dikembalikan
denda (INT) - Rupiah, calculated based on tgl_kembali vs tgl_dikembalikan
status (ENUM):
  - "Menunggu Konfirmasi" (booking tahap awal)
  - "Sedang Dipinjam" (approved, siswa punya buku)
  - "Selesai" (dikembalikan tepat waktu)
  - "Ditolak" (booking ditolak admin)
  - "Terlambat" (dikembalikan lebih dari tgl_kembali)
created_at, updated_at (TIMESTAMP)

Indexes:
- idx_user (untuk query by siswa)
- idx_buku
- idx_status (untuk filter by status)
- idx_tgl_kembali (untuk query over-due books)
```

---

## 🔌 API Endpoints

Semua API endpoints ada di folder [`api/`](api/) dan dipanggil via AJAX (XMLHttpRequest/Fetch).

### Endpoint 1: `api/add_book.php`
```
Method: POST
Auth Required: YES (admin only)
Purpose: Tambah buku baru ke katalog

Request Body (Form Data):
├─ judul (string) - Required
├─ penulis (string) - Required
├─ penerbit (string)
├─ tahun_terbit (int)
├─ id_kategori (int) - Required
├─ stok (int) - Required
├─ sinopsis (text)
└─ cover_buku (file) - Image upload

Response:
├─ Success: {"status": "success", "message": "Buku berhasil ditambahkan"}
└─ Error: {"status": "error", "message": "error detail"}

Implementation: admin/katalog.php form submission
```

### Endpoint 2: `api/edit_book.php`
```
Method: POST
Auth Required: YES (admin only)
Purpose: Edit data buku existing

Request Body:
├─ id_buku (int) - Required
├─ judul (string)
├─ penulis (string)
├─ penerbit (string)
├─ tahun_terbit (int)
├─ id_kategori (int)
├─ stok (int)
├─ sinopsis (text)
└─ cover_buku (file) - Optional image upload

Response:
├─ Success: {"status": "success", "message": "Buku berhasil diubah"}
└─ Error: {"status": "error", "message": "error detail"}

Implementation: admin/katalog.php modal edit form
```

### Endpoint 3: `api/delete_book.php`
```
Method: POST
Auth Required: YES (admin only)
Purpose: Hapus buku dari katalog

Request Body:
└─ id_buku (int) - Required

Response:
├─ Success: {"status": "success", "message": "Buku berhasil dihapus"}
└─ Error: {"status": "error", "message": "error detail"}

Implementation: admin/katalog.php delete button with confirmation
```

### Endpoint 4: `api/create_booking.php`
```
Method: POST
Auth Required: YES (siswa only)
Purpose: Siswa membuat booking/pemesanan buku

Request Body:
└─ id_buku (int) - Required

Validation:
├─ Check: stok buku > 0
├─ Check: siswa tidak punya peminjaman aktif > 3
├─ Check: siswa belum booking buku yang sama (status pending)
└─ Check: BOOKING_EXPIRY_JAM = 48 jam untuk complete booking

Response:
├─ Success: {"status": "success", "message": "Booking berhasil dibuat", "id_peminjaman": 123}
└─ Error: {"status": "error", "message": "error detail"}

Side Effect:
└─ Create record di peminjaman table dengan status "Menunggu Konfirmasi"

Implementation: siswa/katalog.php modal detail booking button
```

### Endpoint 5: `api/cancel_booking.php`
```
Method: POST
Auth Required: YES (siswa only)
Purpose: Batalkan booking yang masih pending

Request Body:
└─ id_peminjaman (int) - Required

Validation:
└─ Check: status harus "Menunggu Konfirmasi" (belum approved)

Response:
├─ Success: {"status": "success", "message": "Booking berhasil dibatalkan"}
└─ Error: {"status": "error", "message": "error detail"}

Side Effect:
└─ Delete peminjaman record dari database

Implementation: siswa/pinjaman.php tab "Menunggu Konfirmasi" cancel button
```

### Endpoint 6: `api/approve_booking.php`
```
Method: POST
Auth Required: YES (admin only)
Purpose: Approve booking & mulai proses peminjaman

Request Body:
├─ id_peminjaman (int) - Required
└─ tgl_kembali (datetime) - Optional (default = now + 7 hari)

Validation:
├─ Check: status harus "Menunggu Konfirmasi"
├─ Check: stok buku masih > 0
└─ Check: booking age < BOOKING_EXPIRY_JAM (48 jam)

Response:
├─ Success: {"status": "success", "message": "Booking berhasil diapprove"}
└─ Error: {"status": "error", "message": "error detail"}

Side Effect:
├─ Update status → "Sedang Dipinjam"
├─ Set tgl_pinjam = NOW()
├─ Set tgl_kembali = tgl_kembali parameter (or now + 7 days)
├─ Decrease stok buku by 1
└─ Update updated_at

Implementation: admin/sirkulasi.php tab "Menunggu Konfirmasi" approve button
```

### Endpoint 7: `api/return_book.php`
```
Method: POST
Auth Required: YES (admin only)
Purpose: Proses pengembalian buku

Request Body:
└─ id_peminjaman (int) - Required

Validation:
└─ Check: status harus "Sedang Dipinjam"

Response:
├─ Success: {"status": "success", "message": "Buku berhasil dikembalikan", "denda": 0}
└─ Error: {"status": "error", "message": "error detail"}

Side Effect:
├─ Set tgl_dikembalikan = NOW()
├─ Calculate denda via calculate_fine() function
├─ Update status:
│  ├─ "Selesai" (jika tgl_dikembalikan <= tgl_kembali)
│  └─ "Terlambat" (jika tgl_dikembalikan > tgl_kembali)
├─ Increase stok buku by 1
├─ Update denda field
└─ Update updated_at

Implementation: admin/sirkulasi.php tab "Sedang Dipinjam" return button
```

---

## 🧮 Business Logic

File: [`helpers/business_logic.php`](helpers/business_logic.php)

### Konstanta Sistem
```php
define('TARIF_DENDA_PER_HARI', 1000);        // Rp 1.000 per hari keterlambatan
define('DURASI_PEMINJAMAN_HARI', 7);        // 7 hari default peminjaman
define('MAX_PEMINJAMAN_AKTIF', 3);          // Max 3 buku aktif per siswa
define('BOOKING_EXPIRY_JAM', 48);           // Booking hangus dalam 48 jam
```

### Fungsi 1: `calculate_fine($id_peminjaman, $tgl_kembali = null)`
```
Purpose: Hitung denda otomatis berdasarkan keterlambatan

Logic:
1. Ambil data peminjaman dari DB (jika tgl_kembali tidak diberikan)
2. Validasi status: hanya "Sedang Dipinjam" atau "Terlambat"
3. Jika tgl_kembali NULL → denda = 0
4. Hitung selisih hari: hari_sekarang - tgl_kembali
5. Jika selisih > 0 → denda = selisih_hari × TARIF_DENDA_PER_HARI
6. Else → denda = 0

Example:
- tgl_kembali: 2024-01-15
- tgl_dikembalikan: 2024-01-20
- selisih: 5 hari
- denda: 5 × Rp1.000 = Rp5.000

Usage in: api/return_book.php
```

### Fungsi 2: `validate_booking($id_user, $id_buku)`
```
Purpose: Validasi sebelum siswa membuat booking

Checks:
1. Cek stok buku > 0
2. Cek siswa tidak exceed MAX_PEMINJAMAN_AKTIF
   └─ Query: SELECT COUNT(*) FROM peminjaman 
      WHERE id_user = ? AND status IN ('Menunggu Konfirmasi', 'Sedang Dipinjam')
3. Cek siswa belum booking buku yang sama (pending/active)
   └─ Query: SELECT * FROM peminjaman 
      WHERE id_user = ? AND id_buku = ? AND status != 'Selesai'

Return: true/false atau throw Exception dengan pesan error

Usage in: api/create_booking.php
```

### Fungsi 3: `get_active_loans($id_user)`
```
Purpose: Ambil list peminjaman aktif siswa

Query:
SELECT * FROM peminjaman 
WHERE id_user = ? 
AND status IN ('Menunggu Konfirmasi', 'Sedang Dipinjam')

Return: Array of peminjaman records

Usage in: siswa/dashboard.php, siswa/pinjaman.php
```

### Fungsi 4: `get_overdue_books($id_user)`
```
Purpose: Cek buku yang terlambat untuk siswa

Query:
SELECT * FROM peminjaman 
WHERE id_user = ? 
AND status = 'Sedang Dipinjam' 
AND tgl_kembali < NOW()

Return: Array of overdue peminjaman records

Usage in: siswa/dashboard.php (notification), admin/sirkulasi.php
```

### Fungsi 5: `get_pending_fines($id_user)`
```
Purpose: Ambil total denda yang belum dibayar

Query:
SELECT SUM(denda) FROM peminjaman 
WHERE id_user = ? 
AND status IN ('Terlambat')

Return: Integer (total denda)

Usage in: siswa/dashboard.php, admin/dashboard.php
```

---

## 👥 User Credentials

### Default Test Accounts (dari seeding)

#### ADMIN / PETUGAS
```
Username: admin1
Password: password123

Nama: Admin SMEA
Role: admin
```

#### SISWA (3 akun test)
```
1. Username: siswa1
   Password: password123
   Nama: Budi Santoso
   Kelas: XII RPL A

2. Username: siswa2
   Password: password123
   Nama: Siti Nurhaliza
   Kelas: XII RPL B

3. Username: siswa3
   Password: password123
   Nama: Ahmad Wijaya
   Kelas: XII IPA
```

### Password Hashing
- Algorithm: bcrypt ($2y$10$)
- Generated via: `password_hash('password123', PASSWORD_BCRYPT)`
- Verified via: `password_verify($input_password, $hashed_password)`

---

## 🛠️ Teknologi & Library

### Backend
```
✓ PHP 7.4+ (Object-Oriented)
✓ PDO (PHP Data Objects) - Database abstraction layer
✓ Prepared Statements - Prevent SQL Injection
✓ Session Management - Server-side sessions
✓ Password Hashing - bcrypt (PHP native)
```

### Frontend
```
✓ HTML5 - Semantic markup
✓ Tailwind CSS 3.x - Utility-first CSS framework
✓ Alpine.js 3.x - Lightweight JavaScript framework (reactive components)
✓ Font Awesome 6.4.0 - Icon library
✓ SweetAlert2 - Beautiful alert dialogs
```

### Database
```
✓ MySQL 5.7+
✓ InnoDB Engine - Transaction support
✓ UTF-8mb4 Charset - Full Unicode support
✓ Indexes - Performance optimization
```

### CDN Links (loaded in pages)
```
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Alpine.js (Reactive) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x"></script>

<!-- Font Awesome Icons -->
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">

<!-- SweetAlert2 (Modal dialogs) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

---

## 📝 Development Notes

### Folder Permissions
```bash
# Ensure write permission untuk upload folder
chmod 755 /xampppp/htdocs/Glassmorphism/images/
chmod 755 /xampppp/htdocs/Glassmorphism/images/siswa/
```

### Session Configuration
File: [`config/session_handler.php`](config/session_handler.php)

```php
session_start();
$session_timeout = 30 * 60; // 30 minutes

// Check session timeout
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity']) > $session_timeout) {
    session_destroy();
    // Redirect to login with timeout message
}

$_SESSION['last_activity'] = time();
```

### Helper Functions

#### From `config/koneksi.php`
```php
// Execute query dengan prepared statement
execute_query($query, $params)

// Fetch single row
fetch_one($query, $params)

// Fetch multiple rows
fetch_all($query, $params)

// Insert/Update/Delete
execute_action($query, $params)

// Get last inserted ID
get_last_id()

// Transaction
begin_transaction()
commit_transaction()
rollback_transaction()
```

#### From `helpers/business_logic.php`
```php
calculate_fine($id_peminjaman, $tgl_kembali)
validate_booking($id_user, $id_buku)
get_active_loans($id_user)
get_overdue_books($id_user)
get_pending_fines($id_user)
```

#### From `helpers/layout.php`
```php
render_navbar($page_title, $role)       // Navbar component
render_sidebar($role)                   // Sidebar menu
render_footer()                         // Footer component
```

### Image Upload Handling
File: [`helpers/image_handler.php`](helpers/image_handler.php)

```php
// Upload cover buku
$filename = upload_book_cover($_FILES['cover_buku'])

// Validasi: 
// - File types: jpg, jpeg, png, gif
// - Max size: 5MB
// - Saved to: images/ folder

// Usage in: api/add_book.php, api/edit_book.php
```

### UI/UX Features

#### Page Transitions (Smooth 500ms fade)
```javascript
// Implemented in all 8 pages
- Page transition overlay (white fade effect)
- Navigation interception on internal links
- Floating animated background balls (glassmorphism effect)

// Removed: sessionStorage persistence (caused balls to disappear)
// Now: Fresh floating balls created on each page load for variety
```

#### Responsive Grid Layout
```css
/* Katalog books display */
grid-cols-1              /* Mobile: 1 column */
sm:grid-cols-2           /* Tablet: 2 columns */
md:grid-cols-3           /* Medium: 3 columns */
lg:grid-cols-5           /* Desktop/Large: 5 columns */
```

#### Glassmorphism Design
```css
.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.9);
}
```

---

## 🔒 Security Measures

✅ **SQL Injection Prevention**
- Prepared statements dengan parameter binding (PDO)
- No direct string concatenation in queries

✅ **XSS Prevention**
- Output encoding dengan `htmlspecialchars()`
- Input validation & sanitization

✅ **CSRF Prevention** (future enhancement)
- CSRF tokens dapat ditambahkan di forms

✅ **Session Security**
- Session timeout (30 menit)
- Session regeneration setelah login
- Secure password hashing (bcrypt)

✅ **Access Control**
- Role-based access (admin vs siswa)
- Session validation di setiap page
- API endpoint protection (auth check)

---

## 🐛 Troubleshooting

### Database Connection Error
```
Error: "Error Koneksi Database: ..."

Solution:
1. Cek MySQL running (start XAMPP Control Panel)
2. Verify credentials di config/koneksi.php
3. Create database via install.php
```

### Session/Login Issues
```
Error: Redirect loop, logout tidak bekerja

Solution:
1. Clear browser cookies
2. Check config/session_handler.php for timeout config
3. Verify auth/logout.php clearance of $_SESSION
```

### Image Upload Failed
```
Error: Cover image tidak upload

Solution:
1. Check folder permissions: chmod 755 images/
2. Check file size < 5MB
3. Check file format (jpg, png, gif, jpeg only)
```

### Floating Background Balls Disappear
```
Error: Balls hilang saat navigasi halaman

Solution:
✓ Already fixed: sessionStorage check removed
- Now creates fresh balls on every page load
```

---

## 📞 Support & Kontribusi

Untuk bug reports atau feature requests, silakan buat issue atau hubungi tim development.

---

## 📄 Lisensi

Sistem informasi E-Perpus SMEA © 2024
Developed for SMEA Digital Library Management

---

**Last Updated:** June 2, 2024
**Version:** 1.0.0
**Status:** Production Ready

---

## 🗺️ Quick Navigation Map

```
LOGIN FLOW:
index.php 
  ↓
auth/login.php (credentials check)
  ↓
session_handler.php (session set)
  ↓
Redirect by role → admin/dashboard.php atau siswa/dashboard.php

ADMIN WORKFLOW:
admin/dashboard.php (overview)
  ├→ admin/katalog.php (CRUD buku)
  │   ├→ api/add_book.php
  │   ├→ api/edit_book.php
  │   └→ api/delete_book.php
  ├→ admin/sirkulasi.php (process loans)
  │   ├→ api/approve_booking.php
  │   └→ api/return_book.php
  ├→ admin/profil.php (manage account)
  └→ auth/logout.php

SISWA WORKFLOW:
siswa/dashboard.php (overview + active loans)
  ├→ siswa/katalog.php (browse books)
  │   └→ api/create_booking.php
  ├→ siswa/pinjaman.php (loan history + cancel)
  │   └→ api/cancel_booking.php
  ├→ siswa/profil.php (manage account)
  └→ auth/logout.php

DATA FLOW:
User Input (form/button)
  ↓
JavaScript (fetch/AJAX)
  ↓
API Endpoint (api/*.php)
  ↓
business_logic.php (validation)
  ↓
koneksi.php (PDO query)
  ↓
Database (MySQL)
  ↓
Response JSON
  ↓
JavaScript (update DOM)
  ↓
User sees result
```

---

Dokumentasi lengkap dibuat untuk memudahkan understanding sistem E-Perpus SMEA secara menyeluruh.
