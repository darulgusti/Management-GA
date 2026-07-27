<?php
session_start();
require_once __DIR__ . '/config/database.php';

$title = trim($_GET['title'] ?? 'Pendaftaran Berhasil!');
$msg   = trim($_GET['msg'] ?? 'Data Anda telah berhasil dicatat ke dalam sistem GA Management. Terima kasih!');
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
            width: 80px;
            height: 80px;
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
        .countdown-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 1.5rem;
            border: 1px solid var(--border);
        }
        .countdown-num {
            color: var(--primary);
            font-size: 1.15rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="success-wrapper">
        <div class="success-card">
            <div class="success-icon">
                <svg width="44" height="44" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 style="font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.75rem; font-weight: 700;">
                <?= htmlspecialchars($title) ?>
            </h2>
            
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 0.5rem;">
                <?= htmlspecialchars($msg) ?>
            </p>

            <div class="countdown-badge">
                Halaman ini akan ditutup dalam <span id="countdown" class="countdown-num">3</span> detik...
            </div>

            <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="button" onclick="closePage()" class="btn btn-primary btn-block" style="padding: 0.75rem; font-size: 0.95rem;">
                    Tutup Halaman Sekarang
                </button>
            </div>
        </div>
    </div>

    <script>
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        const countdownBadge = document.querySelector('.countdown-badge');

        function closePage() {
            // 1. Attempt window close methods
            try { window.close(); } catch (e) {}
            try { self.close(); } catch (e) {}
            try {
                window.opener = null;
                window.open('', '_self', '');
                window.close();
            } catch (e) {}

            // 2. Attempt In-App scanner close APIs
            try {
                if (typeof WeixinJSBridge !== 'undefined') {
                    WeixinJSBridge.call('closeWindow');
                }
            } catch (e) {}

            // 3. Attempt history back (returns to Camera / Scanner app)
            try {
                if (window.history.length > 1) {
                    window.history.go(-2); // Skip form post back to scanner
                }
            } catch (e) {}

            // 4. Update status badge text on mobile screen
            setTimeout(() => {
                if (countdownBadge) {
                    countdownBadge.innerHTML = "✔ Data Berhasil Disimpan. Silakan tutup tab ini.";
                    countdownBadge.style.backgroundColor = "#dcfce7";
                    countdownBadge.style.color = "#16a34a";
                }
            }, 400);
        }

        const timer = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.innerText = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                closePage();
            }
        }, 1000);
    </script>
</body>
</html>
