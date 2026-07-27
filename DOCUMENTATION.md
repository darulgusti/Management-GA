# 📚 Dokumentasi Teknis GA & Logistic Management System

Dokumen ini berisi panduan arsitektur teknis, struktur database, hak akses pengguna, serta alur sistem terpadu **GA Management System & Logistic Gate Pass**.

---

## 🗄️ Skema Database Tambahan (Logistic Gate Pass)

### 1. Tabel `logistic_gate_ins` (Buku Masuk Logistik)
| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `INT` (PK, Auto Increment) | Primary Key |
| `nopol` | `VARCHAR(50)` | Nomor Polisi Kendaraan |
| `driver_name` | `VARCHAR(255)` | Nama Sopir |
| `transportir` | `VARCHAR(255)` | Perusahaan Transportir |
| `destination` | `VARCHAR(100)` | Tujuan (*Kirim, Export Ajinex, Umbal-umbal, Muat*) |
| `checklist_sim` | `TINYINT(1)` | Status Verifikasi SIM |
| `checklist_stnk` | `TINYINT(1)` | Status Verifikasi STNK |
| `checklist_kir` | `TINYINT(1)` | Status Verifikasi Surat KIR |
| `entry_time` | `DATETIME` | Waktu Kedatangan |
| `status` | `VARCHAR(50)` | Status Transaction (default: `Done`) |

### 2. Tabel `logistic_gate_outs` (Buku Keluar Logistik)
| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `INT` (PK, Auto Increment) | Primary Key |
| `nopol` | `VARCHAR(50)` | Nomor Polisi Kendaraan |
| `driver_name` | `VARCHAR(255)` | Nama Sopir |
| `do_number` | `VARCHAR(100)` | Nomor Delivery Order (DO) |
| `destination` | `VARCHAR(255)` | Tujuan Pengiriman |
| `tonnage` | `DECIMAL(10,2)` | Berat Tonase Muatan |
| `transportir` | `VARCHAR(255)` | Perusahaan Transportir |
| `exit_time` | `DATETIME` | Waktu Keberangkatan |
| `status` | `VARCHAR(50)` | Status (default: `Done`) |

### 3. Tabel `logistic_export_nex_mopors` (Export Kontainer Ekspor)
| Field | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `INT` (PK, Auto Increment) | Primary Key |
| `mopor_number` | `VARCHAR(100)` | Nomor Identifikasi MOPOR |
| `driver_name` | `VARCHAR(255)` | Nama Sopir |
| `do_number` | `VARCHAR(100)` | Nomor DO |
| `container_number` | `VARCHAR(100)` | Nomor Kontainer |
| `seal_number` | `VARCHAR(100)` | Nomor Segel Kontainer |
| `destination` | `VARCHAR(255)` | Tujuan Ekspor |
| `tonnage` | `DECIMAL(10,2)` | Berat Tonase |
| `transportir` | `VARCHAR(255)` | Nama Transportir |
| `exit_time` | `DATETIME` | Waktu Keberangkatan |
| `status` | `VARCHAR(50)` | Status (default: `Done`) |

---

## 👥 Matriks Hak Akses Pengguna Logistik

| Fitur / Halaman | Tamu (Public) | Staf Secom (`secom`) | Manager (`manager`) |
| :--- | :---: | :---: | :---: |
| Akses Menu Logistik dari Portal | Harus Login | ✅ Langsung ke Form/Tabel | ✅ Langsung ke Tabel Read-Only |
| Input Data Gate In / Out / Export | ❌ | ✅ BISA INPUT | ❌ TIDAK BISA INPUT |
| Hapus Data Logistik | ❌ | ✅ BISA HAPUS | ❌ TIDAK BISA HAPUS |
| Lihat Tabel & Monitoring Logistik | ❌ | ✅ | ✅ (Read-Only) |
| Dashboard Monitoring Logistik | ❌ | ✅ | ✅ |
| Rekap Laporan & Export Excel | ❌ | ✅ | ✅ |
| Pengarsipan & Reset Data Logistik | ❌ | ❌ | ✅ (Manager Only) |
