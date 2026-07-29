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

// Auto-migration: Pastikan kolom baru ada di tabel (untuk Cloud DB / Vercel)
try { $pdo->exec("ALTER TABLE item_borrowings ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'GA' AFTER borrower_name"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN sim_number VARCHAR(100) NULL AFTER destination"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN document_photo LONGTEXT NULL AFTER sim_number"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE guests ADD COLUMN sim_number VARCHAR(100) NULL AFTER visitor_card_number"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE guests ADD COLUMN document_photo LONGTEXT NULL AFTER sim_number"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_outs MODIFY COLUMN tonnage VARCHAR(100) NOT NULL DEFAULT '-'"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_export_nex_mopors MODIFY COLUMN tonnage VARCHAR(100) NOT NULL DEFAULT '-'"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_outs ADD COLUMN document_photo LONGTEXT NULL AFTER transportir"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_export_nex_mopors ADD COLUMN document_photo LONGTEXT NULL AFTER transportir"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE item_borrowings ADD COLUMN key_number VARCHAR(100) NULL AFTER item_code"); } catch (\Throwable $t) {}

