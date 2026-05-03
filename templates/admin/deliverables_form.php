<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $deliverable */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($deliverable, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($deliverable !== null && array_key_exists($k, $deliverable)) {
        return (string) $deliverable[$k];
    }

    return '';
};

$visChecked = false;
if (array_key_exists('is_visible', $old)) {
    $visChecked = $old['is_visible'] !== '' && $old['is_visible'] !== '0';
} elseif ($deliverable !== null) {
    $visChecked = (int) ($deliverable['is_visible'] ?? 0) === 1;
}

$iconPreview = k2_deliverable_icon_sanitize($val('icon_name'));

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/deliverables')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>All deliverables</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New card' : 'Edit card' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/deliverables/new') : k2_url('/admin/deliverables/edit') . '?id=' . (int) $editId) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="dv-title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="dv-title" name="title" required maxlength="255" value="<?= k2_e($val('title')) ?>">
            </div>
            <div class="col-12">
                <label for="dv-desc" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="dv-desc" name="description" rows="3" maxlength="512" required><?= k2_e($val('description')) ?></textarea>
            </div>
            <div class="col-md-6">
                <label for="dv-icon" class="form-label">Icon (Bootstrap Icons)</label>
                <input type="text" class="form-control font-monospace" id="dv-icon" name="icon_name" maxlength="48" placeholder="e.g. window-stack" value="<?= k2_e($val('icon_name')) ?>">
                <div class="form-text">
                    Slug only — no <code>bi-</code> prefix. Browse <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener noreferrer">Bootstrap Icons</a>.
                    Preview: <span class="k2-icon-preview ms-1 align-middle text-primary"><i class="bi bi-<?= k2_e($iconPreview) ?> fs-4" aria-hidden="true"></i></span>
                </div>
            </div>
            <div class="col-md-3">
                <label for="dv-sort" class="form-label">Sort order</label>
                <input type="number" class="form-control" id="dv-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="dv-vis" name="is_visible" value="1" <?= $visChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dv-vis">Visible on home</label>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-k2-accent">Save</button>
            <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/deliverables')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
