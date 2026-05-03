<?php

declare(strict_types=1);

define('K2_ROOT', dirname(__DIR__));

require_once K2_ROOT . '/includes/env.php';
k2_load_env(K2_ROOT . '/.env');

require_once K2_ROOT . '/includes/config.php';
require_once K2_ROOT . '/includes/http.php';
require_once K2_ROOT . '/includes/layout_helpers.php';
require_once K2_ROOT . '/includes/html.php';
require_once K2_ROOT . '/includes/session.php';
require_once K2_ROOT . '/includes/csrf.php';
require_once K2_ROOT . '/includes/security_headers.php';
require_once K2_ROOT . '/includes/db.php';
require_once K2_ROOT . '/includes/rate_limit.php';

if (is_file(K2_ROOT . '/vendor/autoload.php')) {
    require_once K2_ROOT . '/vendor/autoload.php';
}

require_once K2_ROOT . '/includes/blog_core.php';
require_once K2_ROOT . '/includes/app_core.php';
require_once K2_ROOT . '/includes/photo_core.php';
require_once K2_ROOT . '/includes/faq_core.php';
require_once K2_ROOT . '/includes/deliverables_core.php';
require_once K2_ROOT . '/includes/pricing_core.php';

require_once K2_ROOT . '/includes/flash.php';
require_once K2_ROOT . '/includes/mail_contact.php';
require_once K2_ROOT . '/includes/contact.php';
require_once K2_ROOT . '/includes/contact_info.php';
require_once K2_ROOT . '/includes/admin_auth.php';
