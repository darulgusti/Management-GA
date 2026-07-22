<?php
// Set Timezone Real-Time Local (WIB / Asia/Jakarta UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database GA Management System
// Mendukung Environment Variables (untuk Deployment Cloud Vercel/Railway) & Fallback Local Laragon
$host = getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$db   = getenv('DB_NAME') ?: 'test';
$user = getenv('DB_USER') ?: 'iD6MKQvLzepHBRX.root';
$pass = getenv('DB_PASS') ?: 'pd8vzYeUOpfWDK0E';
$port = getenv('DB_PORT') ?: '4000';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => true, // Reuse database connection sockets (0ms latency for warm requests)
];

// Jika koneksi Cloud DB (TiDB Cloud / Aiven), aktifkan SSL / Secure Transport
if (getenv('DB_HOST')) {
    $sslVerifyOpt = defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT') ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT : 1006;
    $sslCaOpt     = defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : 1002;

    $options[$sslVerifyOpt] = false;
    $caPath = file_exists('/etc/ssl/certs/ca-certificates.crt') ? '/etc/ssl/certs/ca-certificates.crt' : (file_exists('/etc/pki/tls/certs/ca-bundle.crt') ? '/etc/pki/tls/certs/ca-bundle.crt' : '');
    if ($caPath) {
        $options[$sslCaOpt] = $caPath;
    }
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '+07:00';");
} catch (\PDOException $e) {
    if (!getenv('DB_HOST')) {
        try {
            $pass = 'root';
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->exec("SET time_zone = '+07:00';");
        } catch (\PDOException $ex) {
            die("Koneksi Database Gagal: " . $ex->getMessage() . "<br><br><i>Pastikan MySQL di Laragon/XAMPP sudah berjalan dan file <b>database.sql</b> sudah diimport.</i>");
        }
    } else {
        die("Koneksi Cloud Database Gagal: " . $e->getMessage() . "<br><br><i>Periksa kembali Environment Variables DB_HOST, DB_NAME, DB_USER, DB_PASS di Dashboard Vercel.</i>");
    }
}
