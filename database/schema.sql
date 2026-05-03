-- K2 portfolio — schema v1 (Phase 0)
-- Create database in phpMyAdmin or: CREATE DATABASE k2_portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Then select it and import this file.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_posts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    body MEDIUMTEXT NOT NULL,
    featured_image VARCHAR(512) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blog_posts_slug (slug),
    KEY idx_blog_posts_status_published (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE app_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    short_description VARCHAR(512) NOT NULL,
    long_description MEDIUMTEXT NULL,
    icon_path VARCHAR(512) NULL,
    external_url VARCHAR(512) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_app_items_slug (slug),
    KEY idx_app_items_visible_sort (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE app_screenshots (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    app_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(512) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_app_screenshots_app (app_id),
    CONSTRAINT fk_app_screenshots_app FOREIGN KEY (app_id) REFERENCES app_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE photo_albums (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_photo_albums_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE photos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    album_id INT UNSIGNED NULL,
    image_path VARCHAR(512) NOT NULL,
    caption VARCHAR(512) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_photos_album_sort (album_id, sort_order),
    CONSTRAINT fk_photos_album FOREIGN KEY (album_id) REFERENCES photo_albums (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_submissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(64) NULL,
    subject VARCHAR(255) NULL,
    message MEDIUMTEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(512) NULL,
    email_sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_submissions_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value MEDIUMTEXT NULL,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_site_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faq_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question VARCHAR(512) NOT NULL,
    answer MEDIUMTEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faq_visible_sort (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE home_deliverables (
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

CREATE TABLE pricing_plans (
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

CREATE TABLE pricing_plan_features (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    plan_id INT UNSIGNED NOT NULL,
    feature_text VARCHAR(512) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_pricing_plan_features_plan (plan_id),
    CONSTRAINT fk_pricing_plan_features_plan FOREIGN KEY (plan_id) REFERENCES pricing_plans (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE finance_accounts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'MMK',
    opening_balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_finance_accounts_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE finance_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_finance_categories_name (name),
    KEY idx_finance_categories_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE finance_transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type ENUM('income', 'expense', 'transfer') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    occurred_at DATE NOT NULL,
    description VARCHAR(512) NOT NULL DEFAULT '',
    category_id INT UNSIGNED NULL,
    account_id INT UNSIGNED NULL,
    transfer_from_id INT UNSIGNED NULL,
    transfer_to_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_finance_tx_date (occurred_at),
    KEY idx_finance_tx_type (type),
    KEY idx_finance_tx_account (account_id),
    KEY idx_finance_tx_cat (category_id),
    CONSTRAINT fk_finance_tx_category FOREIGN KEY (category_id) REFERENCES finance_categories (id) ON DELETE RESTRICT,
    CONSTRAINT fk_finance_tx_account FOREIGN KEY (account_id) REFERENCES finance_accounts (id) ON DELETE RESTRICT,
    CONSTRAINT fk_finance_tx_from FOREIGN KEY (transfer_from_id) REFERENCES finance_accounts (id) ON DELETE RESTRICT,
    CONSTRAINT fk_finance_tx_to FOREIGN KEY (transfer_to_id) REFERENCES finance_accounts (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Single admin (spec §5.1). Password: ChangeMe!Admin123 — change immediately after first login (Phase 4).
INSERT INTO users (email, password_hash, created_at) VALUES (
    'admin@example.com',
    '$2y$10$SYz5D5hdM7/MGqT7/HRe6u8Xie2D6DuPkJzDJV.BqDebBC5.yJUqK',
    NOW()
);

INSERT INTO faq_items (question, answer, sort_order, is_visible) VALUES
(
    'What kinds of projects do you take on?',
    '<p>We focus on web applications, marketing sites with structured content (blog, galleries, and landing pages), and integrations with email, payments, and internal tools. If it needs a maintainable PHP + MySQL foundation and a polished Bootstrap UI, we are a strong fit.</p>',
    0,
    1
),
(
    'How do you approach security?',
    '<p>Threat modeling starts at the foundation: prepared statements for SQL, CSRF protection on state-changing requests, secure cookies for admin sessions, outbound escaping for XSS, rate limiting on authentication surfaces, and sensible headers (CSP tuned for our asset pipeline).</p>',
    1,
    1
),
(
    'What is the typical engagement model?',
    '<p>Engagements usually start with a short discovery to align on scope, milestones, and success metrics. We prefer iterative delivery — ship a thin vertical slice early, then expand features and content with measurable checkpoints.</p>',
    2,
    1
),
(
    'Can we manage content without developers?',
    '<p>Yes — that is why this stack includes admin tooling for the blog, app gallery, photo gallery, and FAQs. You edit structured content in the browser while the public site stays fast and consistent with your brand tokens.</p>',
    3,
    1
);

INSERT INTO home_deliverables (title, description, icon_name, sort_order, is_visible) VALUES
(
    'Product engineering',
    'Web apps, APIs, and integrations with maintainable PHP + MySQL foundations and room to grow.',
    'window-stack',
    0,
    1
),
(
    'Interfaces that scale',
    'Responsive, accessible UI with Bootstrap 5 — consistent patterns across marketing and admin.',
    'phone',
    1,
    1
),
(
    'Launch & iterate',
    'Blog, galleries, and landing pages you can update without fighting the codebase.',
    'graph-up-arrow',
    2,
    1
);

INSERT INTO pricing_plans (id, project_type, title, summary, price_display, price_note, demo_image_path, external_url, link_label, sort_order, is_visible) VALUES
(
    1,
    'Marketing & content',
    'Landing & brochure site',
    'Campaign pages, structured content, and galleries with an admin you can use day to day.',
    '$2,500',
    'starting at',
    NULL,
    NULL,
    'Example scope',
    0,
    1
),
(
    2,
    'Product build',
    'Web application',
    'Authenticated workflows, APIs, and integrations on PHP + MySQL with a cohesive Bootstrap UI.',
    '$15,000',
    'typical MVP range',
    NULL,
    NULL,
    'Sample roadmap',
    1,
    1
),
(
    3,
    'Ongoing partnership',
    'Monthly retainer',
    'Roadmap increments, production support, and security-conscious maintenance with predictable capacity.',
    '$4,800',
    'per month',
    NULL,
    NULL,
    'Discuss engagement',
    2,
    1
);

INSERT INTO pricing_plan_features (plan_id, feature_text, sort_order) VALUES
(1, 'Up to five primary pages plus blog & FAQ patterns', 0),
(1, 'Responsive Bootstrap 5 UI and accessibility baseline', 1),
(1, 'Contact capture with safe server-side handling', 2),
(2, 'Discovery, specification, and milestone planning', 0),
(2, 'Staging deploys, roles, and structured admin tooling', 1),
(2, 'Email and third-party integrations as scoped', 2),
(3, 'Prioritized backlog and predictable release cadence', 0),
(3, 'Production triage and dependency hygiene', 1),
(3, 'Security-conscious updates aligned to your risk profile', 2);

ALTER TABLE pricing_plans AUTO_INCREMENT = 10;

INSERT INTO finance_categories (name, sort_order, is_active) VALUES
('Office & supplies', 0, 1),
('Travel & transport', 1, 1),
('Marketing & ads', 2, 1),
('Software & hosting', 3, 1),
('Professional fees', 4, 1),
('Utilities', 5, 1),
('Salaries & contractors', 6, 1),
('Sales & income (misc)', 10, 1),
('Other', 99, 1);
