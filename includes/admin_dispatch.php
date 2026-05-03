<?php

declare(strict_types=1);

function k2_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/login') {
        if ($method === 'POST') {
            k2_admin_handle_login_post();
            exit;
        }
        k2_admin_login_page();
        exit;
    }

    if ($path === '/admin/logout') {
        if ($method === 'POST') {
            k2_admin_handle_logout_post();
            exit;
        }
        header('Location: ' . k2_url('/admin/login'), true, 302);
        exit;
    }

    k2_admin_require();

    if (str_starts_with($path, '/admin/blog')) {
        require_once K2_ROOT . '/includes/blog_admin.php';
        k2_blog_admin_dispatch($path);
        exit;
    }

    if ($path === '/admin' || $path === '/admin/') {
        $GLOBALS['adminNavActive'] = 'dashboard';
        require K2_ROOT . '/templates/admin/dashboard.php';
        exit;
    }

    if ($path === '/admin/contacts') {
        $GLOBALS['adminNavActive'] = 'contacts';
        require K2_ROOT . '/templates/admin/contacts.php';
        exit;
    }

    if (str_starts_with($path, '/admin/apps')) {
        require_once K2_ROOT . '/includes/app_admin.php';
        k2_app_admin_dispatch($path);
        exit;
    }

    if (str_starts_with($path, '/admin/gallery')) {
        require_once K2_ROOT . '/includes/photo_admin.php';
        k2_photo_admin_dispatch($path);
        exit;
    }

    if (str_starts_with($path, '/admin/faq')) {
        require_once K2_ROOT . '/includes/faq_admin.php';
        k2_faq_admin_dispatch($path);
        exit;
    }

    if (str_starts_with($path, '/admin/deliverables')) {
        require_once K2_ROOT . '/includes/deliverables_admin.php';
        k2_deliverables_admin_dispatch($path);
        exit;
    }

    if (str_starts_with($path, '/admin/pricing')) {
        require_once K2_ROOT . '/includes/pricing_admin.php';
        k2_pricing_admin_dispatch($path);
        exit;
    }

    if (str_starts_with($path, '/admin/finance')) {
        require_once K2_ROOT . '/includes/finance_admin.php';
        k2_finance_admin_dispatch($path);
        exit;
    }

    if ($path === '/admin/contact-info') {
        require_once K2_ROOT . '/includes/contact_info_admin.php';
        k2_contact_info_admin_screen();
        exit;
    }

    http_response_code(404);
    $GLOBALS['adminNavActive'] = '';
    $pageTitle = 'Not found';
    ob_start();
    ?>
    <div class="alert alert-warning border-0 mb-0">This admin page does not exist yet.</div>
    <?php
    $content = ob_get_clean();
    require K2_ROOT . '/templates/admin/layout.php';
    exit;
}

function k2_admin_login_page(): void
{
    if (k2_admin_logged_in()) {
        header('Location: ' . k2_url('/admin'), true, 302);
        exit;
    }

    require K2_ROOT . '/templates/admin/login.php';
}

function k2_admin_handle_login_post(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('admin_login', ['error' => 'Invalid session. Refresh the page and try again.']);
        header('Location: ' . k2_url('/admin/login'), true, 303);
        exit;
    }

    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (k2_admin_attempt_login($email, $password)) {
        header('Location: ' . k2_url('/admin'), true, 303);
        exit;
    }

    k2_flash_set('admin_login', ['error' => 'Invalid credentials.']);
    header('Location: ' . k2_url('/admin/login'), true, 303);
    exit;
}

function k2_admin_handle_logout_post(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    k2_admin_logout();
    header('Location: ' . k2_url('/admin/login'), true, 303);
    exit;
}

/**
 * @param 'blog'|'apps'|'gallery' $nav
 */
function k2_admin_stub_screen(string $title, string $body, string $nav): void
{
    $GLOBALS['adminNavActive'] = $nav;
    $pageTitle = $title;
    ob_start();
    ?>
    <div class="k2-page-head border-0 bg-transparent px-0 pt-0">
        <h1 class="h3 fw-bold text-dark mb-2"><?= k2_e($title) ?></h1>
        <p class="text-muted mb-0 col-lg-8"><?= k2_e($body) ?></p>
    </div>
    <?php
    $content = ob_get_clean();
    require K2_ROOT . '/templates/admin/layout.php';
    exit;
}
