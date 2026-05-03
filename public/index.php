<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

k2_send_security_headers();
k2_session_start();

$path = k2_request_path();

if (K2_DEBUG && $path === '/security-tests') {
    require_once K2_ROOT . '/includes/dev_security_tests.php';
    k2_dev_security_tests_route();
    exit;
}

if (str_starts_with($path, '/admin')) {
    require_once K2_ROOT . '/includes/admin_dispatch.php';
    k2_admin_dispatch($path);
    exit;
}

if ($path === '/') {
    require K2_ROOT . '/templates/home.php';
    exit;
}

if ($path === '/contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    k2_contact_handle_post();
    exit;
}

if ($path === '/contact') {
    require K2_ROOT . '/templates/contact.php';
    exit;
}

if ($path === '/privacy' || $path === '/privacy-policy') {
    require K2_ROOT . '/templates/legal/privacy.php';
    exit;
}

if ($path === '/terms' || $path === '/terms-of-service') {
    require K2_ROOT . '/templates/legal/terms.php';
    exit;
}

if ($path === '/blog') {
    require K2_ROOT . '/templates/blog/index.php';
    exit;
}

if ($path === '/apps') {
    require K2_ROOT . '/templates/apps/index.php';
    exit;
}

$appSlug = k2_app_slug_from_request_path($path);
if ($appSlug !== null) {
    require K2_ROOT . '/templates/apps/detail.php';
    exit;
}

if ($path === '/gallery') {
    require K2_ROOT . '/templates/gallery/index.php';
    exit;
}

if ($path === '/pricing') {
    require K2_ROOT . '/templates/pricing/index.php';
    exit;
}

$galleryAlbumSlug = k2_gallery_album_slug_from_request_path($path);
if ($galleryAlbumSlug !== null) {
    require K2_ROOT . '/templates/gallery/album.php';
    exit;
}

$blogSlug = k2_blog_slug_from_request_path($path);
if ($blogSlug !== null) {
    require K2_ROOT . '/templates/blog/post.php';
    exit;
}

http_response_code(404);
require K2_ROOT . '/templates/not-found.php';
exit;
