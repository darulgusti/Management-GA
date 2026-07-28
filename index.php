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

        <div class="portal-hero" style="text-align: center; margin-top: 10vh; padding: 3rem 2rem; background: #ffffff; border-radius: var(--radius-lg); box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 600px; margin-left: auto; margin-right: auto; border-top: 5px solid var(--primary);">
            <div style="width: 70px; height: 70px; background-color: var(--primary); color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 1.8rem; font-weight: bold; box-shadow: 0 8px 24px rgba(220, 38, 38, 0.2);">
                GA
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">Portal Layanan GA</h1>
            <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6; margin-bottom: 2rem;">
                Sistem Manajemen Terpadu untuk General Affairs, Keamanan (SECOM), dan Logistik. Silakan masuk dengan akun Anda untuk mengakses Dasbor Monitoring.
            </p>
            <a href="login.php" class="btn btn-primary" style="font-size: 1.05rem; padding: 0.8rem 2.5rem; border-radius: 50px; box-shadow: 0 6px 15px rgba(220, 38, 38, 0.25); display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                Masuk ke Dasbor
            </a>
        </div>

    </div>

</body>
</html>
