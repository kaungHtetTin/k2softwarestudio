<?php

declare(strict_types=1);

/**
 * Lowercase trim and strip an optional `bi-` prefix (UI often copies the full class fragment).
 */
function k2_deliverable_icon_normalize_slug(string $raw): string
{
    $s = strtolower(trim($raw));
    if (str_starts_with($s, 'bi-')) {
        $s = substr($s, 3);
    }

    return $s;
}

/**
 * Sanitize Bootstrap Icons slug for class `bi-{slug}` (alphanumeric + hyphen only).
 */
function k2_deliverable_icon_sanitize(string $raw): string
{
    $s = k2_deliverable_icon_normalize_slug($raw);
    if ($s === '') {
        return 'layers';
    }
    if (!preg_match('/^[a-z0-9][a-z0-9-]{0,47}$/', $s)) {
        return 'layers';
    }

    return $s;
}

/**
 * Cards for the home page “What we deliver” section.
 *
 * @return list<array<string, mixed>>
 */
function k2_deliverables_list_visible(): array
{
    try {
        $pdo = k2_db();
        $stmt = $pdo->query(
            'SELECT id, title, description, icon_name, sort_order
             FROM home_deliverables
             WHERE is_visible = 1
             ORDER BY sort_order ASC, id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('K2 deliverables list: ' . $e->getMessage());

        return [];
    }
}
