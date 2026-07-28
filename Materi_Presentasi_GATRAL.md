# 📊 Laporan & Materi Presentasi Sistem GA Management

Berikut adalah ringkasan, analisis, dan alur sistem yang dirancang khusus untuk menjelaskan perbandingan sebelum (existing) dan sesudah implementasi program digitalisasi di area pos keamanan (SECOM).

---

## 1. Latar Belakang & Permasalahan Awal (History) di Security
Dari hasil pengamatan awal di lapangan, permasalahan utama atau akar kendala (root cause) yang dihadapi oleh petugas keamanan (SECOM) adalah proses input data yang masih dilakukan secara manual menggunakan buku tulis fisik. Proses input manual yang konvensional ini kemudian memicu rentetan kendala operasional lainnya dalam rutinitas harian di pos gerbang:
* **Antrean di Gerbang:** Saat jam sibuk (pagi/sore) atau saat banyak armada/tamu masuk, terjadi penumpukan panjang karena lamanya durasi petugas saat mencatat Nopol, nama sopir, identitas, dan keperluan satu per satu ke dalam buku.
* **Risiko Data Hilang / Rusak:** Penggunaan buku fisik sangat rentan basah, terselip, sobek, atau hilang.
* **Sulit Mencari Riwayat (Tracing):** Jika terjadi suatu insiden atau butuh pengecekan ulang, petugas harus membolak-balik ribuan lembar buku fisik untuk melacak data tamu atau armada di hari tertentu, yang memakan waktu sangat lama (berjam-jam).
* **Pelaporan (Reporting) Sangat Tidak Efisien:** Pembuatan rekapitulasi data bulanan untuk diserahkan ke manajemen / GA (General Affairs) mengharuskan admin untuk menyalin ulang (re-entry) tulisan tangan ke Microsoft Excel, menghabiskan waktu berhari-hari kerja.
* **Keamanan Dokumen:** KTP/SIM pengunjung hanya ditahan secara fisik tanpa adanya bukti salinan arsip digital yang rapi dan praktis.

---

## 2. Solusi dari Pengamatan Awal
Untuk menjawab permasalahan di atas, dirumuskan solusi digitalisasi berupa **Aplikasi Web GA Management** yang dirancang dengan prinsip:
* **Paperless & Self-Service:** Menggantikan buku tulis dengan formulir digital yang bisa diisi langsung oleh tamu/sopir melalui *Scan QR Code* atau tablet di pos, sehingga mengurangi beban input petugas.
* **Database Terpusat & Real-Time:** Seluruh data otomatis tersimpan di cloud dengan status terkini (Siapa yang masih di dalam, siapa yang sudah keluar).
* **Tanda Tangan & Foto Digital:** Dokumentasi KTP/SIM serta bukti persetujuan dilakukan langsung via layar sentuh dan kamera perangkat.

---

## 3. Sistem Menawarkan Apa & Perbandingan Waktu (Existing vs Sesudah Program)

Sistem ini menawarkan **Otomatisasi Administrasi Pos Gerbang**. Mulai dari *Data Entry* hingga *Reporting*.

### ⏱️ Perbandingan Estimasi Waktu (Waktu Pemrosesan per Orang/Kendaraan)

| Proses / Aktivitas | Flow Existing (Manual Buku) | Flow Baru (Sistem Digital) | Efisiensi Waktu |
| :--- | :--- | :--- | :--- |
| **Registrasi Masuk (Check-in)** | **3 - 5 Menit** (Tamu menyebutkan data, petugas menulis, menahan KTP, menyerahkan kartu). | **45 Detik - 1,5 Menit** (Termasuk waktu load halaman, isi form mandiri, jepret foto dokumen, TTD digital, hingga submit & konfirmasi petugas). | **Lebih Cepat ~60-75%** |
| **Proses Keluar (Check-out)** | **1 - 2 Menit** (Mencari nama di buku, mencoret/mencatat jam keluar, mengembalikan KTP). | **15 - 30 Detik** (Tamu mengembalikan kartu visitor, petugas mencari KTP tamu, mencari nama di Dashboard lalu klik *Check-Out*). | **Lebih Cepat ~75-85%** |
| **Pencarian Data (Tracing)** | **Berjam-jam** (Membuka lembar demi lembar buku harian fisik). | **2 Detik** (Menggunakan fitur *Search Bar* berdasarkan nama, instansi, atau nopol). | **Instan** |
| **Rekapitulasi & Pelaporan** | **2 - 3 Hari Kerja** (Menyalin data dari kertas ke Excel di akhir bulan). | **1 Detik** (Klik tombol *Cetak/Print* atau *Filter Data*, otomatis jadi laporan). | **Otomatis** |

> **Kesimpulan Waktu:** Secara total, sistem ini mengubah proses yang tadinya sangat bergantung pada kecepatan tangan petugas (bottleneck), menjadi proses paralel di mana sistem bekerja secara otomatis, menghemat waktu operasional hingga **lebih dari 90%**.

---

## 4. Simulasi Pengaplikasian di Lapangan

Berikut adalah gambaran skenario berjalannya sistem di pos gerbang sehari-hari:

1. **Kedatangan Tamu / Logistik:** 
   Saat tamu atau armada logistik tiba di pos, mereka akan melihat **3 buah QR Code** yang berbeda terpampang di area pos (QR Tamu, QR Peminjaman, dan QR Logistik). Mereka diarahkan untuk memindai QR Code sesuai dengan keperluannya.
2. **Pengisian Mandiri (Self-Service):** 
   Setelah melakukan scan QR, *smartphone* pengguna akan **langsung diarahkan masuk ke halaman formulir spesifik**. Tamu tinggal mengisi data diri, menjepret foto dokumen, membubuhkan Tanda Tangan Digital, lalu menekan *Submit*.
3. **Input Kartu Mandiri & Monitoring Real-Time:** 
   Saat mengisi formulir, tamu/sopir akan menerima Kartu Visitor fisik dari petugas dan langsung menginputkan nomor kartu tersebut (misal: V-012) ke dalam form. Setelah tamu menekan *Submit*, data seketika terdaftar secara *real-time* di **Dashboard Petugas (SECOM)** dan pengunjung dipersilakan masuk (mengeliminasi langkah verifikasi input ulang oleh petugas).
4. **Saat Tamu Pulang (Check-out):** 
   Tamu mengembalikan Kartu Visitor. Petugas cukup melihat Dashboard *Tamu Masih di Lokasi*, mencari nama tamu tersebut, dan menekan tombol merah **Check-out**. Sistem otomatis mencatat jam kepulangan tamu dan memindahkannya ke tabel Riwayat.

---

## 5. Penjelasan Menu Tampilan Awal (Publik vs SECOM)

Sistem memisahkan antarmuka (UI) menjadi dua bagian agar tidak membingungkan pengguna:

### A. Tampilan Portal Publik & Akses Direct QR Code
Untuk mempermudah dan mempercepat akses bagi orang awam (tamu, kurir, sopir), sistem menyediakan jalur akses instan berupa **3 QR Code Spesifik** agar pengunjung tidak perlu bingung mencari menu. Sistem ini tidak memerlukan Login:
1. **Form Buku Tamu Digital (Guest Book):** Diakses via QR Tamu (untuk kunjungan dinas, vendor, interview).
2. **Form Peminjaman (Borrowing):** Diakses via QR Peminjaman (untuk peminjaman kendaraan GA atau kunci SECOM).
3. **Form Pos 4 (Gate Pass System):** Diakses via QR Logistik (untuk mendaftar Gate In, Keluar EDC, atau Ekspor NEX).
*(Catatan: Sistem juga tetap memiliki Tampilan Landing Page yang menampung ke-3 menu tersebut sebagai cadangan jika tamu mengaksesnya melalui layar Tablet/Kiosk).*

### B. Tampilan Petugas / Admin (Dashboard SECOM)
Ini adalah "Dapur" dari sistem yang hanya bisa diakses menggunakan kata sandi (Login). Didesain untuk **Monitoring dan Aksi Cepat**:
1. **Statistik Kartu (Atas):** Menampilkan jumlah total tamu aktif, armada logistik, dan peminjaman hari ini secara instan (Angka besar).
2. **Tabel "Masih di Lokasi" (Tengah):** Hanya menampilkan orang/kendaraan yang statusnya masih *Aktif* di dalam pabrik. Terdapat tombol aksi **[Check-out]** atau **[Hapus]**.
3. **Menu Navigasi (Samping/Atas):** Terdapat tab terpisah untuk melihat *Riwayat* (data masa lalu), serta *Laporan* untuk keperluan mencetak/mem-filter data berdasarkan tanggal tertentu.
