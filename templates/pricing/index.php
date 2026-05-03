<?php

declare(strict_types=1);

$plans = k2_pricing_plans_visible();

$pageTitle = 'Pricing';
$metaDescription = 'Project types, feature sets, and indicative pricing for K2 engagements.';
ob_start();
?>
<div class="k2-page-head border-0 bg-transparent px-0 pt-0">
    <div class="container py-4 py-lg-5">
        <p class="text-uppercase small text-muted mb-2 letter-spacing">Engagements</p>
        <h1 class="display-6 fw-bold text-dark mb-2">Pricing &amp; packages</h1>
        <p class="lead text-muted col-lg-9 mb-0">
            Indicative tiers by project type. Final quotes depend on scope, integrations, and timeline — use these as a starting point, then <a class="link-dark" href="<?= k2_e(k2_url('/contact')) ?>">tell us what you are building</a>.
        </p>
    </div>
</div>

<div class="container pb-5">
    <?php if ($plans === []) : ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5 text-center text-muted">
                <p class="mb-2">No pricing plans are published yet.</p>
                <p class="small mb-0">Add plans under <span class="text-nowrap">Admin → Pricing</span> when you are ready.</p>
            </div>
        </div>
    <?php else : ?>
        <div class="row g-4">
            <?php foreach ($plans as $plan) :
                $ptype = (string) ($plan['project_type'] ?? '');
                $title = (string) ($plan['title'] ?? '');
                $summary = (string) ($plan['summary'] ?? '');
                $priceDisp = (string) ($plan['price_display'] ?? '');
                $priceNote = trim((string) ($plan['price_note'] ?? ''));
                $demo = trim((string) ($plan['demo_image_path'] ?? ''));
                $extUrl = trim((string) ($plan['external_url'] ?? ''));
                $linkLab = trim((string) ($plan['link_label'] ?? ''));
                if ($linkLab === '') {
                    $linkLab = 'Learn more';
                }
                /** @var list<string> $features */
                $features = $plan['features'] ?? [];
                if (!is_array($features)) {
                    $features = [];
                }
                ?>
                <div class="col-lg-4">
                    <article class="card border-0 shadow-sm h-100 k2-pricing-card">
                        <?php if ($demo !== '') : ?>
                            <div class="ratio ratio-16x9 bg-light border-bottom overflow-hidden">
                                <img src="<?= k2_e(k2_asset($demo)) ?>" alt="" class="object-fit-cover" loading="lazy" width="640" height="360" decoding="async">
                            </div>
                        <?php else : ?>
                            <div class="ratio ratio-16x9 bg-light border-bottom d-flex align-items-center justify-content-center text-primary">
                                <i class="bi bi-image display-6 opacity-50" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column p-4">
                            <p class="small text-uppercase text-muted letter-spacing mb-1"><?= k2_e($ptype) ?></p>
                            <h2 class="h4 text-dark mb-2"><?= k2_e($title) ?></h2>
                            <p class="text-muted small mb-3"><?= k2_e($summary) ?></p>
                            <div class="mb-3">
                                <span class="display-6 fw-bold text-dark"><?= k2_e($priceDisp) ?></span>
                                <?php if ($priceNote !== '') : ?>
                                    <span class="d-block small text-muted mt-1"><?= k2_e($priceNote) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($features !== []) : ?>
                                <ul class="list-unstyled small mb-4 flex-grow-1">
                                    <?php foreach ($features as $feat) :
                                        $feat = trim((string) $feat);
                                        if ($feat === '') {
                                            continue;
                                        }
                                        ?>
                                        <li class="d-flex gap-2 mb-2">
                                            <span class="text-primary flex-shrink-0"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                                            <span><?= k2_e($feat) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-2 mt-auto pt-1">
                                <?php if ($extUrl !== '') : ?>
                                    <a class="btn btn-k2-accent" href="<?= k2_e($extUrl) ?>" rel="noopener noreferrer" target="_blank"><?= k2_e($linkLab) ?> <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i></a>
                                    <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/contact')) ?>">Ask about this</a>
                                <?php else : ?>
                                    <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/contact')) ?>">Ask about this</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
