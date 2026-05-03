<?php

declare(strict_types=1);

/**
 * CSRF protection — token stored in session (§6.1).
 * Requires k2_session_start() before use.
 */
function k2_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('Session must be started before CSRF token use.');
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/** POST field name for HTML forms */
function k2_csrf_field_name(): string
{
    return 'csrf_token';
}

function k2_csrf_field(): string
{
    $name = k2_csrf_field_name();
    $token = k2_csrf_token();

    return '<input type="hidden" name="' . k2_e($name) . '" value="' . k2_e($token) . '">';
}

/**
 * Validate CSRF token from POST body or X-CSRF-Token header.
 */
function k2_csrf_verify(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    if (!is_string($sessionToken) || $sessionToken === '') {
        return false;
    }

    $fromPost = $_POST[k2_csrf_field_name()] ?? null;
    $fromHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    $given = is_string($fromPost) && $fromPost !== '' ? $fromPost : (is_string($fromHeader) ? $fromHeader : '');

    return hash_equals($sessionToken, $given);
}
