<?php
// config/migrate.php - Terpisah khusus untuk pembuatan tabel & migrasi otomatis (Auto Migration)
if (!isset($pdo)) {
    return;
}

try {
    // 1. Tabel Logistik Gate In
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_gate_ins` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nopol` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `visitor_number` VARCHAR(100) NULL,
      `antree_number` VARCHAR(100) NULL,
      `transportir` VARCHAR(255) NOT NULL,
      `tonnage` VARCHAR(100) NULL,
      `destination` VARCHAR(100) NOT NULL DEFAULT 'Kirim',
      `sim_type` VARCHAR(50) DEFAULT 'SIM B',
      `sim_number` VARCHAR(100) NULL,
      `document_photo` LONGTEXT NULL,
      `checklist_sim` TINYINT(1) DEFAULT 0,
      `checklist_stnk` TINYINT(1) DEFAULT 0,
      `checklist_kir` TINYINT(1) DEFAULT 0,
      `entry_time` DATETIME NOT NULL,
      `exit_time` DATETIME NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Masuk',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Tabel Logistik Gate Out (EDC)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_gate_outs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nopol` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `do_number` VARCHAR(100) NOT NULL,
      `destination` VARCHAR(255) NOT NULL,
      `tonnage` VARCHAR(100) NOT NULL DEFAULT '-',
      `transportir` VARCHAR(255) NOT NULL,
      `document_photo` LONGTEXT NULL,
      `exit_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Tabel Logistik Export Ajinex / NOPOR
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_export_nex_mopors` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `mopor_number` VARCHAR(100) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL DEFAULT '-',
      `do_number` VARCHAR(100) NOT NULL,
      `container_number` VARCHAR(100) NOT NULL,
      `seal_number` VARCHAR(100) NOT NULL,
      `destination` VARCHAR(255) NOT NULL,
      `tonnage` VARCHAR(100) NOT NULL DEFAULT '-',
      `transportir` VARCHAR(255) NOT NULL DEFAULT '-',
      `document_photo` LONGTEXT NULL,
      `exit_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (\Throwable $t) {}

// Auto-Migration Kolom Baru
try { $pdo->exec("ALTER TABLE item_borrowings ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'GA' AFTER borrower_name"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE item_borrowings ADD COLUMN key_number VARCHAR(100) NULL AFTER item_code"); } catch (\Throwable $t) {}

try { $pdo->exec("ALTER TABLE guests ADD COLUMN sim_number VARCHAR(100) NULL AFTER visitor_card_number"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE guests ADD COLUMN document_photo LONGTEXT NULL AFTER sim_number"); } catch (\Throwable $t) {}

try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN sim_number VARCHAR(100) NULL AFTER destination"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN document_photo LONGTEXT NULL AFTER sim_number"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN visitor_number VARCHAR(100) NULL AFTER driver_name"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN antree_number VARCHAR(100) NULL AFTER visitor_number"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN sim_type VARCHAR(50) DEFAULT 'SIM B' AFTER destination"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN tonnage VARCHAR(100) NULL AFTER transportir"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ADD COLUMN exit_time DATETIME NULL AFTER entry_time"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_ins ALTER COLUMN status SET DEFAULT 'Masuk'"); } catch (\Throwable $t) {}
try { $pdo->exec("UPDATE logistic_gate_ins SET status = 'Masuk' WHERE status = 'Done' OR status IS NULL OR status = ''"); } catch (\Throwable $t) {}

try { $pdo->exec("ALTER TABLE logistic_gate_outs MODIFY COLUMN tonnage VARCHAR(100) NOT NULL DEFAULT '-'"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_gate_outs ADD COLUMN document_photo LONGTEXT NULL AFTER transportir"); } catch (\Throwable $t) {}

try { $pdo->exec("ALTER TABLE logistic_export_nex_mopors MODIFY COLUMN tonnage VARCHAR(100) NOT NULL DEFAULT '-'"); } catch (\Throwable $t) {}
try { $pdo->exec("ALTER TABLE logistic_export_nex_mopors ADD COLUMN document_photo LONGTEXT NULL AFTER transportir"); } catch (\Throwable $t) {}
