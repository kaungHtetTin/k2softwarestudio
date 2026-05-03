<?php

declare(strict_types=1);

/**
 * Request URI path relative to the front controller (leading slash, no trailing slash except root).
 */
function k2_request_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($uri) || $uri === '') {
        $uri = '/';
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = rtrim($scriptDir, '/');

    if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
        $path = substr($uri, strlen($scriptDir)) ?: '/';
    } else {
        $path = $uri;
    }

    $path = '/' . trim($path, '/');
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }

    return $path === '' ? '/' : $path;
}

/**
 * Base URL path for assets/links (e.g. /k2/public).
 */
function k2_base_path(): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    return rtrim($scriptDir, '/');
}

/**
 * Home URL path including subdirectory prefix (always ends with /).
 */
function k2_home_url(): string
{
    $base = k2_base_path();

    return $base === '' ? '/' : $base . '/';
}

/**
 * Absolute path on this host for redirects and links (leading slash), including subdirectory base.
 */
function k2_url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $base = k2_base_path();

    return ($base === '' ? '' : $base) . $path;
}

/**
 * URL to a static file under `public/` (works with subdirectory installs).
 */
function k2_asset(string $path): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $base = k2_base_path();

    return ($base === '' ? '' : $base) . '/' . $path;
}
