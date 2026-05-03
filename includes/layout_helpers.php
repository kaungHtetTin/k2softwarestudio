<?php

declare(strict_types=1);

/**
 * Primary navigation items for public layout (path => label).
 *
 * @return array<string, string>
 */
function k2_nav_items(): array
{
    return [
        '/' => 'Home',
        '/blog' => 'Blog',
        '/apps' => 'Apps',
        '/gallery' => 'Gallery',
        '/pricing' => 'Pricing',
        '/contact' => 'Contact',
    ];
}

function k2_nav_is_active(string $navPath, string $currentPath): bool
{
    if ($navPath === '/') {
        return $currentPath === '/';
    }

    return str_starts_with($currentPath, $navPath);
}

/**
 * Logo for dark backgrounds (navbar).
 * Primary file: `public/assets/logo.png`.
 */
function k2_logo_url(): string
{
    foreach ([
        'assets/logo.png',
        'assets/logo.webp',
        'assets/img/logo.png',
        'assets/img/logo.png',
        'assets/img/logo.webp',
        'assets/img/logo.jpg',
        'assets/img/logo.JPG',
        'assets/img/favicon.png',
    ] as $rel) {
        if (is_file(K2_ROOT . '/public/' . $rel)) {
            return k2_asset($rel);
        }
    }

    return k2_asset('assets/logo.png');
}

/**
 * Logo for light backgrounds (footer).
 * Uses `public/assets/logo.png` first; optional `assets/img/logo-dark.*` overrides when present.
 */
function k2_logo_dark_url(): string
{
    foreach ([
        'assets/logo.png',
        'assets/logo.webp',
        'assets/img/logo-dark.svg',
        'assets/img/logo-dark.png',
        'assets/img/logo-dark.webp',
        'assets/img/logo.png',
        'assets/img/favicon.png',
        'assets/img/logo.png',
        'assets/img/logo.jpg',
        'assets/img/logo.JPG',
    ] as $rel) {
        if (is_file(K2_ROOT . '/public/' . $rel)) {
            return k2_asset($rel);
        }
    }

    return k2_asset('assets/logo.png');
}

/**
 * Prefer raster hero when present; otherwise bundled SVG.
 */
function k2_hero_image_url(): string
{
    foreach (['assets/img/hero.webp', 'assets/img/hero.jpg', 'assets/img/hero.jpeg', 'assets/img/hero.png', 'assets/img/hero.svg'] as $rel) {
        if (is_file(K2_ROOT . '/public/' . $rel)) {
            return k2_asset($rel);
        }
    }

    return k2_asset('assets/img/hero.svg');
}

/**
 * @return array{href: string, type: string}
 */
function k2_favicon(): array
{
    if (is_file(K2_ROOT . '/public/assets/img/favicon.ico')) {
        return ['href' => k2_asset('assets/img/favicon.ico'), 'type' => 'image/x-icon'];
    }

    return ['href' => k2_asset('assets/img/favicon.svg'), 'type' => 'image/svg+xml'];
}

/**
 * Optional Apple touch icon when file exists.
 */
function k2_apple_touch_icon(): ?string
{
    $rel = 'assets/img/apple-touch-icon.png';
    if (is_file(K2_ROOT . '/public/' . $rel)) {
        return k2_asset($rel);
    }

    return null;
}
