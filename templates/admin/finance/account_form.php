<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $account */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($account, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($account !== null && array_key_exists($k, $account)) {
        return (string) $account[$k];
    }

    return '';
};

$visChecked = false;
if (array_key_exists('is_active', $old)) {
    $visChecked = $old['is_active'] !== '' && $old['is_active'] !== '0';
} elseif ($account !== null) {
    $visChecked = (int) ($account['is_active'] ?? 0) === 1;
} else {
    $visChecked = true;
}

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/finance/accounts')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Accounts</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New account' : 'Edit account' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/finance/accounts/new') : k2_url('/admin/finance/accounts/edit') . '?id=' . (int) $editId) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="fa-name" class="form-label">Account name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fa-name" name="name" required maxlength="128" placeholder="e.g. KBZ business" value="<?= k2_e($val('name')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <input type="text" class="form-control bg-light" value="<?= k2_e(k2_finance_currency()) ?>" readonly disabled>
            </div>
            <div class="col-md-4">
                <label for="fa-ob" class="form-label">Opening balance</label>
                <input type="text" class="form-control font-monospace" id="fa-ob" name="opening_balance" inputmode="decimal" value="<?= k2_e($val('opening_balance') !== '' ? $val('opening_balance') : '0') ?>">
                <div class="form-text">Set when you first add the account (often 0). Ledger starts from the account creation date.</div>
            </div>
            <div class="col-md-4">
                <label for="fa-sort" class="form-label">Sort order</label>
                <input type="number" class="form-control" id="fa-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="fa-vis" name="is_active" value="1" <?= $visChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="fa-vis">Active</label>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-k2-accent"><?= $isNew ? 'Create account' : 'Save' ?></button>
            <a class="btn btn-outline-secondary ms-2" href="<?= k2_e(k2_url('/admin/finance/accounts')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
