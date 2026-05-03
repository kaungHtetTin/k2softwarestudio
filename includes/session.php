<?php

declare(strict_types=1);

/**
 * Start session with secure cookie flags (§6.1). Call once per request before output.
 */
function k2_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $secureCookie = $https || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

    // Local HTTP: cookie Secure=false so the session still works without HTTPS.
    $params = session_get_cookie_params();
    $cookiePath = k2_session_cookie_path();

    session_name('k2_session');
    session_set_cookie_params([
        'lifetime' => (int) ($params['lifetime'] ?? 0),
        'path' => $cookiePath,
        'domain' => $params['domain'] ?? '',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Limit session cookie to the app base path when running in a subdirectory.
 */
function k2_session_cookie_path(): string
{
    $base = k2_base_path();
    if ($base === '') {
        return '/';
    }

    return $base . '/';
}
