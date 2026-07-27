# 🏬 General Affair & Logistic Management System (Management-GA)

A comprehensive, web-based management system designed for operational General Affairs (GA) and Security/Logistic teams. Built with Native PHP, PDO MySQL, and Vanilla CSS/JS.

---

## 📌 Deskripsi Sistem

**Management-GA** menyatukan seluruh operasional pencatatan pos keamanan, penerimaan tamu, peminjaman aset/kunci, serta manajemen lalu lintas armada logistik (*Gate In*, *Gate Out*, dan *Export NEX/MOPOR*) ke dalam satu platform terpusat yang cepat, responsif, dan aman.

Sistem ini mendukung **Role-Based Access Control (RBAC)**:
- 🛡️ **Staf Secom / Keamanan:** Memiliki hak akses penuh untuk melakukan penginputan, pengeditan, serta penghapusan data operasional harian.
- 👁️ **Manager:** Memiliki hak akses *Read-Only* (hanya melihat data), melihat tabel pemantauan real-time, rekap statistik, serta melakukan export laporan ke Excel.

---

## ✨ Fitur Utama & Modul

### 1. 📊 Dashboard Utama (`dashboard.php`)
- Ringkasan kartu statistik real-time (Tamu Aktif, Peminjaman Barang, Armada Masuk, Armada Keluar, Kontainer Ekspor).
- Tabel monitoring cepat untuk aktivitas transaksi terkini.
- Akses navigasi langsung ke setiap modul operasional.

### 2. 👥 Buku Tamu Digital (`guest.php`, `guest_form.php`)
- Formulir pendaftaran tamu digital interaktif.
- Fitur **Tanda Tangan Digital (Digital Signature Canvas)**.
- Pencatatan Kategori Tamu, Nomor Kartu Visitor, Tujuan, dan Orang yang Ditemui.
- Pemisahan tabel **Tamu Masih di Lokasi (Aktif)** dan **Riwayat Tamu Selesai**.
- Export data tamu ke format Excel (`export_guest.php`).

### 3. 🔑 Peminjaman Barang & Kunci (`borrowing.php`, `borrowing_form.php`)
- Pencatatan peminjaman barang inventaris kantor & kunci ruangan.
- Pelacakan kondisi barang (Baik / Rusak) serta status peminjaman (*Dipinjam* vs *Dikembalikan*).
- Pemisahan tabel **Peminjaman Aktif** dan **Riwayat Peminjaman Selesai**.
- Export data peminjaman ke format Excel (`export_borrowing.php`).

### 4. 🚚 Logistik Gate Pass System (`logistic.php`)
- Menyajikan **ketiga tabel pemantauan logistik** (Buku Masuk, Buku Keluar, dan Export NEX / NOPOR) secara terpadu dalam 1 halaman terpusat.
- **Buku Masuk (Gate In):** Nopol, Driver, Visitor Number (Opsional), Antre Number (Opsional), Jenis SIM (SIM A, SIM B, SIM B2), STNK, KIR, Transportir (Opsional), dan Pemilih Waktu.
- **Buku Keluar (Gate Out):** Nopol, Driver, No. DO, Tujuan, Tonase, Transportir (Opsional), dan Pemilih Waktu.
- **Export NEX / NOPOR:** No. NOPOR, Driver, No. DO, No. Kontainer, No. Segel, Tujuan, Tonase, Transportir (Opsional), dan Pemilih Waktu.

### 5. 📑 Rekap Laporan GA & Logistik (`report.php`, `export_report.php`)
- Penarikan laporan gabungan dengan filter rentang tanggal (Tanggal Mulai s/d Tanggal Selesai) dan pilihan modul.
- Fitur cetak (*print*) dan **Export Laporan ke Excel Ready** secara komprehensif.

### 8. 📦 Arsip Data (`archives/`)
- Manajemen dan pemantauan arsip dokumen serta data lama sistem General Affair.

---

## 🛠️ Teknologi & Arsitektur

- **Language:** PHP 7.4+ (Pure Native, No Heavy Framework Dependency)
- **Database:** MySQL / MariaDB (Driver PDO with Prepared Statements)
- **Styling:** Modern Vanilla CSS (Design Tokens, Glassmorphism, CSS Grid & Flexbox, Responsive Mobile & Desktop Layout)
- **Interactive UI:** Vanilla JavaScript & SignaturePad
- **Deployment Ready:** Compatible with Laragon, XAMPP, Apache/Nginx, Vercel, and Aiven Cloud Database.

---

## 🗄️ Struktur Database (`database.sql`)

Tabel utama yang digunakan oleh sistem:
1. `users` — Data akun login pengguna (`username`, `password`, `name`, `role`).
2. `guest_logs` — Transaksi pencatatan tamu digital & stempel waktu.
3. `borrowing_logs` — Transaksi peminjaman barang inventaris & kunci.
4. `logistic_gate_ins` — Transaksi armada masuk gerbang (*Gate In*).
5. `logistic_gate_outs` — Transaksi armada keluar gerbang (*Gate Out*).
6. `logistic_export_nex_mopors` — Transaksi kontainer ekspor (*Export NEX/MOPOR*).

---

## 🚀 Panduan Instalasi Lokal

1. **Clone / Copy Repositori**
   Simpan folder project ke direktori server lokal (misalnya `C:\laragon\www\management-GA`).

2. **Import Database**
   - Buka phpMyAdmin / HeidiSQL.
   - Buat database baru dengan nama `management_ga`.
   - Import file [`database.sql`](file:///c:/laragon/www/management-GA/database.sql) ke database tersebut.

3. **Konfigurasi Koneksi Database**
   Sesuaikan kredensial database di [`config/database.php`](file:///c:/laragon/www/management-GA/config/database.php):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'management_ga');
   ```

4. **Akses Aplikasi**
   Buka browser dan akses:
   `http://localhost/management-GA/login.php`

---

## 🔒 Akun Login Default

| Role | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Staf Secom** | `secom` | `secom123` | Full Access (Input, Edit, Hapus Data) |
| **Manager** | `manager` | `manager123` | View Only (Monitoring Dashboard & Export Laporan) |

---

## 📄 Lisensi & Hak Cipta
© 2026 Management-GA System. Hak cipta dilindungi undang-undang.
