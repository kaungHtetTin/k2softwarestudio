-- Pricing page: plans with features, demo image, external link.
-- Run once on existing databases after core tables exist.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS pricing_plans (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_type VARCHAR(128) NOT NULL,
    title VARCHAR(255) NOT NULL,
    summary VARCHAR(512) NOT NULL,
    price_display VARCHAR(64) NOT NULL,
    price_note VARCHAR(128) NULL,
    demo_image_path VARCHAR(512) NULL,
    external_url VARCHAR(512) NULL,
    link_label VARCHAR(80) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pricing_plans_visible_sort (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pricing_plan_features (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    plan_id INT UNSIGNED NOT NULL,
    feature_text VARCHAR(512) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_pricing_plan_features_plan (plan_id),
    CONSTRAINT fk_pricing_plan_features_plan FOREIGN KEY (plan_id) REFERENCES pricing_plans (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
