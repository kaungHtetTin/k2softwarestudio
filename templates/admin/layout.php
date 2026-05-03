<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $content */

$base = k2_base_path();
$assetBase = k2_e($base === '' ? '' : $base);
$titleEsc = k2_e($pageTitle);
$adminNav = $GLOBALS['adminNavActive'] ?? '';
$user = k2_admin_user();
$favicon = k2_favicon();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titleEsc ?> — Admin · K2</title>
    <link rel="icon" href="<?= k2_e($favicon['href']) ?>" type="<?= k2_e($favicon['type']) ?>" sizes="any">
    <meta name="theme-color" content="#092950">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="<?= $assetBase ?>/assets/css/app.css" rel="stylesheet">
    <link href="<?= $assetBase ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="k2-admin-body">
    <div class="container-fluid px-0">
        <div class="row g-0 min-vh-100">
            <aside class="k2-admin-sidebar col-lg-3 col-xl-2 d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10">
                    <a href="<?= k2_e(k2_url('/admin')) ?>" class="d-inline-block">
                        <img src="<?= k2_e(k2_logo_url()) ?>" alt="K2" class="k2-admin-logo" width="168" height="40" decoding="async" loading="lazy">
                    </a>
                    <p class="small text-white-50 mb-0 mt-2">Admin</p>
                </div>
                <nav class="nav flex-column px-2 py-3 gap-1 flex-grow-1" aria-label="Admin">
                    <?php
                    $nav = [
                        'dashboard' => ['/admin', 'Speedometer2', 'Dashboard'],
                        'blog' => ['/admin/blog', 'journal-text', 'Blog'],
                        'apps' => ['/admin/apps', 'grid-3x3-gap', 'Apps'],
                        'gallery' => ['/admin/gallery', 'images', 'Photos'],
                        'faq' => ['/admin/faq', 'patch-question', 'FAQ'],
                        'deliverables' => ['/admin/deliverables', 'grid-3x3-gap', 'Deliverables'],
                        'pricing' => ['/admin/pricing', 'currency-dollar', 'Pricing'],
                        'finance' => ['/admin/finance', 'wallet2', 'Finance'],
                        'contact_info' => ['/admin/contact-info', 'geo-alt', 'Contact info'],
                        'contacts' => ['/admin/contacts', 'envelope-open', 'Messages'],
                        'password' => ['/admin/password', 'key', 'Password'],
                    ];
                    foreach ($nav as $key => $item) :
                        [$href, $icon, $label] = $item;
                        $active = $adminNav === $key ? ' active' : '';
                        ?>
                        <a class="nav-link k2-admin-nav-link<?= k2_e($active) ?>" href="<?= k2_e(k2_url($href)) ?>">
                            <i class="bi bi-<?= k2_e($icon) ?>" aria-hidden="true"></i> <?= k2_e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a class="btn btn-outline-light btn-sm w-100 mb-2" href="<?= k2_e(k2_home_url()) ?>">
                        <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i> View site
                    </a>
                    <form method="post" action="<?= k2_e(k2_url('/admin/logout')) ?>">
                        <?= k2_csrf_field() ?>
                        <button type="submit" class="btn btn-light btn-sm w-100 text-dark">Sign out</button>
                    </form>
                </div>
            </aside>
            <div class="col-lg-9 col-xl-10 d-flex flex-column bg-white">
                <header class="k2-admin-topbar border-bottom px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h1 class="h5 mb-0 text-dark"><?= $titleEsc ?></h1>
                    <?php if ($user !== null) : ?>
                        <span class="small text-muted"><i class="bi bi-person-circle me-1" aria-hidden="true"></i><?= k2_e($user['email']) ?></span>
                    <?php endif; ?>
                </header>
                <main class="k2-admin-main flex-grow-1 p-4">
                    <?= $content ?>
                </main>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
