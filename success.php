<?php
session_start();
require_once __DIR__ . '/config/database.php';

$type = trim($_GET['type'] ?? 'guest');
$messages = [
    'guest' => [
        'title' => 'Check-in Tamu Berhasil!',
        'msg'   => 'Data pendaftaran tamu Anda telah berhasil dicatat ke dalam sistem. Selamat datang!'
    ],
    'borrowing' => [
        'title' => 'Peminjaman Berhasil!',
        'msg'   => 'Data peminjaman barang / kunci Anda telah berhasil dicatat. Terima kasih!'
    ],
    'gate_in' => [
        'title' => 'Gate In Berhasil!',
        'msg'   => 'Data kendaraan masuk (Gate In) telah berhasil disimpan ke sistem Pos 4. Terima kasih!'
    ],
    'gate_out' => [
        'title' => 'Gate Out Berhasil!',
        'msg'   => 'Data kendaraan keluar (Gate Out) telah berhasil disimpan ke sistem Pos 4. Terima kasih!'
    ],
    'export' => [
        'title' => 'Keluar Export NEX Berhasil!',
        'msg'   => 'Data Keluar Export NEX telah berhasil disimpan ke sistem Pos 4. Terima kasih!'
    ],
];

$title = $_GET['title'] ?? ($messages[$type]['title'] ?? 'Pendaftaran Berhasil!');
$msg   = $_GET['msg'] ?? ($messages[$type]['msg'] ?? 'Data Anda telah berhasil dicatat ke dalam sistem. Terima kasih!');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - GA Management</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background-color: var(--bg-main);
        }
        .success-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            max-width: 480px;
            width: 100%;
            border-top: 6px solid var(--success);
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success-icon {
            width: 84px;
            height: 84px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .info-badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            font-weight: 600;
            padding: 0.65rem 1.25rem;
            border-radius: 30px;
            font-size: 0.9rem;
            margin-top: 1.5rem;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body>
    <div class="success-wrapper">
        <div class="success-card">
            <div class="success-icon">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 style="font-size: 1.5rem; color: var(--text-main); margin-bottom: 0.75rem; font-weight: 700;">
                <?= htmlspecialchars($title) ?>
            </h2>
            
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;">
                <?= htmlspecialchars($msg) ?>
            </p>

            <div class="info-badge">
                ✓ Data Terkonfirmasi &amp; Tersimpan
            </div>
        </div>
    </div>
    <script>
        // Clean URL address bar to remove query parameters
        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, "success.php");
        }
        // Prevent user from pressing Back button to return to the form page
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>
