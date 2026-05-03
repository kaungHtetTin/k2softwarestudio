-- Adds `home_deliverables` for the home page “What we deliver” section.
-- Run once on databases created before this table existed (phpMyAdmin or mysql CLI).
-- To seed the three default cards, copy the INSERT block from `database/schema.sql`, or re-import the full schema into a dev database.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS home_deliverables (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(512) NOT NULL,
    icon_name VARCHAR(64) NOT NULL DEFAULT 'layers',
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_home_deliverables_visible_sort (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
