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
    <style>
        .portal-cards-5 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body class="portal-body">

    <header class="portal-navbar" style="background: #ffffff; padding: 0.9rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div class="portal-navbar-left" style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="sidebar-logo-icon" style="background: var(--primary); color: #ffffff;">GA</div>
            <div class="portal-navbar-brand">
                <strong style="font-size: 1.1rem; color: var(--primary);">Portal Layanan GA</strong>
            </div>
        </div>
        <div>
            <a href="login.php" class="btn btn-sm btn-primary">Login</a>
        </div>
    </header>

    <div class="portal-container" style="max-width: 1200px;">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" style="margin-top: 1.5rem; font-size: 0.95rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($flash['text']) ?>
            </div>
        <?php endif; ?>

        <div class="portal-hero">
            <h1>Selamat Datang di Portal Layanan GA</h1>
            <p>Pilih jenis layanan di bawah ini untuk mengakses formulir atau fitur yang Anda butuhkan.</p>
        </div>

        <!-- 5 DISTINCT SERVICE SELECTION CARDS -->
        <div class="portal-cards-5">

            <!-- CARD 1: BUKU TAMU DIGITAL -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; border-top: 4px solid var(--primary);" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div>
                    <div style="width: 44px; height: 44px; background-color: var(--primary-light); color: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">Buku Tamu Digital</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                        Formulir pendaftaran digital untuk pengunjung atau tamu yang datang.
                    </p>
                </div>
                <a href="guest_form.php" class="btn btn-primary btn-block" style="font-size: 0.85rem; padding: 0.55rem;">
                    Form Buku Tamu →
                </a>
            </div>

            <!-- CARD 2: PEMINJAMAN BARANG & KUNCI -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; border-top: 4px solid var(--primary);" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div>
                    <div style="width: 44px; height: 44px; background-color: var(--primary-light); color: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">Peminjaman Barang &amp; Kunci</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                        Formulir peminjaman aset inventaris GA dan kunci ruangan.
                    </p>
                </div>
                <a href="borrowing_form.php" class="btn btn-success btn-block" style="font-size: 0.85rem; padding: 0.55rem;">
                    Form Peminjaman →
                </a>
            </div>

            <!-- CARD 3: LOGISTIK GATE PASS SYSTEM -->
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; border-top: 4px solid var(--primary);" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.10)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div>
                    <div style="width: 44px; height: 44px; background-color: var(--primary-light); color: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 0.85rem;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.35rem;">Pos 4 - Gate Pass System</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                        Pencatatan &amp; pemantauan lalu lintas armada (Buku Masuk, Buku Keluar &amp; Export NEX/NOPOR).
                    </p>
                </div>
                <a href="logistic_form.php" class="btn btn-primary btn-block" style="font-size: 0.85rem; padding: 0.55rem;">
                    Form Input Pos 4 →
                </a>
            </div>

        </div>

    </div>

</body>
</html>
