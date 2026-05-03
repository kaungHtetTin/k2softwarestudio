<?php

declare(strict_types=1);

$albums = k2_photo_album_list_with_covers();

$pageTitle = 'Photo gallery';
$metaDescription = 'Browse photo albums from K2.';
ob_start();
?>
<div class="k2-page-head border-0 bg-transparent px-0 pt-0">
    <div class="container py-4 py-lg-5">
        <p class="text-uppercase small text-muted mb-2 letter-spacing">Gallery</p>
        <h1 class="display-6 fw-bold text-dark mb-2">Photo gallery</h1>
        <p class="lead text-muted col-lg-8 mb-0">Albums and highlights — click through for full images.</p>
    </div>
</div>

<div class="container pb-5">
    <?php if ($albums === []) : ?>
        <p class="text-muted">No albums yet.</p>
    <?php else : ?>
        <div class="row g-4">
            <?php foreach ($albums as $row) :
                $slug = (string) ($row['slug'] ?? '');
                $link = k2_url('/gallery/' . rawurlencode($slug));
                ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= k2_e($link) ?>" class="text-decoration-none text-reset d-block h-100">
                        <article class="card border-0 shadow-sm h-100 k2-blog-card">
                            <?php if (!empty($row['cover_path'])) : ?>
                                <div class="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                                    <img src="<?= k2_e(k2_asset((string) $row['cover_path'])) ?>" alt="" class="object-fit-cover" loading="lazy" width="800" height="600">
                                </div>
                            <?php else : ?>
                                <div class="ratio ratio-4x3 bg-light d-flex align-items-center justify-content-center text-muted rounded-top">
                                    <i class="bi bi-images display-6" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h2 class="h5 mb-0 text-dark"><?= k2_e((string) ($row['title'] ?? '')) ?></h2>
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
