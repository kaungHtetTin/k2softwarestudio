<?php

declare(strict_types=1);

/**
 * Keys stored in `site_settings` for the public contact page.
 */
function k2_contact_info_keys(): array
{
    return [
        'contact_address',
        'contact_phone',
        'contact_facebook_url',
        'contact_telegram_url',
        'contact_tiktok_url',
        'contact_youtube_url',
    ];
}

/**
 * @return array{address: string, phone: string, facebook_url: string, telegram_url: string, tiktok_url: string, youtube_url: string}
 */
function k2_contact_info_all(): array
{
    $defaults = [
        'address' => '',
        'phone' => '',
        'facebook_url' => '',
        'telegram_url' => '',
        'tiktok_url' => '',
        'youtube_url' => '',
    ];

    $map = [
        'contact_address' => 'address',
        'contact_phone' => 'phone',
        'contact_facebook_url' => 'facebook_url',
        'contact_telegram_url' => 'telegram_url',
        'contact_tiktok_url' => 'tiktok_url',
        'contact_youtube_url' => 'youtube_url',
    ];

    try {
        $pdo = k2_db();
        $keys = k2_contact_info_keys();
        $in = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ($in)");
        $stmt->execute($keys);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!is_array($rows)) {
            return $defaults;
        }
    } catch (Throwable $e) {
        error_log('K2 contact info read: ' . $e->getMessage());

        return $defaults;
    }

    $out = $defaults;
    foreach ($map as $dbKey => $outKey) {
        if (isset($rows[$dbKey]) && is_string($rows[$dbKey])) {
            $out[$outKey] = $rows[$dbKey];
        }
    }

    return $out;
}

function k2_contact_info_upsert(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, updated_at)
         VALUES (:k, :v, NOW())
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = NOW()'
    );
    $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_contact_info_validate(array $data): array
{
    $errors = [];

    $urlFields = [
        'contact_facebook_url' => 'Facebook',
        'contact_telegram_url' => 'Telegram',
        'contact_tiktok_url' => 'TikTok',
        'contact_youtube_url' => 'YouTube',
    ];
    foreach ($urlFields as $k => $label) {
        $u = trim((string) ($data[$k] ?? ''));
        if ($u === '') {
            continue;
        }
        if (!filter_var($u, FILTER_VALIDATE_URL)) {
            $errors[] = $label . ' must be a valid URL.';
        } else {
            $scheme = strtolower((string) (parse_url($u, PHP_URL_SCHEME) ?? ''));
            if ($scheme !== 'http' && $scheme !== 'https') {
                $errors[] = $label . ' must use http:// or https://.';
            }
        }
    }

    if (mb_strlen(trim((string) ($data['contact_address'] ?? ''))) > 2000) {
        $errors[] = 'Address is too long (max 2000 characters).';
    }
    if (mb_strlen(trim((string) ($data['contact_phone'] ?? ''))) > 120) {
        $errors[] = 'Phone is too long (max 120 characters).';
    }

    return $errors;
}

/**
 * @param array<string, string> $data
 */
function k2_contact_info_save_all(array $data): void
{
    $pdo = k2_db();
    $fields = [
        'contact_address' => trim((string) ($data['contact_address'] ?? '')),
        'contact_phone' => trim((string) ($data['contact_phone'] ?? '')),
        'contact_facebook_url' => trim((string) ($data['contact_facebook_url'] ?? '')),
        'contact_telegram_url' => trim((string) ($data['contact_telegram_url'] ?? '')),
        'contact_tiktok_url' => trim((string) ($data['contact_tiktok_url'] ?? '')),
        'contact_youtube_url' => trim((string) ($data['contact_youtube_url'] ?? '')),
    ];
    foreach ($fields as $key => $val) {
        k2_contact_info_upsert($pdo, $key, $val);
    }
}
