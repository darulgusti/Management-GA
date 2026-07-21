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
  `time_in` DATETIME NOT NULL,
  `time_out` DATETIME NULL,
  `signature` LONGTEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Item Borrowings (Peminjaman Barang & Kunci)
CREATE TABLE IF NOT EXISTS `item_borrowings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrower_name` VARCHAR(255) NOT NULL,
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

-- 4. Tabel Archives (Riwayat Pengarsipan Data)
CREATE TABLE IF NOT EXISTS `archives` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `archive_type` VARCHAR(100) NOT NULL,
  `records_count` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Data Seeder Awal
-- Admin / Manager: admin@ga.com / admin123
-- Secom / Staf: secom@ga.com / secom123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Manager GA Supervisor', 'admin@ga.com', '$2y$10$w/LyDqb3PqV6/M5O3w9GHuQMyqaUwtAZc7aZsAI7Y5hNqy5b12K9S', 'manager'),
('Staf Secom GA', 'secom@ga.com', '$2y$10$JnCC8OkL.C237MrMykEEheXHWR7owkSEWeatnBu.ZGkXirwi3m9PO', 'secom');

