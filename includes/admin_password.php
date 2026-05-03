<?php

declare(strict_types=1);

function k2_admin_password_screen(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'POST') {
        k2_admin_password_handle_post();
        exit;
    }

    $flashForm = k2_flash_pull('admin_password_form');
    $errors = is_array($flashForm) && isset($flashForm['errors']) && is_array($flashForm['errors'])
        ? $flashForm['errors']
        : [];
    $flashNotice = k2_flash_pull('admin_password_notice');

    $GLOBALS['adminNavActive'] = 'password';
    $pageTitle = 'Change password';
    require K2_ROOT . '/templates/admin/password_change.php';
    exit;
}

function k2_admin_password_handle_post(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('admin_password_form', ['errors' => ['Invalid session. Try again.']]);
        header('Location: ' . k2_url('/admin/password'), true, 303);
        exit;
    }

    $user = k2_admin_user();
    if ($user === null) {
        header('Location: ' . k2_url('/admin/login'), true, 302);
        exit;
    }

    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['new_password_confirm'] ?? '');

    $errors = k2_admin_password_change_validate($current, $new, $confirm);
    if ($errors !== []) {
        k2_flash_set('admin_password_form', ['errors' => $errors]);
        header('Location: ' . k2_url('/admin/password'), true, 303);
        exit;
    }

    $result = k2_admin_change_password($user['id'], $current, $new);
    if ($result === 'wrong_password') {
        k2_flash_set('admin_password_form', ['errors' => ['Current password is incorrect.']]);
        header('Location: ' . k2_url('/admin/password'), true, 303);
        exit;
    }
    if ($result === 'error') {
        k2_flash_set('admin_password_form', ['errors' => ['Could not update password. Try again later.']]);
        header('Location: ' . k2_url('/admin/password'), true, 303);
        exit;
    }

    k2_flash_set('admin_password_notice', ['ok' => 'Your password has been updated.']);
    header('Location: ' . k2_url('/admin/password'), true, 303);
    exit;
}
