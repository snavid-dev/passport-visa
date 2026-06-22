-- ============================================================
-- Iran Visa Processing & Accounting System
-- Full schema + indexes + seed data
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Target: MySQL 5.7+ / MariaDB 10.x
--
-- Design rules:
--   * All dates stored Gregorian (DATE/DATETIME); Shamsi is display-only.
--   * All money columns DECIMAL(15,2). Never FLOAT.
--   * All currency columns ENUM('AFN','USD','TOMAN').
--   * Foreign keys ON DELETE RESTRICT unless stated otherwise.
--   * Every FK and every WHERE/ORDER BY column is indexed.
--
-- Schema change log:
--   2026-06-22 — Initial schema (Phase 1).
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO";

-- ------------------------------------------------------------
-- CodeIgniter session store (sess_driver = 'database')
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `id` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` BLOB NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Roles
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Permissions
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name` VARCHAR(64) NOT NULL,
  `label` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Role ⇄ Permission (composite PK)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `idx_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`)
      REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`)
      REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_active` (`active`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`)
      REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Financial Accounts (client / vendor / expense / income / individual / cash)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `financial_accounts`;
CREATE TABLE `financial_accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `type` ENUM('client','vendor','expense','income','individual','cash') NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_accounts_type` (`type`),
  KEY `idx_accounts_active` (`active`),
  KEY `idx_accounts_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Services (named visa service types with default per-passport fee)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `default_fee` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `default_currency` ENUM('AFN','USD','TOMAN') NOT NULL DEFAULT 'AFN',
  `visa_type` VARCHAR(100) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_services_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Tasks (one passport batch → one vendor → Iran visas) — core entity
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `vendor_id` INT UNSIGNED DEFAULT NULL,
  `service_id` INT UNSIGNED DEFAULT NULL,
  `visa_type` VARCHAR(100) DEFAULT NULL,
  `destination` VARCHAR(100) NOT NULL DEFAULT 'Iran',
  `date` DATE NOT NULL,
  `status` ENUM('open','completed','cancelled') NOT NULL DEFAULT 'open',
  `fee_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `fee_currency` ENUM('AFN','USD','TOMAN') NOT NULL DEFAULT 'AFN',
  `vendor_cost_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `vendor_cost_currency` ENUM('AFN','USD','TOMAN') NOT NULL DEFAULT 'AFN',
  `note` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tasks_client` (`client_id`),
  KEY `idx_tasks_vendor` (`vendor_id`),
  KEY `idx_tasks_service` (`service_id`),
  KEY `idx_tasks_status` (`status`),
  KEY `idx_tasks_date` (`date`),
  CONSTRAINT `fk_tasks_client` FOREIGN KEY (`client_id`)
      REFERENCES `financial_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tasks_vendor` FOREIGN KEY (`vendor_id`)
      REFERENCES `financial_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tasks_service` FOREIGN KEY (`service_id`)
      REFERENCES `services` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`)
      REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Task Passports (rows under a task)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `task_passports`;
CREATE TABLE `task_passports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` INT UNSIGNED NOT NULL,
  `surname` VARCHAR(120) DEFAULT NULL,
  `given_name` VARCHAR(120) DEFAULT NULL,
  `passport_no` VARCHAR(50) DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `place_of_birth` VARCHAR(120) DEFAULT NULL,
  `issue_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `gender` ENUM('male','female') DEFAULT NULL,
  `scan_path` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_passports_task` (`task_id`),
  KEY `idx_passports_no` (`passport_no`),
  CONSTRAINT `fk_passports_task` FOREIGN KEY (`task_id`)
      REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Task Client Payments (money received from client)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `task_client_payments`;
CREATE TABLE `task_client_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` ENUM('AFN','USD','TOMAN') NOT NULL,
  `date` DATE NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cpay_task` (`task_id`),
  KEY `idx_cpay_currency` (`currency`),
  CONSTRAINT `fk_cpay_task` FOREIGN KEY (`task_id`)
      REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cpay_user` FOREIGN KEY (`recorded_by`)
      REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Task Vendor Payments (money paid to vendor)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `task_vendor_payments`;
CREATE TABLE `task_vendor_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` ENUM('AFN','USD','TOMAN') NOT NULL,
  `date` DATE NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vpay_task` (`task_id`),
  KEY `idx_vpay_currency` (`currency`),
  CONSTRAINT `fk_vpay_task` FOREIGN KEY (`task_id`)
      REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vpay_user` FOREIGN KEY (`recorded_by`)
      REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ------------------------------------------------------------
-- Ledger Entries (double-entry; one row per side; per-currency)
--   source  : 'task_client_payment' | 'task_vendor_payment' | 'receipt'
--   reference: the source row id (e.g. task_id or receipt id)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `ledger_entries`;
CREATE TABLE `ledger_entries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT UNSIGNED NOT NULL,
  `currency` ENUM('AFN','USD','TOMAN') NOT NULL,
  `debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `date` DATE NOT NULL,
  `source` VARCHAR(50) NOT NULL,
  `reference` VARCHAR(50) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_account_currency` (`account_id`, `currency`),
  KEY `idx_date` (`date`),
  KEY `idx_source_ref` (`source`, `reference`),
  CONSTRAINT `fk_ledger_account` FOREIGN KEY (`account_id`)
      REFERENCES `financial_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ledger_user` FOREIGN KEY (`recorded_by`)
      REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Permissions ------------------------------------------------
INSERT INTO `permissions` (`key_name`, `label`) VALUES
('manage_tasks',       'مدیریت وظایف'),
('manage_accounts',    'مدیریت حساب‌ها'),
('manage_services',    'مدیریت خدمات'),
('manage_receipts',    'مدیریت رسیدها'),
('view_balance_sheet', 'مشاهده ترازنامه'),
('manage_users',       'مدیریت کاربران'),
('manage_roles',       'مدیریت نقش‌ها'),
('view_reports',       'مشاهده گزارشات');

-- Roles ------------------------------------------------------
INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'مدیر کل');

-- Admin role gets every permission ---------------------------
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- Default admin user -----------------------------------------
-- Username: admin   Password: admin@1234  (change after first login)
INSERT INTO `users` (`name`, `username`, `email`, `phone`, `password`, `role_id`, `active`) VALUES
('سید نوید عظیمی', 'admin', 'snavid.dev@gmail.com', NULL,
 '$2y$10$BN6ZRUEZ8TdUYt57DYeyBuz.Gm266yPMY4yBVhyedKdx1awsFQoIO', 1, 1);

-- Cash account (the company cash box) ------------------------
INSERT INTO `financial_accounts` (`name`, `type`, `active`) VALUES
('صندوق نقدی', 'cash', 1);
