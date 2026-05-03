<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var list<array<string, mixed>> $accounts */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing"><a href="<?= k2_e(k2_url('/admin/finance')) ?>" class="text-muted text-decoration-none">Finance</a></p>
        <h2 class="h4 mb-0 text-dark">Transactions</h2>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= k2_e(k2_url('/admin/finance/transfer/new')) ?>">
            <i class="bi bi-arrow-left-right me-1" aria-hidden="true"></i> Balance transfer
        </a>
        <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/admin/finance/transactions/new')) ?>">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New transaction
        </a>
    </div>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= k2_e(k2_url('/admin/finance/transactions')) ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-0 text-muted">From</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?= k2_e($df) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0 text-muted">To</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?= k2_e($dt) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0 text-muted">Account</label>
                <select class="form-select form-select-sm" name="account_id">
                    <option value="">Any</option>
                    <?php foreach ($accounts as $a) :
                        $id = (int) ($a['id'] ?? 0);
                        ?>
                        <option value="<?= (int) $id ?>" <?= $aid === $id ? 'selected' : '' ?>><?= k2_e((string) ($a['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0 text-muted">Type</label>
                <select class="form-select form-select-sm" name="type">
                    <option value="">All</option>
                    <option value="income" <?= $tp === 'income' ? 'selected' : '' ?>>Income</option>
                    <option value="expense" <?= $tp === 'expense' ? 'selected' : '' ?>>Expense</option>
                    <option value="transfer" <?= $tp === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary btn-sm me-1">Filter</button>
                <a class="btn btn-outline-secondary btn-sm" href="<?= k2_e(k2_url('/admin/finance/transactions')) ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($rows === []) : ?>
    <p class="text-muted">No transactions match these filters.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border bg-white">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Type</th>
                    <th scope="col">Details</th>
                    <th scope="col" class="text-end">Amount</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $rid = (int) ($r['id'] ?? 0);
                    $ty = (string) ($r['type'] ?? '');
                    $badge = 'secondary';
                    if ($ty === 'income') {
                        $badge = 'success';
                    } elseif ($ty === 'expense') {
                        $badge = 'danger';
                    } elseif ($ty === 'transfer') {
                        $badge = 'info';
                    }
                    $amt = k2_finance_format_amount((string) ($r['amount'] ?? '0'));
                    $line = '';
                    if ($ty === 'transfer') {
                        $line = trim((string) ($r['from_account_name'] ?? '')) . ' → ' . trim((string) ($r['to_account_name'] ?? ''));
                    } elseif ($ty === 'income' || $ty === 'expense') {
                        $line = trim((string) ($r['account_name'] ?? ''));
                        $cn = trim((string) ($r['category_name'] ?? ''));
                        if ($cn !== '') {
                            $line .= ' · ' . $cn;
                        }
                    }
                    $desc = trim((string) ($r['description'] ?? ''));
                    ?>
                    <tr>
                        <td class="text-nowrap"><?= k2_e((string) ($r['occurred_at'] ?? '')) ?></td>
                        <td><span class="badge text-bg-<?= k2_e($badge) ?>"><?= k2_e(ucfirst($ty)) ?></span></td>
                        <td>
                            <div><?= k2_e($line) ?></div>
                            <?php if ($desc !== '') : ?>
                                <div class="text-muted"><?= k2_e($desc) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end font-monospace"><?= k2_e($amt) ?></td>
                        <td class="text-end text-nowrap">
                            <?php
                            $editHref = $ty === 'transfer'
                                ? k2_url('/admin/finance/transfer/edit') . '?id=' . $rid
                                : k2_url('/admin/finance/transactions/edit') . '?id=' . $rid;
                            ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= k2_e($editHref) ?>">Edit</a>
                            <form method="post" action="<?= k2_e(k2_url('/admin/finance/transactions/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this transaction?');">
                                <?= k2_csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $rid ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
