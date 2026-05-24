# 🚀 E-Perpus SMEA - Installation Guide

Panduan instalasi cepat E-Perpus SMEA (Perpustakaan Digital SMK).

## Prerequisites

- **XAMPP** sudah terinstall (Apache + MySQL + PHP 8.0+)
- **PHP 8.0+** dengan extensions: `pdo_mysql`, `gd`, `mbstring`
- **MySQL 5.7+**

## Quick Installation (1 Klik Setup)

### Step 1: Copy Project ke htdocs
```bash
cp -r Perpustakaan/ c:\xampp\htdocs\
```

### Step 2: Nyalakan XAMPP
1. Buka XAMPP Control Panel
2. Start **Apache** & **MySQL**

### Step 3: Jalankan Installer
1. Buka browser → `http://localhost/Perpustakaan/install.php`
2. Klik tombol **✅ Mulai Instalasi**
3. Tunggu sampai selesai

### Step 4: Login ke Aplikasi
- Buka `http://localhost/Perpustakaan/`
- Gunakan credentials:

| Role  | Username | Password |
|-------|----------|----------|
| Siswa | `siswa`  | `password` |
| Admin | `admin`  | `password` |

---

## Database Structure

```
Database: perpustakaan_smea

Tabel:
├── kategori         (7 kategori buku)
├── users            (2 test users: siswa + admin)
├── buku             (8 sample books)
└── peminjaman       (transaction history)
```

## Fitur Installer

✅ Otomatis buat database & tables
✅ Otomatis seed kategori & sample data
✅ Otomatis buat test users
✅ Error handling & troubleshooting guide
✅ One-click setup (tanpa manual SQL)

## Test Credentials

### Siswa
- **Username:** `siswa`
- **Password:** `password`
- **Role:** Student
- **Kelas:** XII RPL 1

### Admin
- **Username:** `admin`
- **Password:** `password`
- **Role:** Librarian/Admin
- **Kelas:** N/A

---

## Folder Structure

```
Perpustakaan/
├── install.php              ← Installer page
├── index.php                ← Main router
├── Readme                   ← Project requirements
├── INSTALL.md               ← Panduan ini
│
├── config/
│   ├── koneksi.php          ← PDO connection
│   └── session_handler.php  ← Session management
│
├── helpers/
│   ├── business_logic.php   ← Business functions
│   ├── image_handler.php    ← Image upload & conversion
│   └── layout.php           ← UI components
│
├── auth/
│   ├── login.php            ← Login page
│   └── logout.php           ← Logout handler
│
├── siswa/                   ← Student pages
│   ├── dashboard.php
│   ├── katalog.php
│   ├── pinjaman.php
│   └── profil.php
│
├── admin/                   ← Admin pages
│   ├── dashboard.php
│   ├── sirkulasi.php
│   ├── katalog.php
│   └── profil.php
│
├── api/                     ← API endpoints
│   ├── create_booking.php
│   ├── cancel_booking.php
│   ├── approve_booking.php
│   ├── return_book.php
│   ├── add_book.php
│   ├── edit_book.php
│   └── delete_book.php
│
└── images/
    └── buku/                ← Book cover storage (auto-created)
```

---

## Features

### 👤 Student (Siswa)
- ✅ Login & Dashboard
- ✅ Browse Catalog + Search
- ✅ Booking Books (max 3 active loans)
- ✅ Track Loans (3 tabs: Waiting, Active, History)
- ✅ Auto fine calculation (Rp 1.000/day late)
- ✅ Update Profile & Change Password

### 👨‍💼 Admin (Petugas)
- ✅ Dashboard with Statistics & Chart.js
- ✅ Circulation Desk (Approve Bookings)
- ✅ Process Returns (with real-time fine)
- ✅ Manage Books (Add/Edit/Delete with image upload)
- ✅ Update Profile & Change Password

### 🎨 Design
- ✅ Glassmorphism UI
- ✅ Responsive Mobile-First
- ✅ Floating animated background
- ✅ Tailwind CSS v4
- ✅ Alpine.js interactions

---

## Troubleshooting

### Error: "Koneksi MySQL gagal"
**Solution:**
1. Pastikan MySQL/XAMPP sedang berjalan
2. Check credentials di `install.php` (baris 13-16):
   ```php
   $db_host = 'localhost';   // Atau IP server
   $db_user = 'root';        // Username MySQL
   $db_pass = '';            // Password MySQL (kosong default)
   $db_name = 'perpustakaan_smea';
   ```

### Error: "Database sudah ada"
**Solution:**
- Install otomatis akan DROP & recreate database
- Klik "Coba Lagi" atau reload halaman

### Images tidak tampil di admin katalog
**Solution:**
1. Buat folder `/images/buku/` di root project:
   ```bash
   mkdir Perpustakaan/images
   mkdir Perpustakaan/images/buku
   chmod 777 Perpustakaan/images/buku
   ```
2. Pastikan PHP punya permission write

### Session timeout error
**Solution:**
- Timeout default: 30 menit
- Edit di `config/session_handler.php` (baris 7):
  ```php
  define('SESSION_TIMEOUT', 30); // minutes
  ```

---

## Manual Database Setup (Jika installer gagal)

```bash
# Import schema
mysql -u root -p perpustakaan_smea < database_schema.sql
```

Atau manual SQL:
```sql
-- Buat database
CREATE DATABASE perpustakaan_smea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan_smea;

-- Lihat file: database_schema.sql untuk full DDL
```

---

## Performance Tips

1. **Database Indexing:** ✓ Sudah ada (id_user, id_buku, status, tgl_kembali)
2. **Image Optimization:** PNG otomatis convert ke JPG (max 2MB)
3. **Stock Lock:** FOR UPDATE digunakan untuk prevent overselling
4. **Session Timeout:** 30 min inactivity auto-logout

---

## Support & Contact

Jika ada pertanyaan atau error:
1. Check error message di installer
2. Review `Readme` untuk requirements detail
3. Inspect browser console (F12) untuk JS errors
4. Check server logs di XAMPP

---

## Version Info

- **E-Perpus SMEA v1.0**
- **PHP:** 8.0+
- **MySQL:** 5.7+
- **Last Updated:** May 24, 2026

---

**Happy Reading! 📚**
