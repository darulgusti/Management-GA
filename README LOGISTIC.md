# 🚛 Logistic Gate Pass System

System Manajemen Gate Pass & Logistik berbasis web untuk pencatatan dan pemantauan lalu lintas kendaraan (masuk & keluar) serta kontainer logistik ekspor secara *real-time*.

---

## 📌 Gambaran Umum

**Logistic Gate Pass System** dirancang untuk mempercepat dan meningkatkan akurasi operasional di pintu gerbang (*gate pos*) logistik. Sistem ini memangkas waktu input data manual dari beberapa menit menjadi kurang dari **45 detik**, mencegah manipulasi waktu kedatangan/keberangkatan sopir, serta memberikan visibilitas lalu lintas kendaraan secara terpusat.

---

## ✨ Fitur Utama

- 📥 **Buku Masuk (Gate In)**
  - Pencatatan kendaraan masuk (Nopol, Sopir, Transportir, Pilihan Tujuan).
  - Pilihan tujuan terstandardisasi (*Kirim, Export Ajinex, Umbal-umbal, Muat*).
  - Checklist kelengkapan dokumen berkas driver (SIM, STNK, KIR).
  - *Real-time live clock* untuk mengunci waktu masuk secara presisi.

- 📤 **Buku Keluar (Gate Out)**
  - Pencatatan armada keluar non-ekspor.
  - Input No. DO, Tujuan Pengiriman, Tonase, Transportir, dan Jam Keluar.

- 🚢 **Export NEX / MOPOR**
  - Manajamen pencatatan khusus kontainer & armada ekspor.
  - Input detail MOPOR, No. Kontainer, No. Segel, No. DO, Tonase, & Transportir.

- 📊 **Dashboard Real-Time**
  - Ringkasan statistik jumlah kendaraan masuk, keluar, dan ekspor harian.
  - Tabel pemantauan aktivitas kendaraan terkini.

- 📑 **Export Laporan (Excel Ready)**
  - Ekspor data laporan harian/bulanan dengan format data asli (Date, Time, Numeric) siap olah.

- ⚡ **Tindakan Massal (Bulk Operations)**
  - Fitur hapus massal (*bulk delete*) dan pengubahan status transaksi secara instan.

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 10.x (PHP 8.1+)
- **Frontend**: SPA (Single Page Application) dengan HTML5, Vanilla JavaScript, & Tailwind CSS / Bootstrap
- **Database**: MySQL / TiDB / MariaDB
- **API Architecture**: RESTful API Controller

---

## 🚀 Panduan Instalasi

### 1. Prasyarat Sistem
- PHP >= 8.1
- Composer >= 2.x
- Web Server (Apache / Nginx / Laragon)
- MySQL / TiDB Database

### 2. Langkah-Langkah Setup

1. **Clone Repositori / Buka Project**
   ```bash
   cd c:/laragon/www/logistic-gate-pass
   ```

2. **Install Depedensi Composer**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment (`.env`)**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Sesuaikan konfigurasi database pada `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=logistic_gate_pass
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi & Seeder Database**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser di `http://127.0.0.1:8000` (atau via Laragon `http://logistic-gate-pass.test`).

---

## 🔌 Ringkasan API Endpoints

Semua endpoint API menggunakan prefix `/api`:

| Method | Endpoint | Keterangan |
| :--- | :--- | :--- |
| `GET` | `/api/dashboard` | Mengambil data statistik & ringkasan dashboard |
| `GET/POST` | `/api/gate-in` | Ambil daftar / Buat data Buku Masuk baru |
| `POST` | `/api/gate-in/bulk-delete` | Hapus massal data Buku Masuk |
| `GET/POST` | `/api/gate-out` | Ambil daftar / Buat data Buku Keluar baru |
| `POST` | `/api/gate-out/bulk-delete` | Hapus massal data Buku Keluar |
| `GET/POST` | `/api/export-nex-mopor` | Ambil daftar / Buat data Export NEX/MOPOR |
| `POST` | `/api/export-nex-mopor/bulk-delete` | Hapus massal data Export NEX/MOPOR |

---

## 📚 Dokumentasi Lanjutan

- 📄 **[DOCUMENTATION.md](file:///c:/laragon/www/logistic-gate-pass/DOCUMENTATION.md)** - Arsitektur teknis, skema database, dan panduan pengembang.
- 📐 **[flowchart.md](file:///c:/laragon/www/logistic-gate-pass/flowchart.md)** - Alur kerja dan diagram proses per halaman.
- 🎯 **[stpd.md](file:///c:/laragon/www/logistic-gate-pass/stpd.md)** - Metodologi See-Think-Plan-Do & evaluasi lapangan.

---

## 📝 Lisensi

Aplikasi ini dikembangkan untuk kebutuhan manajemen internal pos logistik gate pass.
