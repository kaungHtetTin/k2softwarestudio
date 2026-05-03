<?php

declare(strict_types=1);

function k2_blog_purify_html(string $html): string
{
    $cacheDir = K2_ROOT . '/storage/cache/htmlpurifier';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0770, true);
    }

    $config = \HTMLPurifier_Config::createDefault();
    $config->set('Cache.SerializerPath', $cacheDir);
    $config->set('HTML.Allowed', implode(',', [
        'p', 'br', 'strong', 'em', 'u', 's', 'sub', 'sup',
        'h2', 'h3', 'h4',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a[href|title|target|rel]',
        'img[src|alt|title|width|height|class]',
        'figure', 'figcaption',
        'hr',
    ]));
    $config->set('HTML.TargetBlank', true);
    $config->set('HTML.Nofollow', true);

    $purifier = new \HTMLPurifier($config);

    return $purifier->purify($html);
}

function k2_blog_slugify(string $title): string
{
    $s = strtolower(trim($title));
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s ?? '') ?? '';
    $s = trim($s, '-');

    return $s !== '' ? $s : 'post';
}

/**
 * Ensure slug is unique in blog_posts.
 */
function k2_blog_unique_slug(PDO $pdo, string $slug, ?int $excludeId = null): string
{
    $base = $slug;
    $n = 0;
    do {
        $candidate = $n === 0 ? $base : $base . '-' . $n;
        $sql = 'SELECT id FROM blog_posts WHERE slug = :slug';
        $params = [':slug' => $candidate];
        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $exists = $stmt->fetch() !== false;
        if (!$exists) {
            return $candidate;
        }
        ++$n;
    } while ($n < 5000);

    return $base . '-' . bin2hex(random_bytes(4));
}

/**
 * Single segment slug after /blog/ — null if not a post URL.
 */
function k2_blog_slug_from_request_path(string $path): ?string
{
    if ($path === '/blog') {
        return null;
    }
    if (!str_starts_with($path, '/blog/')) {
        return null;
    }
    $rest = substr($path, strlen('/blog/'));
    $rest = trim($rest, '/');
    if ($rest === '' || str_contains($rest, '/')) {
        return null;
    }

    return rawurldecode($rest);
}

/**
 * @return array<string, mixed>|null
 */
function k2_blog_fetch_published_by_slug(string $slug): ?array
{
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT * FROM blog_posts WHERE slug = :slug AND status = :st LIMIT 1'
    );
    $stmt->execute([':slug' => $slug, ':st' => 'published']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

/**
 * @return array{rows: list<array<string, mixed>>, total: int, page: int, pages: int}
 */
function k2_blog_fetch_published_page(int $page): array
{
    $per = K2_BLOG_PER_PAGE;
    $page = max(1, $page);
    $offset = ($page - 1) * $per;

    $pdo = k2_db();
    $total = (int) $pdo->query(
        "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'"
    )->fetchColumn();

    $pages = max(1, (int) ceil($total / $per));

    $stmt = $pdo->prepare(
        'SELECT id, title, slug, excerpt, featured_image, published_at
         FROM blog_posts
         WHERE status = :st
         ORDER BY published_at DESC
         LIMIT :lim OFFSET :off'
    );
    $stmt->bindValue(':st', 'published', PDO::PARAM_STR);
    $stmt->bindValue(':lim', $per, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'rows' => $rows,
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
    ];
}

/**
 * Recent published posts for the home page (newest first).
 *
 * @return list<array<string, mixed>>
 */
function k2_blog_fetch_published_recent(int $limit = 3): array
{
    $limit = max(1, min(12, $limit));
    $pdo = k2_db();
    $stmt = $pdo->prepare(
        'SELECT id, title, slug, excerpt, body, featured_image, published_at
         FROM blog_posts
         WHERE status = :st
         ORDER BY COALESCE(published_at, created_at) DESC, id DESC
         LIMIT :lim'
    );
    $stmt->bindValue(':st', 'published', PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function k2_blog_excerpt_plain(?string $html, int $max = 180): string
{
    if ($html === null || $html === '') {
        return '';
    }
    $text = strip_tags($html);
    $text = preg_replace('/\s+/u', ' ', $text) ?? '';
    $text = trim($text);
    if (function_exists('mb_strlen') && mb_strlen($text) > $max) {
        return mb_substr($text, 0, $max) . '…';
    }
    if (strlen($text) > $max) {
        return substr($text, 0, $max) . '…';
    }

    return $text;
}
