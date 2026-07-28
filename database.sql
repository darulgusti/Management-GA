-- 1. Tabel Users (Pengguna Sistem)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'secom',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Guests (Buku Tamu Digital)
CREATE TABLE IF NOT EXISTS `guests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `institution` VARCHAR(255) NULL,
  `guest_category` VARCHAR(100) NOT NULL DEFAULT 'kedinasan',
  `purpose` TEXT NULL,
  `person_to_meet` VARCHAR(255) NOT NULL,
  `id_type` VARCHAR(100) NULL,
  `visitor_card_number` VARCHAR(100) NULL,
  `sim_number` VARCHAR(100) NULL,
  `document_photo` LONGTEXT NULL,
  `time_in` DATETIME NOT NULL,
  `time_out` DATETIME NULL,
  `signature` LONGTEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Item Borrowings (Peminjaman Barang & Kunci)
CREATE TABLE IF NOT EXISTS `item_borrowings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrower_name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'GA',
  `department` VARCHAR(255) NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `item_code` VARCHAR(255) NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `borrow_time` DATETIME NOT NULL,
  `return_time` DATETIME NULL,
  `initial_condition` VARCHAR(255) NOT NULL DEFAULT 'Baik',
  `return_condition` VARCHAR(255) NULL,
  `signature` LONGTEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'borrowed',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexing untuk optimasi query (Performance Boost)
ALTER TABLE `guests` ADD INDEX `idx_time_in` (`time_in`);
ALTER TABLE `guests` ADD INDEX `idx_time_out` (`time_out`);
ALTER TABLE `item_borrowings` ADD INDEX `idx_borrow_time` (`borrow_time`);
ALTER TABLE `item_borrowings` ADD INDEX `idx_status` (`status`);

-- 4. Tabel Archives (Riwayat Pengarsipan Data)
CREATE TABLE IF NOT EXISTS `archives` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `archive_type` VARCHAR(100) NOT NULL,
  `records_count` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabel Logistic Gate In (Buku Masuk Logistik)
CREATE TABLE IF NOT EXISTS `logistic_gate_ins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nopol` VARCHAR(50) NOT NULL,
  `driver_name` VARCHAR(255) NOT NULL,
  `visitor_number` VARCHAR(100) NULL,
  `antree_number` VARCHAR(100) NULL,
  `transportir` VARCHAR(255) NOT NULL,
  `sim_type` VARCHAR(50) DEFAULT 'SIM B',
  `sim_number` VARCHAR(100) NULL,
  `document_photo` LONGTEXT NULL,
  `checklist_sim` TINYINT(1) DEFAULT 0,
  `checklist_stnk` TINYINT(1) DEFAULT 0,
  `checklist_kir` TINYINT(1) DEFAULT 0,
  `entry_time` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabel Logistic Gate Out (Buku Keluar Logistik)
CREATE TABLE IF NOT EXISTS `logistic_gate_outs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nopol` VARCHAR(50) NOT NULL,
  `driver_name` VARCHAR(255) NOT NULL,
  `do_number` VARCHAR(100) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `tonnage` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `transportir` VARCHAR(255) NOT NULL,
  `exit_time` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 7. Tabel Logistic Export NEX / MOPOR (Export Kontainer Logistik)
CREATE TABLE IF NOT EXISTS `logistic_export_nex_mopors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mopor_number` VARCHAR(100) NOT NULL,
  `driver_name` VARCHAR(255) NOT NULL,
  `do_number` VARCHAR(100) NOT NULL,
  `container_number` VARCHAR(100) NOT NULL,
  `seal_number` VARCHAR(100) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `tonnage` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `transportir` VARCHAR(255) NOT NULL,
  `exit_time` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexing untuk optimasi query Logistik
ALTER TABLE `logistic_gate_ins` ADD INDEX `idx_entry_time` (`entry_time`);
ALTER TABLE `logistic_gate_outs` ADD INDEX `idx_exit_time` (`exit_time`);
ALTER TABLE `logistic_export_nex_mopors` ADD INDEX `idx_export_exit_time` (`exit_time`);

-- Data Seeder Awal
-- Admin / Manager: admin@ga.com / admin123
-- Secom / Staf: secom@ga.com / secom123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Manager GA Supervisor', 'admin@ga.com', '$2y$10$w/LyDqb3PqV6/M5O3w9GHuQMyqaUwtAZc7aZsAI7Y5hNqy5b12K9S', 'manager'),
('Staf Secom GA', 'secom@ga.com', '$2y$10$JnCC8OkL.C237MrMykEEheXHWR7owkSEWeatnBu.ZGkXirwi3m9PO', 'secom');

