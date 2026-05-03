<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $flash */

ob_start();
$reportsUrl = k2_url('/admin/finance/reports') . '?date_from=' . urlencode(date('Y-m-01')) . '&date_to=' . urlencode(date('Y-m-t'));
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing">Finance</p>
        <h2 class="h4 mb-0 text-dark">Financial management</h2>
        <p class="text-muted small mb-0 mt-2">Accounts, categories, transactions, and reports (<?= k2_e(k2_finance_currency()) ?>).</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= k2_e(k2_url('/admin')) ?>">Financial overview</a>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/finance/accounts')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-bank fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Accounts</h2>
                    </div>
                    <p class="small text-muted mb-0">Wallet-style accounts, opening balances, and remaining balances.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/finance/categories')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-tags fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Categories</h2>
                    </div>
                    <p class="small text-muted mb-0">Income and expense categories for transactions.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/finance/transactions')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-list-columns-reverse fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Transactions</h2>
                    </div>
                    <p class="small text-muted mb-0">Browse, add, and edit income and expense entries.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e(k2_url('/admin/finance/transfer/new')) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-arrow-left-right fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Balance transfer</h2>
                    </div>
                    <p class="small text-muted mb-0">Move funds between two accounts.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="<?= k2_e($reportsUrl) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 k2-admin-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="k2-admin-icon-wrap rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-graph-up fs-4" aria-hidden="true"></i></span>
                        <h2 class="h5 mb-0 text-dark">Reports</h2>
                    </div>
                    <p class="small text-muted mb-0">Income, expense, and net for a date range (defaults to this month).</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
