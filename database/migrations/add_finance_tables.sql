-- Finance: accounts (MMK), global categories, transactions (income / expense / transfer).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS finance_accounts (
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

CREATE TABLE IF NOT EXISTS finance_categories (
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

CREATE TABLE IF NOT EXISTS finance_transactions (
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

INSERT IGNORE INTO finance_categories (name, sort_order, is_active) VALUES
('Office & supplies', 0, 1),
('Travel & transport', 1, 1),
('Marketing & ads', 2, 1),
('Software & hosting', 3, 1),
('Professional fees', 4, 1),
('Utilities', 5, 1),
('Salaries & contractors', 6, 1),
('Sales & income (misc)', 10, 1),
('Other', 99, 1);
