<?php

declare(strict_types=1);

/**
 * Application configuration from environment (set via includes/bootstrap.php).
 */

if (!defined('K2_ROOT')) {
    define('K2_ROOT', dirname(__DIR__));
}

define('K2_ENV', $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production');
define('K2_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

define('K2_APP_URL', rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', '/'));

define('K2_DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1');
define('K2_DB_PORT', (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306'));
define('K2_DB_NAME', $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
define('K2_DB_USER', $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '');
define('K2_DB_PASS', $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '');

define('K2_MAIL_FROM', $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: '');
define('K2_MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'Site');

define('K2_SMTP_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: '');
define('K2_SMTP_PORT', (int) ($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: '587'));
define('K2_SMTP_USER', $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?: '');
define('K2_SMTP_PASS', $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?: '');
define('K2_SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?: 'tls');

/** Login throttle — failed attempts per identity + IP (§6.1) */
define(
    'K2_LOGIN_MAX_ATTEMPTS',
    max(1, (int) ($_ENV['LOGIN_MAX_ATTEMPTS'] ?? getenv('LOGIN_MAX_ATTEMPTS') ?: '5'))
);
define(
    'K2_LOGIN_LOCKOUT_SECONDS',
    max(60, (int) ($_ENV['LOGIN_LOCKOUT_WINDOW'] ?? getenv('LOGIN_LOCKOUT_WINDOW') ?: '900'))
);

/** Contact form rate limit (per IP, sliding window) */
define(
    'K2_CONTACT_MAX_PER_IP',
    max(1, (int) ($_ENV['CONTACT_THROTTLE_MAX'] ?? getenv('CONTACT_THROTTLE_MAX') ?: '5'))
);
define(
    'K2_CONTACT_WINDOW_SECONDS',
    max(60, (int) ($_ENV['CONTACT_THROTTLE_WINDOW'] ?? getenv('CONTACT_THROTTLE_WINDOW') ?: '3600'))
);

/** Inbound notification address for contact form (required for email alert) */
define('K2_CONTACT_MAIL_TO', $_ENV['CONTACT_MAIL_TO'] ?? getenv('CONTACT_MAIL_TO') ?: '');

/** Blog listing */
define(
    'K2_BLOG_PER_PAGE',
    max(1, min(50, (int) ($_ENV['BLOG_PER_PAGE'] ?? getenv('BLOG_PER_PAGE') ?: '10')))
);
define(
    'K2_UPLOAD_MAX_IMAGE_BYTES',
    max(102400, (int) ($_ENV['UPLOAD_MAX_IMAGE_BYTES'] ?? getenv('UPLOAD_MAX_IMAGE_BYTES') ?: '3145728'))
);
