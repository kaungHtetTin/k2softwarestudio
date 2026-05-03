<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $accounts */
/** @var array<int, string> $balances */
/** @var array{income: string, expense: string} $monthTotals */
/** @var string $monthNet */
/** @var string $monthLabel */
/** @var string $totalRemaining Total of active account balances (decimal string) */
/** @var array<string, mixed>|null $flash */

ob_start();
$reportsUrl = k2_url('/admin/finance/reports') . '?date_from=' . urlencode(date('Y-m-01')) . '&date_to=' . urlencode(date('Y-m-t'));
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing">Overview</p>
        <h2 class="h4 mb-0 text-dark">Financial dashboard</h2>
        <p class="text-muted small mb-0 mt-2">Amounts in <?= k2_e(k2_finance_currency()) ?>.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="<?= k2_e(k2_url('/admin/finance')) ?>">Financial management</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= k2_e(k2_url('/admin/site')) ?>">Site &amp; content</a>
    </div>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h3 class="h5 text-dark mb-1">Report — <?= k2_e($monthLabel) ?></h3>
                <p class="small text-muted mb-0">Income and expenses dated this calendar month.</p>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="<?= k2_e($reportsUrl) ?>">Full reports</a>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="rounded-3 border bg-light p-3 h-100">
                    <p class="small text-muted mb-1">Income</p>
                    <p class="h5 mb-0 font-monospace text-success"><?= k2_e(k2_finance_format_amount($monthTotals['income'])) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 border bg-light p-3 h-100">
                    <p class="small text-muted mb-1">Expense</p>
                    <p class="h5 mb-0 font-monospace text-danger"><?= k2_e(k2_finance_format_amount($monthTotals['expense'])) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rounded-3 border bg-light p-3 h-100">
                    <p class="small text-muted mb-1">Net</p>
                    <p class="h5 mb-0 font-monospace"><?= k2_e(k2_finance_format_amount($monthNet)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h3 class="h5 text-dark mb-3">Remaining balance</h3>
        <p class="small text-muted mb-4">Per active account (opening balance plus all recorded movements).</p>
        <?php if ($accounts === []) : ?>
            <p class="text-muted mb-0">No active accounts. <a href="<?= k2_e(k2_url('/admin/finance/accounts/new')) ?>">Create an account</a>.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Account</th>
                            <th scope="col" class="text-end">Remaining (<?= k2_e(k2_finance_currency()) ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $a) :
                            $aid = (int) ($a['id'] ?? 0);
                            $bal = $balances[$aid] ?? '0.00';
                            ?>
                            <tr>
                                <td class="fw-medium"><?= k2_e((string) ($a['name'] ?? '')) ?></td>
                                <td class="text-end font-monospace"><?= k2_e(k2_finance_format_amount($bal)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th scope="row" class="small">Total (active accounts)</th>
                            <td class="text-end font-monospace fw-semibold"><?= k2_e(k2_finance_format_amount($totalRemaining)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
