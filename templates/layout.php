<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $content */

$base = k2_base_path();
$currentPath = k2_request_path();
$titleEsc = k2_e($pageTitle);
$assetBase = k2_e($base === '' ? '' : $base);
$homeHref = k2_e(k2_home_url());
$faqHref = k2_e(k2_home_url() . '#faq');
$metaDesc = isset($metaDescription) && is_string($metaDescription) ? k2_e($metaDescription) : '';

$footerContact = k2_contact_info_all();
$footerAddr = trim($footerContact['address'] ?? '');
$footerPhone = trim($footerContact['phone'] ?? '');
$footerFb = trim($footerContact['facebook_url'] ?? '');
$footerTg = trim($footerContact['telegram_url'] ?? '');
$footerTt = trim($footerContact['tiktok_url'] ?? '');
$footerYt = trim($footerContact['youtube_url'] ?? '');
$footerHasDetail = $footerAddr !== '' || $footerPhone !== '' || $footerFb !== '' || $footerTg !== '' || $footerTt !== '' || $footerYt !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($metaDesc !== '') : ?>
        <meta name="description" content="<?= $metaDesc ?>">
    <?php endif; ?>
    <title><?= $titleEsc ?> — K2</title>
    <?php
    $favicon = k2_favicon();
    $appleTouch = k2_apple_touch_icon();
    ?>
    <link rel="icon" href="<?= k2_e($favicon['href']) ?>" type="<?= k2_e($favicon['type']) ?>" sizes="any">
    <?php if ($appleTouch !== null) : ?>
        <link rel="apple-touch-icon" href="<?= k2_e($appleTouch) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#092950">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="<?= $assetBase ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <a class="visually-hidden-focusable btn btn-sm btn-k2-accent position-fixed top-0 start-0 m-2" href="#main-content" style="z-index: 1080;">Skip to content</a>

    <header class="k2-navbar navbar navbar-expand-lg navbar-dark sticky-top">
        <nav class="container" aria-label="Primary">
            <a class="navbar-brand d-flex align-items-center py-1" href="<?= $homeHref ?>">
                <img src="<?= k2_e(k2_logo_url()) ?>" alt="K2 Software" class="k2-logo" width="168" height="40" decoding="async" fetchpriority="high">
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#k2Nav" aria-controls="k2Nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="k2Nav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                    <?php foreach (k2_nav_items() as $navPath => $label) :
                        $href = k2_e(k2_url($navPath));
                        $active = k2_nav_is_active($navPath, $currentPath);
                        $itemClass = 'nav-item';
                        $linkClass = 'nav-link rounded-pill px-3' . ($active ? ' active' : '');
                        ?>
                        <li class="<?= k2_e($itemClass) ?>">
                            <a class="<?= k2_e($linkClass) ?>" <?= $active ? 'aria-current="page"' : '' ?> href="<?= $href ?>"><?= k2_e($label) ?></a>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3" href="<?= $faqHref ?>">FAQ</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main id="main-content">
        <?= $content ?>
    </main>

    <footer class="k2-footer border-top mt-auto bg-white">
        <div class="container py-5">
            <div class="row g-4 g-lg-5">
                <div class="col-md-4">
                    <div class="mb-3">
                        <img src="<?= k2_e(k2_logo_dark_url()) ?>" alt="K2 Software" class="k2-logo-footer" width="168" height="40" decoding="async" loading="lazy">
                    </div>
                    <p class="small text-muted mb-0">We design and ship reliable web and mobile experiences — security-first, performance-minded, and built to match your roadmap.</p>
                </div>
                <div class="col-6 col-md-2">
                    <h2 class="h6 text-uppercase text-muted mb-3 small">Product</h2>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/apps')) ?>">App gallery</a></li>
                        <li class="mb-2"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/gallery')) ?>">Photo gallery</a></li>
                        <li class="mb-0"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/pricing')) ?>">Pricing</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-2">
                    <h2 class="h6 text-uppercase text-muted mb-3 small">Company</h2>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/blog')) ?>">Blog</a></li>
                        <li class="mb-0"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/contact')) ?>">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h2 class="h6 text-uppercase text-muted mb-3 small">Contact</h2>
                    <?php if ($footerHasDetail) : ?>
                        <?php if ($footerAddr !== '') : ?>
                            <div class="d-flex gap-2 mb-3">
                                <span class="flex-shrink-0 text-primary"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                <div class="small text-muted"><?= nl2br(k2_e($footerAddr), false) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($footerPhone !== '') : ?>
                            <?php
                            $telDigits = preg_replace('/[^\d\+]/u', '', $footerPhone) ?? '';
                            ?>
                            <div class="d-flex gap-2 mb-3">
                                <span class="flex-shrink-0 text-primary"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                <div class="small">
                                    <?php if ($telDigits !== '') : ?>
                                        <a class="link-dark text-decoration-none link-underline-opacity-25 link-underline-opacity-75-hover" href="<?= k2_e('tel:' . $telDigits) ?>"><?= k2_e($footerPhone) ?></a>
                                    <?php else : ?>
                                        <?= k2_e($footerPhone) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($footerFb !== '' || $footerTg !== '' || $footerTt !== '' || $footerYt !== '') : ?>
                            <ul class="list-unstyled small mb-3 d-flex flex-wrap gap-2 align-items-center">
                                <?php if ($footerFb !== '') : ?>
                                    <li>
                                        <a class="link-dark text-decoration-none d-inline-flex align-items-center gap-1" href="<?= k2_e($footerFb) ?>" rel="noopener noreferrer" target="_blank">
                                            <img src="<?= k2_e(k2_asset('assets/img/ic_facebook.svg')) ?>" alt="" width="16" height="16" decoding="async" loading="lazy">
                                            <span>Facebook</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($footerTg !== '') : ?>
                                    <li>
                                        <a class="link-dark text-decoration-none d-inline-flex align-items-center gap-1" href="<?= k2_e($footerTg) ?>" rel="noopener noreferrer" target="_blank">
                                            <img src="<?= k2_e(k2_asset('assets/img/ic_telegram.svg')) ?>" alt="" width="16" height="16" decoding="async" loading="lazy">
                                            <span>Telegram</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($footerTt !== '') : ?>
                                    <li>
                                        <a class="link-dark text-decoration-none d-inline-flex align-items-center gap-1" href="<?= k2_e($footerTt) ?>" rel="noopener noreferrer" target="_blank">
                                            <img src="<?= k2_e(k2_asset('assets/img/ic_tiktok.svg')) ?>" alt="" width="16" height="16" decoding="async" loading="lazy">
                                            <span>TikTok</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($footerYt !== '') : ?>
                                    <li>
                                        <a class="link-dark text-decoration-none d-inline-flex align-items-center gap-1" href="<?= k2_e($footerYt) ?>" rel="noopener noreferrer" target="_blank">
                                            <img src="<?= k2_e(k2_asset('assets/img/ic_youtube.svg')) ?>" alt="" width="16" height="16" decoding="async" loading="lazy">
                                            <span>YouTube</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="small text-muted mb-3">Reach us through the contact form — or add address, phone, and social links under <span class="text-nowrap">Admin → Contact info</span>.</p>
                    <?php endif; ?>
                    <p class="small mb-4">
                        <a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/contact')) ?>"><i class="bi bi-envelope-open me-1" aria-hidden="true"></i>Send a message</a>
                    </p>

                    <h2 class="h6 text-uppercase text-muted mb-3 small mt-4 pt-1 border-top">Legal</h2>
                    <ul class="list-unstyled small mb-3">
                        <li class="mb-2"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/privacy')) ?>">Privacy policy</a></li>
                        <li class="mb-0"><a class="link-dark link-underline-opacity-0 link-underline-opacity-75-hover" href="<?= k2_e(k2_url('/terms')) ?>">Terms of service</a></li>
                    </ul>
                    <p class="small text-muted mb-0">&copy; <?= k2_e((string) date('Y')) ?> K2. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?= $assetBase ?>/assets/js/app.js" defer></script>
</body>
</html>
