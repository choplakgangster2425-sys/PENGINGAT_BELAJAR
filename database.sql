CREATE DATABASE IF NOT EXISTS `mading_bulanan`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `mading_bulanan`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(70) NOT NULL,
    `username` VARCHAR(30) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    KEY `role_status_idx` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mading_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED DEFAULT NULL,
    `event_year` SMALLINT UNSIGNED NOT NULL,
    `event_month` TINYINT UNSIGNED NOT NULL,
    `event_day` TINYINT UNSIGNED NOT NULL,
    `title` VARCHAR(70) NOT NULL,
    `category` VARCHAR(20) NOT NULL DEFAULT 'Lainnya',
    `event_time` TIME DEFAULT NULL,
    `note` VARCHAR(180) NOT NULL DEFAULT '',
    `is_done` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `month_day_idx` (`event_year`, `event_month`, `event_day`),
    KEY `user_month_idx` (`user_id`, `event_year`, `event_month`),
    CONSTRAINT `fk_mading_events_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calendar_info` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_by` BIGINT UNSIGNED DEFAULT NULL,
    `event_date` DATE NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `info_type` VARCHAR(40) NOT NULL,
    `details` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `event_date_idx` (`event_date`),
    CONSTRAINT `fk_calendar_info_admin`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Akun demo tugas lokal. INSERT IGNORE tidak menimpa akun yang sudah ada.
INSERT IGNORE INTO `users` (`name`, `username`, `password_hash`, `role`, `status`) VALUES
    ('Admin Demo', 'admin.demo', '$2y$10$hQcl.WnM0AlLSdvLN9e6buUEyVRXipBoybWY1vPdF5SgaI4g3zoca', 'admin', 'active'),
    ('Pengguna Demo', 'user.demo', '$2y$10$anFlIV/YvoTYwNJ25s4EsulZ7lbXidQt/uTuUrKo1hw4jEd7f2aqa', 'user', 'active');
