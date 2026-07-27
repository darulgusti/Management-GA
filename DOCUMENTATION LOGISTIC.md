# 📄 Dokumentasi Teknis Logistic Gate Pass System

Dokumen ini berisi panduan teknis mendalam mengenai arsitektur sistem, struktur database, mekanisme alur data, serta petunjuk pengembangan untuk pengembang (*developers*).

---

## 🏗️ 1. Arsitektur Sistem

Aplikasi ini menggunakan pola **Single Page Application (SPA)** yang terintegrasi langsung dengan Laravel Framework:

```
[ Browser / Frontend (Vanilla JS + SPA) ]
                    │
           HTTP Request (REST API)
                    │
                    ▼
 [ Laravel Routing (`routes/web.php`) ]
                    │
                    ▼
  [ API Controllers (`app/Http/Controllers/`) ]
  ├── DashboardController.php
  ├── GateInController.php
  ├── GateOutController.php
  └── ExportNexMoporController.php
                    │
                    ▼
    [ Eloquent Models (`app/Models/`) ]
    ├── GateIn.php
    ├── GateOut.php
    └── ExportNexMopor.php
                    │
                    ▼
          [ Database (MySQL / TiDB) ]
```

---

## 🗄️ 2. Skema & Struktur Database

Sistem ini memiliki 3 entitas utama untuk mengelola aktivitas transaksi logistik:

### A. Tabel `gate_ins` (Buku Masuk)
Pencatatan armada yang tiba di pos gerbang.

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigint` (PK, Auto Increment) | Primary Key |
| `nopol` | `string` | Nomor Polisi Kendaraan |
| `driver_name` | `string` | Nama Sopir |
| `transportir` | `string` | Nama Perusahaan Transportir |
| `destination` | `enum / string` | Tujuan (*Kirim, Export Ajinex, Umbal-umbal, Muat*) |
| `checklist_sim` | `boolean` | Status verifikasi SIM |
| `checklist_stnk` | `boolean` | Status verifikasi STNK |
| `checklist_kir` | `boolean` | Status verifikasi Surat KIR |
| `entry_time` | `datetime` | Waktu kendaraan masuk (real-time lock) |
| `status` | `string` | Status transaksi (default: `Done`) |
| `created_at` / `updated_at` | `timestamp` | Audit Trail |

### B. Tabel `gate_outs` (Buku Keluar)
Pencatatan armada keluar non-ekspor.

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigint` (PK, Auto Increment) | Primary Key |
| `nopol` | `string` | Nomor Polisi Kendaraan |
| `driver_name` | `string` | Nama Sopir |
| `do_number` | `string` | Nomor Delivery Order (DO) |
| `destination` | `string` | Tujuan Pengiriman |
| `tonnage` | `decimal/float` | Total Berat / Tonase Muatan |
| `transportir` | `string` | Nama Transportir |
| `exit_time` | `datetime` | Waktu kendaraan keluar |
| `status` | `string` | Status transaksi (default: `Done`) |

### C. Tabel `export_nex_mopors` (Export NEX / MOPOR)
Pencatatan khusus armada kontainer logistik ekspor.

| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `bigint` (PK, Auto Increment) | Primary Key |
| `mopor_number` | `string` | Nomor Identifikasi MOPOR |
| `driver_name` | `string` | Nama Sopir |
| `do_number` | `string` | Nomor Delivery Order (DO) |
| `container_number` | `string` | Nomor Kontainer |
| `seal_number` | `string` | Nomor Segel Kontainer |
| `destination` | `string` | Negara / Pelabuhan Tujuan Ekspor |
| `tonnage` | `decimal/float` | Berat Tonase |
| `transportir` | `string` | Nama Transportir |
| `exit_time` | `datetime` | Waktu Keberangkatan |
| `status` | `string` | Status transaksi (default: `Done`) |

---

## ⚡ 3. Spesifikasi API

### 1. Dashboard
- **GET `/api/dashboard`**
  - Mengembalikan statistik total kendaraan masuk, keluar, dan ekspor hari ini.

### 2. Buku Masuk (`GateIn`)
- **GET `/api/gate-in`**: Mengambil seluruh daftar kendaraan masuk.
- **POST `/api/gate-in`**: Menyimpan data kendaraan masuk baru.
- **PUT/PATCH `/api/gate-in/{id}`**: Memperbarui data kendaraan masuk.
- **DELETE `/api/gate-in/{id}`**: Menghapus satu data kendaraan masuk.
- **POST `/api/gate-in/bulk-delete`**: Hapus banyak data sekaligus berdasarkan array ID `{ ids: [...] }`.
- **POST `/api/gate-in/{id}/status`**: Mengubah status data transaksi.

### 3. Buku Keluar (`GateOut`)
- **GET `/api/gate-out`**: Mengambil daftar transaksi keluar.
- **POST `/api/gate-out`**: Menambah transaksi keluar baru.
- **POST `/api/gate-out/bulk-delete`**: Menghapus massal transaksi keluar.

### 4. Export NEX / MOPOR (`ExportNexMopor`)
- **GET `/api/export-nex-mopor`**: Mengambil daftar transaksi ekspor kontainer.
- **POST `/api/export-nex-mopor`**: Menambah data ekspor baru.
- **POST `/api/export-nex-mopor/bulk-delete`**: Menghapus massal data ekspor.

---

## 🔄 4. Alur Bisnis Utama

Detail alur bisnis dan diagram interaksi lengkap per halaman dapat dilihat pada file:
- 📐 **[flowchart.md](file:///c:/laragon/www/logistic-gate-pass/flowchart.md)** - Diagram alur per halaman.
- 🎯 **[stpd.md](file:///c:/laragon/www/logistic-gate-pass/stpd.md)** - Analisis problem-solving STPD & dampak operasional.

### Prinsip Utama Sistem:
1. **Auto Real-Time Time Lock**: Waktu kedatangan/keberangkatan terkunci otomatis saat tombol simpan diklik tanpa bisa diubah manual oleh operator.
2. **Direct Done Status**: Menghilangkan tahap *Draft*, setiap pengisian form valid langsung tersimpan dengan status *Done*.
3. **Standardized Dropdown Input**: Dropdown terpusat mencegah kesalahan pengetikan nama tujuan/transportir.

---

## 🛠️ 5. Panduan Pengembangan (Developer Guide)

### Menambahkan Fitur Baru atau Model Baru:
1. Buat migration dan model baru:
   ```bash
   php artisan make:model NamaModel -m
   ```
2. Buat Resource Controller:
   ```bash
   php artisan make:controller NamaController --api
   ```
3. Daftarkan rute pada `routes/web.php` di dalam grup `Route::prefix('api')`.

---

## ❓ FAQ & Troubleshooting

- **Data waktu di Excel tidak valid/format text?**
  - Ekspor laporan telah disesuaikan agar tanggal & jam menggunakan tipe data sel asli (*Native Datetime*), pastikan library ekspor mengonversi string ISO menjadi format Excel DateTime.
- **Bagaimana cara mereset data dummy/testing?**
  - Jalankan `php artisan migrate:fresh --seed` untuk mengulang dari awal data seeder bawaan (`database/seeders/LogisticSeeder.php`).
