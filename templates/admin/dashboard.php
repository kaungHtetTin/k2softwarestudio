<?php

declare(strict_types=1);

$pageTitle = 'Dashboard';

ob_start();
?>
<div class="row g-4">
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/blog')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-journal-text fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Blog</h2>
                    </div>
                    <p class="small text-muted mb-0">Manage posts and drafts — Phase 5.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/apps')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-grid-3x3-gap fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">App gallery</h2>
                    </div>
                    <p class="small text-muted mb-0">Apps &amp; screenshots — Phase 6.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/gallery')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-images fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Photo gallery</h2>
                    </div>
                    <p class="small text-muted mb-0">Albums &amp; uploads — Phase 7.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/faq')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-patch-question fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">FAQ</h2>
                    </div>
                    <p class="small text-muted mb-0">Home page questions &amp; answers.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/deliverables')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-layers fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Deliverables</h2>
                    </div>
                    <p class="small text-muted mb-0">“What we deliver” cards on the home page.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/pricing')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-currency-dollar fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Pricing</h2>
                    </div>
                    <p class="small text-muted mb-0">Packages, features, demo images &amp; external links.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/finance')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-wallet2 fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Finance</h2>
                    </div>
                    <p class="small text-muted mb-0">Accounts, categories, MMK transactions &amp; reports.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/contact-info')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-geo-alt fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Contact info</h2>
                    </div>
                    <p class="small text-muted mb-0">Address, phone &amp; social links on Contact &amp; footer.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/contacts')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card border-warning border-opacity-25">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap k2-admin-icon-wrap--accent rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-envelope-open fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Messages</h2>
                    </div>
                    <p class="small text-muted mb-0">Contact form submissions.</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
