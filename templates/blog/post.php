<?php

declare(strict_types=1);

$path = k2_request_path();
$slug = k2_blog_slug_from_request_path($path);
if ($slug === null || $slug === '') {
    http_response_code(404);
    require K2_ROOT . '/templates/not-found.php';
    exit;
}

$post = k2_blog_fetch_published_by_slug($slug);
if ($post === null) {
    http_response_code(404);
    require K2_ROOT . '/templates/not-found.php';
    exit;
}

$pageTitle = (string) ($post['title'] ?? 'Post');
$rawEx = $post['excerpt'] ?? '';
$metaDescription = is_string($rawEx) && trim($rawEx) !== ''
    ? strip_tags($rawEx)
    : k2_blog_excerpt_plain((string) ($post['body'] ?? ''));

ob_start();
?>
<article class="k2-blog-article">
    <header class="k2-page-head border-0 bg-transparent px-0 pt-0">
        <div class="container py-4 py-lg-5">
            <p class="small text-muted mb-2">
                <a href="<?= k2_e(k2_url('/blog')) ?>" class="link-secondary">Blog</a>
                <span aria-hidden="true"> / </span>
                <time datetime="<?= k2_e((string) ($post['published_at'] ?? '')) ?>"><?= k2_e((string) ($post['published_at'] ?? '')) ?></time>
            </p>
            <h1 class="display-6 fw-bold text-dark mb-3"><?= k2_e((string) ($post['title'] ?? '')) ?></h1>
        </div>
    </header>

    <?php if (!empty($post['featured_image'])) : ?>
        <div class="container mb-4">
            <div class="ratio ratio-21x9 rounded-3 overflow-hidden shadow-sm bg-light">
                <img src="<?= k2_e(k2_asset((string) $post['featured_image'])) ?>" alt="" class="object-fit-cover" width="1200" height="514" fetchpriority="high">
            </div>
        </div>
    <?php endif; ?>

    <div class="container pb-5 col-lg-8">
        <div class="k2-blog-body text-dark">
            <?= $post['body'] ?>
        </div>
    </div>
</article>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
