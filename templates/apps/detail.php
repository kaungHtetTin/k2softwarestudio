<?php

declare(strict_types=1);

$path = k2_request_path();
$slug = k2_app_slug_from_request_path($path);
if ($slug === null || $slug === '') {
    http_response_code(404);
    require K2_ROOT . '/templates/not-found.php';
    exit;
}

$app = k2_app_fetch_visible_by_slug($slug);
if ($app === null) {
    http_response_code(404);
    require K2_ROOT . '/templates/not-found.php';
    exit;
}

$appId = (int) ($app['id'] ?? 0);
$screenshots = k2_app_screenshots_for($appId);
$title = (string) ($app['title'] ?? 'App');
$pageTitle = $title;
$excerpt = trim(strip_tags((string) ($app['short_description'] ?? '')));
$metaDescription = $excerpt !== '' ? $excerpt : 'Details for ' . $title . ' — K2 app gallery.';

$external = trim((string) ($app['external_url'] ?? ''));
$longHtml = (string) ($app['long_description'] ?? '');

ob_start();
?>
<div class="k2-page-head border-0 bg-transparent px-0 pt-0">
    <div class="container py-4 py-lg-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= k2_e(k2_url('/apps')) ?>" class="text-decoration-none">Apps</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= k2_e($title) ?></li>
            </ol>
        </nav>
        <div class="row align-items-start g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold text-dark mb-3"><?= k2_e($title) ?></h1>
                <p class="lead text-muted mb-0"><?= k2_e((string) ($app['short_description'] ?? '')) ?></p>
            </div>
            <?php if (!empty($app['icon_path'])) : ?>
                <div class="col-lg-4 text-lg-end">
                    <img src="<?= k2_e(k2_asset((string) $app['icon_path'])) ?>" alt="" class="rounded-3 border shadow-sm" width="160" height="160" style="object-fit: cover;" loading="lazy">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if ($screenshots !== []) : ?>
        <div id="k2AppCarousel" class="carousel slide carousel-dark mb-5 shadow-sm rounded-3 overflow-hidden border">
            <div class="carousel-indicators">
                <?php foreach ($screenshots as $i => $_s) : ?>
                    <button type="button" data-bs-target="#k2AppCarousel" data-bs-slide-to="<?= (int) $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= (int) ($i + 1) ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($screenshots as $i => $s) : ?>
                    <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                        <div class="ratio ratio-21x9 bg-light">
                            <img src="<?= k2_e(k2_asset((string) ($s['image_path'] ?? ''))) ?>" class="d-block w-100 object-fit-contain" alt="" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#k2AppCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#k2AppCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($longHtml !== '') : ?>
        <div class="k2-blog-body col-lg-10 mb-4">
            <?= $longHtml ?>
        </div>
    <?php endif; ?>

    <?php if ($external !== '') : ?>
        <p class="mb-0">
            <a class="btn btn-k2-accent btn-lg" href="<?= k2_e($external) ?>" rel="noopener noreferrer" target="_blank">
                Open link <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
            </a>
        </p>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
