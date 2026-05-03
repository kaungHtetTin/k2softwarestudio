<?php

declare(strict_types=1);

$page = max(1, (int) ($_GET['page'] ?? 1));
$data = k2_blog_fetch_published_page($page);
$rows = $data['rows'];
$total = $data['total'];
$pageNum = $data['page'];
$totalPages = $data['pages'];

$pageTitle = 'Blog';
$metaDescription = 'Articles and updates from K2.';
ob_start();
?>
<div class="k2-page-head border-0 bg-transparent px-0 pt-0">
    <div class="container py-4 py-lg-5">
        <p class="text-uppercase small text-muted mb-2 letter-spacing">Journal</p>
        <h1 class="display-6 fw-bold text-dark mb-2">Blog</h1>
        <p class="lead text-muted col-lg-8 mb-0">Notes on shipping software, security, and delivery.</p>
    </div>
</div>

<div class="container pb-5">
    <?php if ($rows === []) : ?>
        <p class="text-muted">No articles published yet.</p>
    <?php else : ?>
        <div class="row g-4">
            <?php foreach ($rows as $row) :
                $slug = (string) ($row['slug'] ?? '');
                $link = k2_url('/blog/' . rawurlencode($slug));
                $ex = $row['excerpt'] ?? '';
                if ($ex === null || trim((string) $ex) === '') {
                    $ex = k2_blog_excerpt_plain((string) ($row['body'] ?? ''));
                } else {
                    $ex = strip_tags((string) $ex);
                }
                ?>
                <div class="col-md-6">
                    <a href="<?= k2_e($link) ?>" class="text-decoration-none text-reset d-block h-100">
                        <article class="card border-0 shadow-sm h-100 k2-blog-card">
                            <?php if (!empty($row['featured_image'])) : ?>
                                <div class="ratio ratio-16x9 bg-light rounded-top overflow-hidden">
                                    <img src="<?= k2_e(k2_asset((string) $row['featured_image'])) ?>" alt="" class="object-fit-cover" loading="lazy" width="800" height="450">
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <time class="small text-muted" datetime="<?= k2_e((string) ($row['published_at'] ?? '')) ?>"><?= k2_e((string) ($row['published_at'] ?? '')) ?></time>
                                <h2 class="h5 mt-2 mb-2 text-dark"><?= k2_e((string) ($row['title'] ?? '')) ?></h2>
                                <p class="text-muted small mb-0"><?= k2_e($ex) ?></p>
                            </div>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1) : ?>
            <nav class="mt-5 d-flex justify-content-center" aria-label="Blog pagination">
                <ul class="pagination mb-0">
                    <?php if ($pageNum > 1) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= k2_e(k2_url('/blog') . '?page=' . ($pageNum - 1)) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <li class="page-item disabled"><span class="page-link">Page <?= (int) $pageNum ?> / <?= (int) $totalPages ?></span></li>
                    <?php if ($pageNum < $totalPages) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= k2_e(k2_url('/blog') . '?page=' . ($pageNum + 1)) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
