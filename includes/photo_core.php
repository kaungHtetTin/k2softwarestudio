<?php

declare(strict_types=1);

/**
 * Album slug segment for /gallery/{slug}.
 */
function k2_gallery_album_slug_from_request_path(string $path): ?string
{
    if ($path === '/gallery') {
        return null;
    }
    if (!str_starts_with($path, '/gallery/')) {
        return null;
    }
    $rest = substr($path, strlen('/gallery/'));
    $rest = trim($rest, '/');
    if ($rest === '' || str_contains($rest, '/')) {
        return null;
    }

    return rawurldecode($rest);
}

function k2_photo_album_unique_slug(PDO $pdo, string $slug, ?int $excludeId = null): string
{
    $base = k2_blog_slugify($slug);
    if ($base === '') {
        $base = 'album';
    }

    $n = 0;
    do {
        $candidate = $n === 0 ? $base : $base . '-' . $n;
        $sql = 'SELECT id FROM photo_albums WHERE slug = :slug';
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
 * Albums for public index with optional cover image (first visible photo).
 *
 * @return list<array<string, mixed>>
 */
function k2_photo_album_list_with_covers(): array
{
    $pdo = k2_db();
    $stmt = $pdo->query(
        'SELECT a.id, a.title, a.slug, a.sort_order, a.created_at,
            (SELECT p.image_path FROM photos p
             WHERE p.album_id = a.id AND p.is_visible = 1
             ORDER BY p.sort_order ASC, p.id ASC LIMIT 1) AS cover_path
         FROM photo_albums a
         ORDER BY a.sort_order ASC, a.title ASC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @return array<string, mixed>|null
 */
function k2_photo_album_fetch_by_slug(string $slug): ?array
{
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT id, title, slug, sort_order, created_at FROM photo_albums WHERE slug = :slug LIMIT 1'
    );
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

/**
 * Visible photos for public album view.
 *
 * @return list<array<string, mixed>>
 */
function k2_photo_list_visible_for_album(int $albumId): array
{
    if ($albumId <= 0) {
        return [];
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT id, image_path, caption, sort_order
         FROM photos
         WHERE album_id = :aid AND is_visible = 1
         ORDER BY sort_order ASC, id ASC'
    );
    $stmt->execute([':aid' => $albumId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * All photos for an album (admin).
 *
 * @return list<array<string, mixed>>
 */
function k2_photo_list_for_album_admin(int $albumId): array
{
    if ($albumId <= 0) {
        return [];
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT id, album_id, image_path, caption, sort_order, is_visible
         FROM photos
         WHERE album_id = :aid
         ORDER BY sort_order ASC, id ASC'
    );
    $stmt->execute([':aid' => $albumId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
