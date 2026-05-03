<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $category */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($category, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($category !== null && array_key_exists($k, $category)) {
        return (string) $category[$k];
    }

    return '';
};

$visChecked = false;
if (array_key_exists('is_active', $old)) {
    $visChecked = $old['is_active'] !== '' && $old['is_active'] !== '0';
} elseif ($category !== null) {
    $visChecked = (int) ($category['is_active'] ?? 0) === 1;
} else {
    $visChecked = true;
}

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/finance/categories')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Categories</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New category' : 'Edit category' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/finance/categories/new') : k2_url('/admin/finance/categories/edit') . '?id=' . (int) $editId) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="fc-name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fc-name" name="name" required maxlength="128" value="<?= k2_e($val('name')) ?>">
            </div>
            <div class="col-md-4">
                <label for="fc-sort" class="form-label">Sort order</label>
                <input type="number" class="form-control" id="fc-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="fc-vis" name="is_active" value="1" <?= $visChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="fc-vis">Active (shown in dropdowns for new transactions)</label>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-k2-accent"><?= $isNew ? 'Create' : 'Save' ?></button>
            <a class="btn btn-outline-secondary ms-2" href="<?= k2_e(k2_url('/admin/finance/categories')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
