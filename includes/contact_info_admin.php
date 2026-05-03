<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/contact_info.php';

function k2_contact_info_admin_screen(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'POST') {
        k2_contact_info_admin_handle_post();
        exit;
    }

    $info = k2_contact_info_all();
    $data = [
        'contact_address' => $info['address'],
        'contact_phone' => $info['phone'],
        'contact_facebook_url' => $info['facebook_url'],
        'contact_telegram_url' => $info['telegram_url'],
        'contact_tiktok_url' => $info['tiktok_url'],
        'contact_youtube_url' => $info['youtube_url'],
    ];
    $flash = k2_flash_pull('contact_info_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];
    if ($old !== []) {
        $data = array_merge($data, $old);
    }

    $flashAdmin = k2_flash_pull('contact_info_admin');
    $GLOBALS['adminNavActive'] = 'contact_info';
    $pageTitle = 'Contact page info';
    require K2_ROOT . '/templates/admin/contact_info_form.php';
    exit;
}

function k2_contact_info_admin_handle_post(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('contact_info_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/contact-info'), true, 303);
        exit;
    }

    $data = [
        'contact_address' => (string) ($_POST['contact_address'] ?? ''),
        'contact_phone' => (string) ($_POST['contact_phone'] ?? ''),
        'contact_facebook_url' => (string) ($_POST['contact_facebook_url'] ?? ''),
        'contact_telegram_url' => (string) ($_POST['contact_telegram_url'] ?? ''),
        'contact_tiktok_url' => (string) ($_POST['contact_tiktok_url'] ?? ''),
        'contact_youtube_url' => (string) ($_POST['contact_youtube_url'] ?? ''),
    ];

    $errors = k2_contact_info_validate($data);
    if ($errors !== []) {
        k2_flash_set('contact_info_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/contact-info'), true, 303);
        exit;
    }

    try {
        k2_contact_info_save_all($data);
    } catch (Throwable $e) {
        error_log('K2 contact info save: ' . $e->getMessage());
        k2_flash_set('contact_info_form', ['errors' => ['Could not save. Check the database.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/contact-info'), true, 303);
        exit;
    }

    k2_flash_set('contact_info_admin', ['ok' => 'Contact information saved.']);
    header('Location: ' . k2_url('/admin/contact-info'), true, 303);
    exit;
}
