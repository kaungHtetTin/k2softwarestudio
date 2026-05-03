<?php

declare(strict_types=1);

/** @var string $df */
/** @var string $dt */
/** @var array{income: string, expense: string} $totals */
/** @var list<array{name: string, total: string}> $byCat */
/** @var list<array<string, mixed>> $accounts */
/** @var array<int, string> $balances */
/** @var string $net */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing"><a href="<?= k2_e(k2_url('/admin/finance')) ?>" class="text-muted text-decoration-none">Finance</a></p>
        <h2 class="h4 mb-0 text-dark">Reports</h2>
        <p class="small text-muted mb-0 mt-1">Totals use transaction dates in the selected range. Balances are current (all time).</p>
    </div>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= k2_e(k2_url('/admin/finance/reports')) ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0 text-muted">From</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?= k2_e($df) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0 text-muted">To</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?= k2_e($dt) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary btn-sm">Apply range</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="small text-muted mb-1">Income (period)</p>
                <p class="h4 mb-0 font-monospace text-success"><?= k2_e(k2_finance_format_amount($totals['income'])) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="small text-muted mb-1">Expense (period)</p>
                <p class="h4 mb-0 font-monospace text-danger"><?= k2_e(k2_finance_format_amount($totals['expense'])) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="small text-muted mb-1">Net (period)</p>
                <p class="h4 mb-0 font-monospace"><?= k2_e(k2_finance_format_amount($net)) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-uppercase text-muted mb-3">Expense by category</h3>
                <?php if ($byCat === []) : ?>
                    <p class="text-muted small mb-0">No expenses in this period.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Category</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($byCat as $row) : ?>
                                    <tr>
                                        <td><?= k2_e($row['name']) ?></td>
                                        <td class="text-end font-monospace"><?= k2_e(k2_finance_format_amount($row['total'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-uppercase text-muted mb-3">Current balance by account</h3>
                <?php if ($accounts === []) : ?>
                    <p class="text-muted small mb-0">No active accounts.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Account</th><th class="text-end">Balance</th></tr></thead>
                            <tbody>
                                <?php foreach ($accounts as $a) :
                                    $aid = (int) ($a['id'] ?? 0);
                                    $bal = $balances[$aid] ?? '0.00';
                                    ?>
                                    <tr>
                                        <td><?= k2_e((string) ($a['name'] ?? '')) ?></td>
                                        <td class="text-end font-monospace"><?= k2_e(k2_finance_format_amount($bal)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
