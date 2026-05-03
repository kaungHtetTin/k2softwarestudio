<?php

declare(strict_types=1);

$pageTitle = 'Home';
$metaDescription = 'K2 builds secure, scalable software — web apps, integrations, and ongoing delivery your team can rely on.';
$faqItems = k2_faq_list_visible();
$deliverableCards = k2_deliverables_list_visible();
$homeBlogRows = k2_blog_fetch_published_recent(3);
$homeAppRows = k2_app_list_visible_recent(3);
ob_start();
?>
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-section-bg" aria-hidden="true">
        <div class="hero-section-bg__base"></div>
        <div class="hero-section-bg__pattern"></div>
    </div>
    <div class="k2-hero-emblem d-none d-lg-flex" aria-hidden="true">
        <span class="k2-hero-emblem__ring">
            <img src="<?= k2_e(k2_asset('assets/img/logo.png')) ?>" alt="" width="44" height="44" decoding="async" class="k2-hero-emblem__logo">
        </span>
    </div>
    <div class="container position-relative py-5 py-lg-6 hero-section-content">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7 k2-animate k2-hero-copy">
                <p class="text-uppercase small k2-hero-eyebrow mb-2 letter-spacing">Software development studio</p>
                <h1 class="display-4 fw-bold mb-3">Ship software that feels inevitable</h1>
                <p class="lead k2-hero-lead col-xl-10 mb-4">
                    From discovery to launch, we pair crisp engineering with operational discipline — so your product stays fast, safe, and easy to evolve.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-2">
                    <a class="btn btn-k2-accent btn-lg" href="<?= k2_e(k2_url('/contact')) ?>">Get in touch</a>
                    <a class="btn btn-k2-outline btn-lg" href="<?= k2_e(k2_url('/apps')) ?>">Explore apps</a>
                </div>
                <?php if (K2_DEBUG) : ?>
                    <p class="small text-muted mb-0 mt-3">
                        <a class="link-secondary text-decoration-underline" href="<?= k2_e(k2_url('/security-tests')) ?>">Security tests (dev)</a>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-lg-5 k2-animate k2-hero-visual">
                <figure class="k2-hero-figure mb-0">
                    <img
                        src="<?= k2_e(k2_hero_image_url()) ?>"
                        alt="Engineering and product delivery"
                        class="img-fluid rounded-4 k2-hero-img"
                        width="900"
                        height="560"
                        loading="eager"
                        decoding="async"
                        fetchpriority="high"
                    >
                </figure>
            </div>
        </div>
    </div>
</section>

<section id="services" class="py-5 bg-white">
    <div class="container py-lg-2">
        <div class="row justify-content-center text-center mb-5 k2-animate">
            <div class="col-lg-8">
                <h2 class="h1 fw-bold mb-3">What we deliver</h2>
                <p class="lead text-muted mb-0">Focused capabilities that map to how modern teams actually ship.</p>
            </div>
        </div>
        <div class="row g-4">
            <?php if ($deliverableCards === []) : ?>
                <div class="col-12">
                    <p class="text-muted small mb-0 text-center">No capability cards yet. Add them in <strong>Admin → Deliverables</strong>.</p>
                </div>
            <?php else : ?>
                <?php foreach ($deliverableCards as $card) :
                    $ic = k2_deliverable_icon_sanitize((string) ($card['icon_name'] ?? ''));
                    ?>
                    <div class="col-md-6 col-lg-4 k2-animate">
                        <div class="k2-card card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="k2-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-<?= k2_e($ic) ?> fs-4" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 fw-semibold"><?= k2_e((string) ($card['title'] ?? '')) ?></h3>
                                <p class="text-muted small mb-0"><?= k2_e((string) ($card['description'] ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-white border-top border-light" aria-label="Latest content">
    <div class="container py-lg-2">
        <div class="mb-5 k2-animate">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                <div>
                    <p class="text-uppercase small text-muted mb-1 letter-spacing">Gallery</p>
                    <h2 class="h1 fw-bold mb-0 text-dark">Latest apps</h2>
                </div>
                <a class="btn btn-outline-primary" href="<?= k2_e(k2_url('/apps')) ?>">View app gallery</a>
            </div>
            <?php if ($homeAppRows === []) : ?>
                <p class="text-muted mb-0">No apps to show yet.</p>
            <?php else : ?>
                <div class="row g-4">
                    <?php foreach ($homeAppRows as $row) :
                        $slug = (string) ($row['slug'] ?? '');
                        $link = k2_url('/apps/' . rawurlencode($slug));
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= k2_e($link) ?>" class="text-decoration-none text-reset d-block h-100">
                                <article class="card border-0 shadow-sm h-100 k2-blog-card bg-white">
                                    <?php if (!empty($row['icon_path'])) : ?>
                                        <div class="ratio ratio-1x1 bg-light overflow-hidden rounded-top" style="max-height: 200px;">
                                            <img src="<?= k2_e(k2_asset((string) $row['icon_path'])) ?>" alt="" class="object-fit-cover" loading="lazy" width="400" height="400">
                                        </div>
                                    <?php else : ?>
                                        <div class="ratio ratio-16x9 bg-light d-flex align-items-center justify-content-center text-muted rounded-top">
                                            <i class="bi bi-grid-3x3-gap display-6" aria-hidden="true"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h3 class="h5 mb-2 text-dark"><?= k2_e((string) ($row['title'] ?? '')) ?></h3>
                                        <p class="text-muted small mb-0"><?= k2_e((string) ($row['short_description'] ?? '')) ?></p>
                                    </div>
                                </article>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="k2-animate">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                <div>
                    <p class="text-uppercase small text-muted mb-1 letter-spacing">Journal</p>
                    <h2 class="h1 fw-bold mb-0 text-dark">Latest from the blog</h2>
                </div>
                <a class="btn btn-outline-primary" href="<?= k2_e(k2_url('/blog')) ?>">View all posts</a>
            </div>
            <?php if ($homeBlogRows === []) : ?>
                <p class="text-muted mb-0">No articles published yet.</p>
            <?php else : ?>
                <div class="row g-4">
                    <?php foreach ($homeBlogRows as $row) :
                        $slug = (string) ($row['slug'] ?? '');
                        $link = k2_url('/blog/' . rawurlencode($slug));
                        $ex = $row['excerpt'] ?? '';
                        if ($ex === null || trim((string) $ex) === '') {
                            $ex = k2_blog_excerpt_plain((string) ($row['body'] ?? ''));
                        } else {
                            $ex = strip_tags((string) $ex);
                        }
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= k2_e($link) ?>" class="text-decoration-none text-reset d-block h-100">
                                <article class="card border-0 shadow-sm h-100 k2-blog-card bg-white">
                                    <?php if (!empty($row['featured_image'])) : ?>
                                        <div class="ratio ratio-16x9 bg-light rounded-top overflow-hidden">
                                            <img src="<?= k2_e(k2_asset((string) $row['featured_image'])) ?>" alt="" class="object-fit-cover" loading="lazy" width="800" height="450">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <time class="small text-muted" datetime="<?= k2_e((string) ($row['published_at'] ?? '')) ?>"><?= k2_e((string) ($row['published_at'] ?? '')) ?></time>
                                        <h3 class="h5 mt-2 mb-2 text-dark"><?= k2_e((string) ($row['title'] ?? '')) ?></h3>
                                        <p class="text-muted small mb-0"><?= k2_e($ex) ?></p>
                                    </div>
                                </article>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5 k2-section-muted">
    <div class="container">
        <p class="text-uppercase small text-muted text-center mb-3 letter-spacing k2-animate">Trusted pattern</p>
        <div class="row row-cols-2 row-cols-md-4 g-4 align-items-center justify-content-center text-center k2-animate">
            <div class="col"><span class="k2-logo-pill d-inline-block py-3 px-4 rounded-3 w-100 text-muted small fw-semibold">Cloud-ready</span></div>
            <div class="col"><span class="k2-logo-pill d-inline-block py-3 px-4 rounded-3 w-100 text-muted small fw-semibold">API-first</span></div>
            <div class="col"><span class="k2-logo-pill d-inline-block py-3 px-4 rounded-3 w-100 text-muted small fw-semibold">Observability</span></div>
            <div class="col"><span class="k2-logo-pill d-inline-block py-3 px-4 rounded-3 w-100 text-muted small fw-semibold">Secure SDLC</span></div>
        </div>
    </div>
</section>

<section id="faq" class="py-5 bg-white">
    <div class="container py-lg-2">
        <div class="row justify-content-center mb-5 k2-animate">
            <div class="col-lg-8 text-center">
                <h2 class="h1 fw-bold mb-3">FAQ</h2>
                <p class="lead text-muted mb-0">Straight answers — we will refine these with your real positioning as the site grows.</p>
            </div>
        </div>
        <div class="row justify-content-center k2-animate">
            <div class="col-lg-9">
                <div class="accordion accordion-flush k2-faq shadow-sm rounded-4 overflow-hidden border" id="faqAccordion">
                    <?php if ($faqItems === []) : ?>
                        <p class="text-muted small mb-0 px-3 py-4">No FAQ entries yet. Add them in the admin under <strong>FAQ</strong>.</p>
                    <?php else : ?>
                        <?php
                        $faqCount = count($faqItems);
                        foreach ($faqItems as $fi => $faqRow) :
                            $fid = (int) ($faqRow['id'] ?? 0);
                            $collapseId = 'faq-item-' . $fid;
                            $isFirst = $fi === 0;
                            $isLast = $fi === $faqCount - 1;
                            ?>
                            <div class="accordion-item border-0<?= $isLast ? '' : ' border-bottom' ?>">
                                <h3 class="accordion-header">
                                    <button class="accordion-button fw-semibold<?= $isFirst ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= k2_e($collapseId) ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>" aria-controls="<?= k2_e($collapseId) ?>">
                                        <?= k2_e((string) ($faqRow['question'] ?? '')) ?>
                                    </button>
                                </h3>
                                <div id="<?= k2_e($collapseId) ?>" class="accordion-collapse collapse<?= $isFirst ? ' show' : '' ?>" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted small k2-blog-body">
                                        <?= (string) ($faqRow['answer'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 k2-cta-band text-white">
    <div class="container py-lg-2">
        <div class="row align-items-center gy-4 k2-animate">
            <div class="col-lg-8">
                <h2 class="h3 fw-bold mb-2">Ready to outline your next release?</h2>
                <p class="text-white-50 mb-0">Tell us about timelines, constraints, and what “done” looks like — we will respond with a clear next step.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-k2-accent btn-lg w-100 w-lg-auto" href="<?= k2_e(k2_url('/contact')) ?>">Start a conversation</a>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
