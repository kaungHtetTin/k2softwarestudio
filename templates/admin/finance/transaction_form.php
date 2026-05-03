<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $tx */
/** @var list<array<string, mixed>> $accounts */
/** @var list<array<string, mixed>> $categories */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($tx, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($tx !== null && array_key_exists($k, $tx)) {
        return (string) $tx[$k];
    }

    return '';
};

$typeVal = $val('type');
if ($typeVal === '') {
    $typeVal = $tx !== null ? (string) ($tx['type'] ?? 'expense') : 'expense';
}

$dateVal = $val('occurred_at');
if ($dateVal === '') {
    $dateVal = $tx !== null ? substr((string) ($tx['occurred_at'] ?? ''), 0, 10) : date('Y-m-d');
}

$noAccounts = $accounts === [];

$showIncomeExpense = ($typeVal === 'income' || $typeVal === 'expense');

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/finance/transactions')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Transactions</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New transaction' : 'Edit transaction' ?></h2>
    <p class="small text-muted mb-0 mt-2">Income and expense only. To move money between accounts, use <a href="<?= k2_e(k2_url('/admin/finance/transfer/new')) ?>">Balance transfer</a>.</p>
</div>

<?php if ($noAccounts) : ?>
    <div class="alert alert-warning border-0 shadow-sm">Create at least one <a href="<?= k2_e(k2_url('/admin/finance/accounts/new')) ?>">active account</a> before recording transactions.</div>
<?php endif; ?>

<?php if ($errors !== []) : ?>
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3 small">
            <?php foreach ($errors as $err) : ?>
                <li><?= k2_e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/finance/transactions/new') : k2_url('/admin/finance/transactions/edit') . '?id=' . (int) $editId) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="ft-type" class="form-label">Type <span class="text-danger">*</span></label>
                <select class="form-select" id="ft-type" name="type" <?= $noAccounts ? 'disabled' : '' ?>>
                    <option value="income" <?= $typeVal === 'income' ? 'selected' : '' ?>>Income</option>
                    <option value="expense" <?= $typeVal === 'expense' ? 'selected' : '' ?>>Expense</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="ft-date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="ft-date" name="occurred_at" required value="<?= k2_e($dateVal) ?>" <?= $noAccounts ? 'disabled' : '' ?>>
            </div>
            <div class="col-md-4">
                <label for="ft-amt" class="form-label">Amount (<?= k2_e(k2_finance_currency()) ?>) <span class="text-danger">*</span></label>
                <input type="text" class="form-control font-monospace" id="ft-amt" name="amount" inputmode="decimal" required placeholder="e.g. 150000" value="<?= k2_e($val('amount')) ?>" <?= $noAccounts ? 'disabled' : '' ?>>
            </div>
            <div class="col-12">
                <label for="ft-desc" class="form-label">Description</label>
                <input type="text" class="form-control" id="ft-desc" name="description" maxlength="512" placeholder="Optional note" value="<?= k2_e($val('description')) ?>" <?= $noAccounts ? 'disabled' : '' ?>>
            </div>

            <div id="blk-income-expense" class="col-12<?= $showIncomeExpense ? '' : ' d-none' ?>">
                <div class="border rounded-3 p-3 bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ft-account" class="form-label"><span id="lbl-account-text"><?= $typeVal === 'income' ? 'Receive into account' : 'Pay from account' ?></span> <span class="text-danger">*</span></label>
                            <select class="form-select" id="ft-account" name="account_id" <?= (!$showIncomeExpense || $noAccounts) ? 'disabled' : '' ?>>
                                <option value="">— Select account —</option>
                                <?php foreach ($accounts as $a) :
                                    $aid = (int) ($a['id'] ?? 0);
                                    $sel = (string) $aid === $val('account_id')
                                        || ($val('account_id') === '' && $tx !== null && (int) ($tx['account_id'] ?? 0) === $aid && (($tx['type'] ?? '') === 'income' || ($tx['type'] ?? '') === 'expense'));
                                    ?>
                                    <option value="<?= (int) $aid ?>" <?= $sel ? 'selected' : '' ?>><?= k2_e((string) ($a['name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ft-category" class="form-label">
                                Category
                                <span id="cat-star" class="text-danger" <?= $typeVal === 'expense' ? '' : 'style="display:none"' ?> aria-hidden="<?= $typeVal === 'expense' ? 'false' : 'true' ?>">*</span>
                            </label>
                            <select class="form-select" id="ft-category" name="category_id" <?= (!$showIncomeExpense || $noAccounts) ? 'disabled' : '' ?> <?= $typeVal === 'expense' ? 'required' : '' ?>>
                                <option value=""><?= $typeVal === 'income' ? '— Optional —' : '— Select category —' ?></option>
                                <?php foreach ($categories as $c) :
                                    $cid = (int) ($c['id'] ?? 0);
                                    $sel = (string) $cid === $val('category_id')
                                        || ($val('category_id') === '' && $tx !== null && (int) ($tx['category_id'] ?? 0) === $cid);
                                    ?>
                                    <option value="<?= (int) $cid ?>" <?= $sel ? 'selected' : '' ?>><?= k2_e((string) ($c['name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text" id="hint-category"><?= $typeVal === 'income' ? 'Optional for income.' : 'Required for expenses.' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-k2-accent" <?= $noAccounts ? 'disabled' : '' ?>><?= $isNew ? 'Save transaction' : 'Update' ?></button>
            <a class="btn btn-outline-secondary ms-2" href="<?= k2_e(k2_url('/admin/finance/transactions')) ?>">Cancel</a>
        </div>
    </div>
</form>

<script>
(function () {
    var sel = document.getElementById('ft-type');
    if (!sel) return;
    var noAcc = <?= $noAccounts ? 'true' : 'false' ?>;
    var blkIE = document.getElementById('blk-income-expense');
    var acc = document.getElementById('ft-account');
    var cat = document.getElementById('ft-category');
    var lblAcc = document.getElementById('lbl-account-text');
    var catStar = document.getElementById('cat-star');
    var hintCat = document.getElementById('hint-category');

    function sync() {
        var t = sel.value;
        var isIE = (t === 'income' || t === 'expense');

        if (blkIE) blkIE.classList.toggle('d-none', !isIE);

        if (acc) acc.disabled = !isIE || noAcc;
        if (cat) {
            cat.disabled = !isIE || noAcc;
            cat.required = (t === 'expense');
        }

        if (lblAcc) {
            lblAcc.textContent = t === 'income' ? 'Receive into account' : 'Pay from account';
        }
        if (catStar) {
            catStar.style.display = (t === 'expense') ? '' : 'none';
            catStar.setAttribute('aria-hidden', t === 'expense' ? 'false' : 'true');
        }
        if (hintCat) {
            hintCat.textContent = t === 'income' ? 'Optional for income.' : 'Required for expenses.';
        }
        if (cat && cat.options.length) {
            var first = cat.options[0];
            if (first && first.value === '') {
                first.textContent = (t === 'income') ? '— Optional —' : '— Select category —';
            }
        }
    }

    sel.addEventListener('change', sync);
    sync();
})();
</script>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
