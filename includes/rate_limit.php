<?php

declare(strict_types=1);

/**
 * File-based login attempt throttle by IP + identity (§6.1).
 * Uses storage/rate_limit/ — not for distributed setups without shared storage.
 */

function k2_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    return is_string($ip) ? $ip : '0.0.0.0';
}

function k2_login_bucket(string $identity): string
{
    $id = strtolower(trim($identity));

    return k2_client_ip() . '|' . $id;
}

function k2_rate_limit_dir(): string
{
    return K2_ROOT . '/storage/rate_limit';
}

function k2_rate_limit_file(string $bucket): string
{
    $safe = hash('sha256', $bucket);

    return k2_rate_limit_dir() . '/' . $safe . '.json';
}

/**
 * @return array{timestamps: list<int>}
 */
function k2_rate_limit_read(string $bucket): array
{
    $path = k2_rate_limit_file($bucket);
    if (!is_readable($path)) {
        return ['timestamps' => []];
    }

    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return ['timestamps' => []];
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['timestamps']) || !is_array($data['timestamps'])) {
        return ['timestamps' => []];
    }

    $timestamps = [];
    foreach ($data['timestamps'] as $t) {
        if (is_int($t) || (is_string($t) && ctype_digit($t))) {
            $timestamps[] = (int) $t;
        }
    }

    return ['timestamps' => $timestamps];
}

function k2_rate_limit_write(string $bucket, array $timestamps): void
{
    $dir = k2_rate_limit_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }

    $path = k2_rate_limit_file($bucket);
    $payload = json_encode(['timestamps' => array_values($timestamps)], JSON_THROW_ON_ERROR);
    file_put_contents($path, $payload, LOCK_EX);
}

/**
 * Whether another login attempt is allowed (under max failures in sliding window).
 */
function k2_login_allowed(string $identity): bool
{
    $bucket = k2_login_bucket($identity);
    $now = time();
    $window = K2_LOGIN_LOCKOUT_SECONDS;
    $max = K2_LOGIN_MAX_ATTEMPTS;

    $data = k2_rate_limit_read($bucket);
    $cutoff = $now - $window;
    $recent = array_values(array_filter($data['timestamps'], static fn (int $t): bool => $t >= $cutoff));

    return count($recent) < $max;
}

/**
 * Record a failed login attempt.
 */
function k2_login_register_failure(string $identity): void
{
    $bucket = k2_login_bucket($identity);
    $now = time();
    $window = K2_LOGIN_LOCKOUT_SECONDS;

    $data = k2_rate_limit_read($bucket);
    $cutoff = $now - $window;
    $recent = array_values(array_filter($data['timestamps'], static fn (int $t): bool => $t >= $cutoff));
    $recent[] = $now;

    k2_rate_limit_write($bucket, $recent);
}

/**
 * Clear failures after successful login.
 */
function k2_login_clear_failures(string $identity): void
{
    $bucket = k2_login_bucket($identity);
    $path = k2_rate_limit_file($bucket);
    if (is_file($path)) {
        unlink($path);
    }
}

/**
 * Status for UI / diagnostics: attempts in window, whether blocked, seconds until retry (approx).
 *
 * @return array{attempts:int,blocked:bool,retry_after:int|null,max:int,window:int}
 */
function k2_login_throttle_status(string $identity): array
{
    $bucket = k2_login_bucket($identity);
    $now = time();
    $window = K2_LOGIN_LOCKOUT_SECONDS;
    $max = K2_LOGIN_MAX_ATTEMPTS;

    $data = k2_rate_limit_read($bucket);
    $cutoff = $now - $window;
    $recent = array_values(array_filter($data['timestamps'], static fn (int $t): bool => $t >= $cutoff));
    sort($recent);
    $count = count($recent);
    $blocked = $count >= $max;

    $retryAfter = null;
    if ($blocked && $recent !== []) {
        $oldest = $recent[0];
        $retryAfter = max(0, ($oldest + $window) - $now);
    }

    return [
        'attempts' => $count,
        'blocked' => $blocked,
        'retry_after' => $retryAfter,
        'max' => $max,
        'window' => $window,
    ];
}

function k2_contact_bucket(): string
{
    return 'contact|' . k2_client_ip();
}

/**
 * Whether this IP may submit another contact form (under max per window).
 */
function k2_contact_allowed(): bool
{
    $bucket = k2_contact_bucket();
    $now = time();
    $window = K2_CONTACT_WINDOW_SECONDS;
    $max = K2_CONTACT_MAX_PER_IP;

    $data = k2_rate_limit_read($bucket);
    $cutoff = $now - $window;
    $recent = array_values(array_filter($data['timestamps'], static fn (int $t): bool => $t >= $cutoff));

    return count($recent) < $max;
}

/**
 * Record a successful contact submission for rate limiting.
 */
function k2_contact_register_submission(): void
{
    $bucket = k2_contact_bucket();
    $now = time();
    $window = K2_CONTACT_WINDOW_SECONDS;

    $data = k2_rate_limit_read($bucket);
    $cutoff = $now - $window;
    $recent = array_values(array_filter($data['timestamps'], static fn (int $t): bool => $t >= $cutoff));
    $recent[] = $now;

    k2_rate_limit_write($bucket, $recent);
}
