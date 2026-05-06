<?php

declare(strict_types=1);

/**
 * Slug segment for /apps/{slug}.
 */
function k2_app_slug_from_request_path(string $path): ?string
{
    if ($path === '/apps') {
        return null;
    }
    if (!str_starts_with($path, '/apps/')) {
        return null;
    }
    $rest = substr($path, strlen('/apps/'));
    $rest = trim($rest, '/');
    if ($rest === '' || str_contains($rest, '/')) {
        return null;
    }

    return rawurldecode($rest);
}

function k2_app_unique_slug(PDO $pdo, string $slug, ?int $excludeId = null): string
{
    $base = k2_blog_slugify($slug);
    if ($base === '') {
        $base = 'app';
    }

    $n = 0;
    do {
        $candidate = $n === 0 ? $base : $base . '-' . $n;
        $sql = 'SELECT id FROM app_items WHERE slug = :slug';
        $params = [':slug' => $candidate];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if ($stmt->fetch() === false) {
            return $candidate;
        }
        ++$n;
    } while ($n < 5000);

    return $base . '-' . bin2hex(random_bytes(4));
}

/**
 * @return list<array<string, mixed>>
 */
function k2_app_list_visible(): array
{
    $pdo = k2_db();
    $stmt = $pdo->query(
        'SELECT id, title, slug, short_description, long_description, icon_path, external_url, sort_order,
                (
                    SELECT s.image_path
                    FROM app_screenshots s
                    WHERE s.app_id = app_items.id
                    ORDER BY s.sort_order ASC, s.id ASC
                    LIMIT 1
                ) AS cover_image_path
         FROM app_items
         WHERE is_visible = 1
         ORDER BY sort_order ASC, title ASC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Recently updated visible apps for the home page.
 *
 * @return list<array<string, mixed>>
 */
function k2_app_list_visible_recent(int $limit = 3): array
{
    $limit = max(1, min(12, $limit));
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT id, title, slug, short_description, long_description, icon_path, external_url, sort_order,
                (
                    SELECT s.image_path
                    FROM app_screenshots s
                    WHERE s.app_id = app_items.id
                    ORDER BY s.sort_order ASC, s.id ASC
                    LIMIT 1
                ) AS cover_image_path
         FROM app_items
         WHERE is_visible = 1
         ORDER BY updated_at DESC, id DESC
         LIMIT :lim'
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @return array<string, mixed>|null
 */
function k2_app_fetch_visible_by_slug(string $slug): ?array
{
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT * FROM app_items WHERE slug = :slug AND is_visible = 1 LIMIT 1'
    );
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

/**
 * @return list<array<string, mixed>>
 */
function k2_app_screenshots_for(int $appId): array
{
    if ($appId <= 0) {
        return [];
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT id, image_path, sort_order FROM app_screenshots WHERE app_id = :aid ORDER BY sort_order ASC, id ASC'
    );
    $stmt->execute([':aid' => $appId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
