<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/database.php';

// Disable browser caching for login page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Hapus sesi HANYA jika pengguna menekan tombol logout secara eksplisit
if (isset($_GET['logout'])) {
    $_SESSION = [];
    destroy_encrypted_session_cookie();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    init_safe_session();
}

// Jika sudah login dengan peran valid, alihkan ke guest.php
if (is_logged_in() && !isset($_GET['error'])) {
    $user_role = strtolower(trim($_SESSION['role'] ?? ''));
    if ($user_role === 'sekom') {
        $_SESSION['role'] = 'secom';
        $user_role = 'secom';
    }
    if ($user_role === 'manager') {
        header("Location: dashboard.php");
        exit();
    } elseif ($user_role === 'secom') {
        header("Location: guest.php");
        exit();
    } else {
        session_unset();
    }
}

$error = $_GET['error'] ?? '';
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $role = strtolower(trim($user['role']));
                if ($role === 'sekom') {
                    $role = 'secom';
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $role;

                if ($role === 'manager') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: guest.php");
                }
                exit();
            } else {
                $error = "Email atau password salah!";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GA Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <div style="margin-bottom: 1.5rem;">
            <a href="index.php" class="btn-back">
                ← Kembali ke Portal Utama
            </a>
        </div>

        <div class="login-brand-header">
            <div class="login-brand-logo">GA</div>
            <h1 class="login-title">GA Management</h1>
            <p class="login-subtitle">Masuk untuk mengakses sistem internal General Affairs</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg === 'logout'): ?>
            <div class="alert alert-info">
                Anda telah berhasil keluar dari sistem.
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label">Alamat Email</label>
                <input type="email" name="email" required class="form-control" placeholder="email@perusahaan.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 1rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                Masuk ke Sistem
            </button>
        </form>

    </div>

</body>
</html>
