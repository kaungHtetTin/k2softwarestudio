<?php

declare(strict_types=1);

$rows = k2_app_list_visible();

$pageTitle = 'Apps';
$metaDescription = 'Apps and products showcased by K2.';
ob_start();
?>
<div class="k2-page-head border-0 bg-transparent px-0 pt-0">
    <div class="container py-4 py-lg-5">
        <p class="text-uppercase small text-muted mb-2 letter-spacing">Gallery</p>
        <h1 class="display-6 fw-bold text-dark mb-2">App gallery</h1>
        <p class="lead text-muted col-lg-8 mb-0">Explore what we ship — with screenshots and links.</p>
    </div>
</div>

<div class="container pb-5">
    <?php if ($rows === []) : ?>
        <p class="text-muted">No apps to show yet.</p>
    <?php else : ?>
        <div class="row g-4">
            <?php foreach ($rows as $row) :
                $slug = (string) ($row['slug'] ?? '');
                $link = k2_url('/apps/' . rawurlencode($slug));
                ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= k2_e($link) ?>" class="text-decoration-none text-reset d-block h-100">
                        <article class="card border-0 shadow-sm h-100 k2-blog-card">
                            <?php if (!empty($row['icon_path'])) : ?>
                                <div class="ratio ratio-1x1 bg-light overflow-hidden" style="max-height: 200px;">
                                    <img src="<?= k2_e(k2_asset((string) $row['icon_path'])) ?>" alt="" class="object-fit-cover" loading="lazy" width="400" height="400">
                                </div>
                            <?php else : ?>
                                <div class="ratio ratio-16x9 bg-light d-flex align-items-center justify-content-center text-muted">
                                    <i class="bi bi-grid-3x3-gap display-6" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h2 class="h5 mb-2 text-dark"><?= k2_e((string) ($row['title'] ?? '')) ?></h2>
                                <p class="text-muted small mb-0"><?= k2_e((string) ($row['short_description'] ?? '')) ?></p>
                            </div>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
