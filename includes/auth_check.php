<?php
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/excel_helper.php';

// Encrypted Cookie Session Secret Key
if (!defined('SESSION_SECRET_KEY')) {
    define('SESSION_SECRET_KEY', getenv('SESSION_SECRET') ?: 'GA_MANAGEMENT_SECURE_TOKEN_2026_SECRET');
}

function encrypt_session_payload($data) {
    $cipher = "aes-256-cbc";
    $key = hash('sha256', SESSION_SECRET_KEY, true);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt(json_encode($data), $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    return base64_encode($iv . $hmac . $ciphertext_raw);
}

function decrypt_session_payload($session_str) {
    if (empty($session_str)) return null;
    $c = base64_decode($session_str);
    $cipher = "aes-256-cbc";
    $key = hash('sha256', SESSION_SECRET_KEY, true);
    $ivlen = openssl_cipher_iv_length($cipher);
    if (strlen($c) < $ivlen + 32) return null;
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, 32);
    $ciphertext_raw = substr($c, $ivlen + 32);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    if (hash_equals($hmac, $calcmac)) {
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return json_decode($original_plaintext, true);
    }
    return null;
}

function save_encrypted_session_cookie($userData) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    $payload = [
        'id'    => $userData['id'] ?? null,
        'name'  => $userData['name'] ?? '',
        'email' => $userData['email'] ?? '',
        'role'  => $userData['role'] ?? 'secom',
        'exp'   => time() + 604800 // 7 Hari
    ];
    $token = encrypt_session_payload($payload);

    setcookie('ga_sess', $token, [
        'expires'  => time() + 604800,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function destroy_encrypted_session_cookie() {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie('ga_sess', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function init_safe_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => 604800, // 7 hari
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        ini_set('session.gc_maxlifetime', 604800);
        session_start();
    }

    // Auto-restore session from encrypted cookie if container PHP session file was lost/recycled
    if (empty($_SESSION['user_id']) && isset($_COOKIE['ga_sess'])) {
        $payload = decrypt_session_payload($_COOKIE['ga_sess']);
        if ($payload && !empty($payload['id']) && isset($payload['exp']) && $payload['exp'] > time()) {
            $_SESSION['user_id'] = $payload['id'];
            $_SESSION['name']    = $payload['name'];
            $_SESSION['email']   = $payload['email'];
            $_SESSION['role']    = $payload['role'];
        }
    }

    // Auto-sync/refresh encrypted session cookie if user is logged in
    if (!empty($_SESSION['user_id'])) {
        save_encrypted_session_cookie([
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role'  => $_SESSION['role'] ?? 'secom'
        ]);
    }
}

init_safe_session();

if (isset($GLOBALS['pdo'])) {
    check_and_run_auto_archive($GLOBALS['pdo']);
}

/**
 * Memastikan pengguna sudah login.
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=" . urlencode("Silakan login terlebih dahulu."));
        exit();
    }
}

/**
 * Memastikan pengguna memiliki peran yang diizinkan.
 */
function check_role($allowed_roles = []) {
    check_login();
    $role = strtolower(trim($_SESSION['role'] ?? 'secom'));
    if (empty($role) || $role === 'sekom') {
        $role = 'secom';
        $_SESSION['role'] = 'secom';
    }
    if (!in_array($role, $allowed_roles)) {
        header("Location: guest.php?error=" . urlencode("Akses ditolak untuk halaman tersebut."));
        exit();
    }
}

/**
 * Cek apakah pengguna saat ini sedang login.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Mengambil data session user aktif.
 */
function get_logged_user() {
    if (!is_logged_in()) {
        return null;
    }
    $role = strtolower(trim($_SESSION['role'] ?? 'secom'));
    if ($role === 'sekom') {
        $role = 'secom';
    }
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['name'] ?? 'User',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $role
    ];
}

/**
 * Set Flash Message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'text' => $message
    ];
}

/**
 * Ambil & Hapus Flash Message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

/**
 * Render Pagination Helper (5 Data Per Halaman)
 * Halaman 1 dan Halaman Terakhir Tetap di Ujung, Halaman Tengah Bergeser Dinamis.
 */
function render_pagination($current_page, $total_pages, $query_params = [], $page_param_name = 'page') {
    $total_pages = max(1, (int)$total_pages);
    $current_page = max(1, min($current_page, $total_pages));

    $build_url = function($page) use ($query_params, $page_param_name) {
        $params = array_merge($_GET, $query_params, [$page_param_name => $page]);
        return '?' . http_build_query($params);
    };

    $html = '<div class="pagination-container">';
    
    // Tombol Prev
    if ($current_page > 1) {
        $html .= '<a href="' . $build_url($current_page - 1) . '" class="page-link page-prev">&laquo; Prev</a>';
    } else {
        $html .= '<span class="page-link disabled">&laquo; Prev</span>';
    }

    $pages_to_show = [];
    $pages_to_show[] = 1; // Halaman 1 selalu tetap di awal

    if ($total_pages <= 7) {
        for ($i = 2; $i <= $total_pages; $i++) {
            $pages_to_show[] = $i;
        }
    } else {
        $middle_start = max(2, $current_page - 1);
        $middle_end   = min($total_pages - 1, $current_page + 1);

        if ($current_page <= 3) {
            $middle_start = 2;
            $middle_end   = 4;
        }
        if ($current_page >= $total_pages - 2) {
            $middle_start = $total_pages - 3;
            $middle_end   = $total_pages - 1;
        }

        if ($middle_start > 2) {
            $pages_to_show[] = '...';
        }

        for ($i = $middle_start; $i <= $middle_end; $i++) {
            $pages_to_show[] = $i;
        }

        if ($middle_end < $total_pages - 1) {
            $pages_to_show[] = '...';
        }

        $pages_to_show[] = $total_pages; // Halaman terakhir selalu tetap di akhir
    }

    foreach ($pages_to_show as $p) {
        if ($p === '...') {
            $html .= '<span class="page-link ellipsis">&hellip;</span>';
        } elseif ($p == $current_page) {
            $html .= '<span class="page-link active">' . $p . '</span>';
        } else {
            $html .= '<a href="' . $build_url($p) . '" class="page-link">' . $p . '</a>';
        }
    }

    // Tombol Next
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $build_url($current_page + 1) . '" class="page-link page-next">Next &raquo;</a>';
    } else {
        $html .= '<span class="page-link disabled">Next &raquo;</span>';
    }

    $html .= '</div>';
    return $html;
}
