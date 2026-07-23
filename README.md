# 🇮🇩 GA Management System (General Affairs Digital Platform)

GA Management System adalah platform web manajemen **General Affairs (GA)** berbasis **Pure PHP** modern yang dirancang untuk mengelola **Buku Tamu Digital** dan **Peminjaman Barang & Kunci Inventaris** secara *real-time*, dilengkapi dengan **Tanda Tangan Digital**, **Laporan Excel**, **Fitur Cetak/Print**, serta siap dideploy ke **Vercel** dan **TiDB Cloud**.

---

## 🌟 Fitur Utama

### 1. 📖 Buku Tamu Digital (`Guest Book`)
- **Formulir Mandiri Tamu:** Tamu dapat mengiri data diri (Nama, Instansi, Kategori Kunjungan, Tujuan, Orang yang Ditemu, No. Kartu Visitor).
- **Tanda Tangan Digital:** Integrasi canvas HTML5 untuk pembubuhan tanda tangan langsung dari perangkat touchscreen/mouse.
- **Monitoring Tamu Aktif:** Halaman khusus petugas (Secom) untuk memantau tamu yang sedang berada di area pabrik/kantor.
- **Proses Check-out:** Sekali klik untuk mencatat waktu keluar tamu secara akurat.
- **Arsip & Riwayat Kunjungan:** Pencarian cepat dan riwayat lengkap tamu yang sudah keluar.

### 2. 🔑 Peminjaman Barang & Kunci (`Item & Key Borrowing`)
- **Form Peminjaman Barang:** Peminjaman barang/aset GA dan kunci ruangan secara digital.
- **Pencatatan Kondisi Barang:** Mengisi kondisi awal barang saat dipinjam dan kondisi akhir saat dikembalikan.
- **Monitoring Peminjaman Aktif:** Pantau barang yang belum dikembalikan (*Status: Borrowed*).
- **Proses Pengembalian (Return):** Fitur pengembalian barang dengan pencatatan waktu dan kondisi barang.

### 3. 📊 Rekapitulasi & Laporan (`Reports & Analytics`)
- **Dashboard Interaktif:** Ringkasan statistik statistik harian/bulanan untuk Manager & Staf Secom.
- **Filter Laporan:** Filter berdasarkan rentang tanggal (*Date Range*) dan modul tertentu (Tamu / Peminjaman / Semua).
- **Ekspor Excel (.xls):** Ekspor rekapitulasi data ke format Excel siap pakai.
- **Fitur Cetak / Print Rapi:** Tampilan cetak yang telah dioptimalkan (A4 Landscape) bebas dari navigasi/elemen tombol web.

### 4. 🔐 Hak Akses & Keamanan (`Role-Based Auth`)
- **Multi-Role User:**
  - `Manager`: Akses penuh ke Dashboard, Laporan, Pengaturan User, dan Pengarsipan Data.
  - `Secom`: Akses ke Buku Tamu, Peminjaman Barang, dan Laporan.
- **Keamanan Password:** Enkripsi password menggunakan `BCRYPT`.
- **Proteksi Akses:** Session handling aman dengan proteksi per peran (*Role Authorization*).

---

## 🛠️ Teknologi yang Digunakan

- **Backend:** Pure PHP (PHP 7.4 / 8.x) - *Tanpa Heavy Framework*
- **Database:** MySQL / MariaDB / TiDB Cloud (MySQL Compatible)
- **Frontend:** HTML5, Vanilla CSS3 (Custom Design System Tema Merah Putih 🇮🇩), Vanilla JavaScript (Signature Pad & Interaktivitas UI)
- **Ekspor Data:** Pure HTML Table Spreadsheet Format (.xls)
- **Deployment Support:** Vercel Serverless (`vercel-php`) & TiDB Cloud SSL

---

## 📂 Struktur Direktori Project

```text
management-GA/
├── api/
│   └── index.php              # Entry point untuk Vercel Serverless Deployment
├── config/
│   └── database.php           # Konfigurasi PDO Database (Local & Cloud TiDB)
├── css/
│   └── style.css              # Custom Design System (Merah Putih Theme & Print CSS)
├── includes/
│   ├── auth_check.php         # Auth Guard, Session Manager, & Pagination Helper
│   ├── excel_helper.php       # Utility Generator Excel Spreadsheet
│   ├── header.php             # Komponen Header & Navbar
│   ├── sidebar.php            # Komponen Sidebar Navigasi
│   └── footer.php             # Komponen Footer
├── js/                        # Script interaksi UI & Signature Canvas
├── archives/                  # Direktori simpan arsip data
├── borrowing.php              # Manajer & Riwayat Peminjaman Barang
├── borrowing_form.php         # Form Peminjaman Barang (Public/Staf)
├── dashboard.php              # Dashboard Utama Manager & Secom
├── database.sql               # Skema Database & Data Awal (Seeder)
├── export_report.php          # Handler Ekspor Laporan Excel
├── guest.php                  # Manajer & Riwayat Buku Tamu
├── guest_form.php             # Form Buku Tamu Digital (Public)
├── index.php                  # Portal Utama Layanan GA
├── login.php                  # Halaman Login Sistem
├── logout.php                 # Handler Logout
├── report.php                 # Rekap Laporan & Filter Tanggal
├── settings.php               # Kelola Pengguna & Pengarsipan Data
├── vercel.json                # Konfigurasi Routing Deployment Vercel
├── README.md                  # Dokumentasi Umum Project
└── DOCUMENTATION.md           # Arsitektur & Panduan Lengkap Sistem
```

---

## 🚀 Panduan Instalasi Lokal (Laragon / XAMPP)

### Prasyarat
- PHP 7.4 atau PHP 8.x
- MySQL / MariaDB Server (via Laragon / XAMPP)
- Git CLI

### Langkah Instalasi

1. **Clone Repository:**
   ```bash
   cd C:\laragon\www   # atau C:\xampp\htdocs
   git clone https://github.com/darulgusti/Management-GA.git
   cd Management-GA
   ```

2. **Setup Database:**
   - Buka **phpMyAdmin** / **HeidiSQL** / **DBeaver**.
   - Buat database baru dengan nama `test` (atau nama lain pilihan Anda).
   - Import file `database.sql` yang ada di dalam folder project.

3. **Konfigurasi Koneksi Database (Jika Diperlukan):**
   - Buka file `config/database.php`.
   - Sesuaikan `host`, `db`, `user`, dan `pass` dengan MySQL lokal Anda jika tidak menggunakan bawaan Laragon.

4. **Jalankan Aplikasi:**
   - Akses via browser: `http://localhost/Management-GA` atau `http://management-ga.test`.

---

## 🔑 Akun Default (Seeder)

| Peran (Role) | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Manager** | `admin@ga.com` | `admin123` | Akses Penuh (Dashboard, Laporan, User Management, Archive) |
| **Staf Secom** | `secom@ga.com` | `secom123` | Operasional Buku Tamu, Peminjaman Barang, Laporan |

---

## ☁️ Deployment ke Vercel & TiDB Cloud

Project ini sudah dilengkapi konfigurasi `vercel.json` dan dukungan SSL TiDB Cloud otomatis di `config/database.php`.

### Environment Variables di Vercel (Opsional):
- `DB_HOST`: Host TiDB Cloud Anda
- `DB_NAME`: Nama Database
- `DB_USER`: Username Database
- `DB_PASS`: Password Database
- `DB_PORT`: `4000`

---

## 📝 Lisensi & Pengembang

Dikembangkan oleh **Team General Affairs (GA)**. Bebas digunakan dan dikembangkan untuk keperluan operasional perusahaan.
