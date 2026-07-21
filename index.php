<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Layanan GA - General Affairs System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="portal-body">

    <header class="portal-navbar">
        <div class="portal-navbar-left">
            <div class="sidebar-logo-icon">GA</div>
            <div class="portal-navbar-brand">
                <strong>Portal Layanan GA</strong>
                <small>General Affairs &middot; Reception &amp; Service</small>
            </div>
        </div>
        <div>
            <a href="login.php" class="btn btn-sm btn-outline-light">Login Petugas</a>
        </div>
    </header>

    <div class="portal-container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" style="margin-top: 1.5rem; font-size: 1rem;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($flash['text']) ?>
            </div>
        <?php endif; ?>

        <div class="portal-hero">
            <h1>Selamat Datang di<br>Portal Layanan GA</h1>
            <p>Pilih jenis layanan di bawah ini untuk mengakses formulir yang Anda butuhkan.</p>
        </div>

        <!-- TWO DISTINCT SERVICE SELECTION CARDS -->
        <div class="portal-cards">

            <!-- CARD 1: FORM BUKU TAMU -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 2rem; border-top: 4px solid var(--primary);" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div>
                    <div style="width: 52px; height: 52px; background-color: var(--primary-light); color: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Buku Tamu Digital</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.65; margin-bottom: 1.5rem;">
                        Formulir pendaftaran untuk pengunjung, kedinasan, vendor, atau tamu pabrik yang memerlukan kartu akses masuk.
                    </p>
                </div>
                <a href="guest_form.php" class="btn btn-primary btn-lg btn-block">
                    Buka Form Buku Tamu →
                </a>
            </div>

            <!-- CARD 2: FORM PEMINJAMAN BARANG -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 2rem; border-top: 4px solid var(--success);" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div>
                    <div style="width: 52px; height: 52px; background-color: #dcfce7; color: var(--success); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Peminjaman Barang &amp; Kunci</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.65; margin-bottom: 1.5rem;">
                        Formulir peminjaman kunci ruangan, alat/peralatan inventaris, proyektor, atau fasilitas GA oleh staf/karyawan.
                    </p>
                </div>
                <a href="borrowing_form.php" class="btn btn-success btn-lg btn-block">
                    Buka Form Peminjaman →
                </a>
            </div>

        </div>

    </div>

</body>
</html>
