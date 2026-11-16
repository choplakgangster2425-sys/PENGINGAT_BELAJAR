<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'mading_bulanan';
const DB_USER = 'root';
const DB_PASS = '';

function connectDatabase(): PDO
{
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $server = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, $options);
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $database = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        $options
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(70) NOT NULL,
            username VARCHAR(30) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM("admin", "user") NOT NULL DEFAULT "user",
            status ENUM("active", "disabled") NOT NULL DEFAULT "active",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX role_status_idx (role, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS mading_events (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            event_year SMALLINT UNSIGNED NOT NULL,
            event_month TINYINT UNSIGNED NOT NULL,
            event_day TINYINT UNSIGNED NOT NULL,
            title VARCHAR(70) NOT NULL,
            category VARCHAR(20) NOT NULL DEFAULT "Lainnya",
            event_time TIME NULL,
            note VARCHAR(180) NOT NULL DEFAULT "",
            is_done TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX month_day_idx (event_year, event_month, event_day),
            INDEX user_month_idx (user_id, event_year, event_month),
            CONSTRAINT fk_mading_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS calendar_info (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            created_by BIGINT UNSIGNED NULL,
            event_date DATE NOT NULL,
            title VARCHAR(100) NOT NULL,
            info_type VARCHAR(40) NOT NULL,
            details VARCHAR(255) NOT NULL DEFAULT "",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX event_date_idx (event_date),
            CONSTRAINT fk_calendar_info_admin FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $column = $database->query("SHOW COLUMNS FROM mading_events LIKE 'user_id'")->fetch();
    if (!$column) {
        $database->exec('ALTER TABLE mading_events ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id');
        $database->exec('ALTER TABLE mading_events ADD INDEX user_month_idx (user_id, event_year, event_month)');
    }

    return $database;
}
