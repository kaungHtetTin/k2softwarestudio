<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $tx */
/** @var list<array<string, mixed>> $accounts */
/** @var bool $canTransfer */
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

$dateVal = $val('occurred_at');
if ($dateVal === '') {
    $dateVal = $tx !== null ? substr((string) ($tx['occurred_at'] ?? ''), 0, 10) : date('Y-m-d');
}

$amtVal = $val('amount');
if ($amtVal === '' && $tx !== null && isset($tx['amount'])) {
    $amtVal = k2_finance_format_amount((string) $tx['amount']);
}

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/finance/transactions')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Transactions</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'Balance transfer' : 'Edit transfer' ?></h2>
    <p class="text-muted small mb-0 mt-2">Move <?= k2_e(k2_finance_currency()) ?> from one active account to another. No category — this is not income or expense.</p>
</div>

<?php if ($errors !== []) : ?>
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3 small">
            <?php foreach ($errors as $err) : ?>
                <li><?= k2_e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!$canTransfer) : ?>
    <div class="alert alert-warning border-0 shadow-sm">
        You need at least <strong>two active accounts</strong>. <a href="<?= k2_e(k2_url('/admin/finance/accounts/new')) ?>">Create another account</a>, then return here.
    </div>
<?php endif; ?>

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/finance/transfer/new') : k2_url('/admin/finance/transfer/edit') . '?id=' . (int) $editId) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <label for="tr-from" class="form-label">From account <span class="text-danger">*</span></label>
                <select class="form-select form-select-lg" id="tr-from" name="transfer_from_id" required <?= !$canTransfer ? 'disabled' : '' ?>>
                    <option value="">Choose account…</option>
                    <?php foreach ($accounts as $a) :
                        $aid = (int) ($a['id'] ?? 0);
                        $sel = (string) $aid === $val('transfer_from_id')
                            || ($val('transfer_from_id') === '' && $tx !== null && (int) ($tx['transfer_from_id'] ?? 0) === $aid);
                        ?>
                        <option value="<?= (int) $aid ?>" <?= $sel ? 'selected' : '' ?>><?= k2_e((string) ($a['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="tr-to" class="form-label">To account <span class="text-danger">*</span></label>
                <select class="form-select form-select-lg" id="tr-to" name="transfer_to_id" required <?= !$canTransfer ? 'disabled' : '' ?>>
                    <option value="">Choose account…</option>
                    <?php foreach ($accounts as $a) :
                        $aid = (int) ($a['id'] ?? 0);
                        $sel = (string) $aid === $val('transfer_to_id')
                            || ($val('transfer_to_id') === '' && $tx !== null && (int) ($tx['transfer_to_id'] ?? 0) === $aid);
                        ?>
                        <option value="<?= (int) $aid ?>" <?= $sel ? 'selected' : '' ?>><?= k2_e((string) ($a['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="tr-amt" class="form-label">Amount (<?= k2_e(k2_finance_currency()) ?>) <span class="text-danger">*</span></label>
                <input type="text" class="form-control font-monospace" id="tr-amt" name="amount" inputmode="decimal" required placeholder="e.g. 500000" value="<?= k2_e($amtVal) ?>" <?= !$canTransfer ? 'disabled' : '' ?>>
            </div>
            <div class="col-md-4">
                <label for="tr-date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="tr-date" name="occurred_at" required value="<?= k2_e($dateVal) ?>" <?= !$canTransfer ? 'disabled' : '' ?>>
            </div>
            <div class="col-md-4">
                <label for="tr-desc" class="form-label">Note</label>
                <input type="text" class="form-control" id="tr-desc" name="description" maxlength="512" placeholder="Optional" value="<?= k2_e($val('description')) ?>" <?= !$canTransfer ? 'disabled' : '' ?>>
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
            <button type="submit" class="btn btn-k2-accent btn-lg" <?= !$canTransfer ? 'disabled' : '' ?>><?= $isNew ? 'Record transfer' : 'Save changes' ?></button>
            <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/finance/transactions')) ?>">Cancel</a>
        </div>
    </div>
</form>

<script>
(function () {
    var fr = document.getElementById('tr-from');
    var to = document.getElementById('tr-to');
    if (!fr || !to) return;
    fr.addEventListener('change', function () {
        if (fr.value && to.value === fr.value) {
            to.value = '';
        }
    });
    to.addEventListener('change', function () {
        if (fr.value && to.value === fr.value) {
            fr.value = '';
        }
    });
})();
</script>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
