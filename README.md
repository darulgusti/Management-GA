# 🇮🇩 GA & Logistic Management System

Platform web terpadu untuk manajemen **General Affairs (GA)** dan **Logistic Gate Pass System** berbasis **Pure PHP** modern. Dirancang untuk mengelola **Buku Tamu Digital**, **Peminjaman Barang & Kunci**, serta **Lalu Lintas Kendaraan & Kontainer Logistik** secara *real-time*, dilengkapi dengan **Tanda Tangan Digital**, **Laporan Excel Multi-Sheet**, **Fitur Cetak/Print Mode**, dan siap di-deploy ke **Vercel** & **TiDB Cloud**.

---

## 🌟 Fitur Utama

### 1. 🚚 Logistic Gate Pass System (`logistic.php`)
- **Buku Masuk (Gate In):** Pencatatan armada masuk (Nopol, Sopir, Transportir, Tujuan Kirim/Export Ajinex/Umbal-umbal/Muat, Checklist SIM/STNK/KIR).
- **Buku Keluar (Gate Out):** Pencatatan armada keluar non-ekspor (Nopol, Sopir, No. DO, Tujuan, Tonase, Transportir).
- **Export NEX / MOPOR:** Pencatatan khusus armada kontainer logistik ekspor (No. MOPOR, Sopir, No. DO, No. Kontainer, No. Segel, Tujuan, Tonase).
- **Hak Akses Khusus:**
  - **Staf Secom (`secom`):** Memiliki akses Penuh (View & Input/Simpan/Hapus Data).
  - **Manager (`manager`):** Memiliki akses **Read-Only / Hanya Lihat Data** (Tidak dapat menambah/mengedit/menghapus data logistik).

### 2. 📖 Buku Tamu Digital (`guest.php` & `guest_form.php`)
- **Formulir Mandiri Tamu:** Tamu mengisi Nama, Instansi, Kategori, Tujuan, Orang Ditemui, No. Kartu Visitor.
- **Tanda Tangan Digital:** Canvas HTML5 untuk pembubuhan TTD digital.
- **Monitoring Tamu Aktif & Check-out:** Staf Secom mengelola tamu aktif di lokasi dan proses check-out.

### 3. 🔑 Peminjaman Barang & Kunci (`borrowing.php` & `borrowing_form.php`)
- **Peminjaman Aset GA:** Pencatatan peminjam, departemen, barang/kunci, kondisi awal & kondisi kembali.
- **Monitoring Peminjaman & Pengembalian:** Pantau status barang yang sedang dipinjam & proses pengembalian.

### 4. 📊 Dashboard & Rekap Laporan GA (`dashboard.php` & `report.php`)
- **Dashboard Real-Time:** Monitoring total tamu, peminjaman, dan armada logistik masuk/keluar harian.
- **Rekap Laporan GA:** Filter tanggal & kategori (Semua, Buku Tamu, Peminjaman, Gate In, Gate Out, Export NEX).
- **Ekspor Excel (.xls):** Ekspor multi-worksheet format Excel siap olah.
- **Fitur Cetak / Print Mode (A4 Landscape):** Cetak laporan rapi bebas elemen UI/tombol web.

---

## 🛠️ Teknologi yang Digunakan

- **Backend:** Pure PHP (PHP 7.4 / 8.x)
- **Database:** MySQL / MariaDB / TiDB Cloud (MySQL Compatible)
- **Frontend:** HTML5, Vanilla CSS3 (Custom Design System Merah Putih 🇮🇩), Vanilla JavaScript
- **Deployment:** Vercel Serverless (`vercel-php`) & TiDB Cloud SSL

---

## 🔑 Akun Default (Seeder)

| Peran (Role) | Email | Password | Akses Logistik |
| :--- | :--- | :--- | :--- |
| **Staf Secom** | `secom@ga.com` | `secom123` | **Full Access** (Bisa Input, Edit, Hapus Data Logistik & Operasional) |
| **Manager** | `admin@ga.com` | `admin123` | **Read-Only** (Hanya Bisa Melihat Tabel Data Logistik, Full GA, User & Archive) |

---

## ⚠️ Catatan Penting
Perubahan kode saat ini **belum di-push ke GitHub** sesuai instruksi.
