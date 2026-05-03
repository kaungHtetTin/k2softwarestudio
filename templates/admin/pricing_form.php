<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $plan */
/** @var list<string> $featureLines */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($plan, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($plan !== null && array_key_exists($k, $plan)) {
        return (string) $plan[$k];
    }

    return '';
};

$visChecked = false;
if (array_key_exists('is_visible', $old)) {
    $visChecked = $old['is_visible'] !== '' && $old['is_visible'] !== '0';
} elseif ($plan !== null) {
    $visChecked = (int) ($plan['is_visible'] ?? 0) === 1;
}

$featuresText = array_key_exists('features_text', $old)
    ? (string) $old['features_text']
    : implode("\n", $featureLines);

$demoPath = ($plan !== null && !empty($plan['demo_image_path'])) ? (string) $plan['demo_image_path'] : '';

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/pricing')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>All plans</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New plan' : 'Edit plan' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/pricing/new') : k2_url('/admin/pricing/edit') . '?id=' . (int) $editId) ?>" enctype="multipart/form-data" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="pp-type" class="form-label">Project type <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pp-type" name="project_type" required maxlength="128" placeholder="e.g. Web application" value="<?= k2_e($val('project_type')) ?>">
                <div class="form-text">Shown as a label above the title on the public page.</div>
            </div>
            <div class="col-md-6">
                <label for="pp-title" class="form-label">Package title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pp-title" name="title" required maxlength="255" value="<?= k2_e($val('title')) ?>">
            </div>
            <div class="col-12">
                <label for="pp-sum" class="form-label">Summary <span class="text-danger">*</span></label>
                <textarea class="form-control" id="pp-sum" name="summary" rows="2" maxlength="512" required><?= k2_e($val('summary')) ?></textarea>
            </div>
            <div class="col-md-4">
                <label for="pp-price" class="form-label">Price display <span class="text-danger">*</span></label>
                <input type="text" class="form-control font-monospace" id="pp-price" name="price_display" required maxlength="64" placeholder="$2,500" value="<?= k2_e($val('price_display')) ?>">
            </div>
            <div class="col-md-4">
                <label for="pp-note" class="form-label">Price note</label>
                <input type="text" class="form-control" id="pp-note" name="price_note" maxlength="128" placeholder="starting at, per month…" value="<?= k2_e($val('price_note')) ?>">
            </div>
            <div class="col-md-4">
                <label for="pp-sort" class="form-label">Sort order</label>
                <input type="number" class="form-control" id="pp-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
            </div>
            <div class="col-12">
                <label for="pp-feat" class="form-label">Features <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace" id="pp-feat" name="features_text" rows="8" required placeholder="One feature per line"><?= k2_e($featuresText) ?></textarea>
                <div class="form-text">One bullet per line (max 40 lines).</div>
            </div>
            <div class="col-md-6">
                <label for="pp-demo" class="form-label">Demo image</label>
                <input type="file" class="form-control" id="pp-demo" name="demo_image" accept="image/jpeg,image/png,image/webp,image/gif">
                <div class="form-text">Optional. JPEG, PNG, WebP, or GIF — same size limits as other uploads.</div>
                <?php if ($demoPath !== '') : ?>
                    <div class="mt-2 d-flex align-items-center gap-3 flex-wrap">
                        <img src="<?= k2_e(k2_asset($demoPath)) ?>" alt="" class="rounded border" width="160" height="90" style="object-fit:cover;" loading="lazy" decoding="async">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_demo" value="1" id="pp-rm">
                            <label class="form-check-label small" for="pp-rm">Remove current image</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="pp-url" class="form-label">External link</label>
                <input type="url" class="form-control" id="pp-url" name="external_url" maxlength="512" placeholder="https://…" value="<?= k2_e($val('external_url')) ?>">
                <div class="form-text">Optional link (case study, calendar, external proposal, etc.).</div>
                <label for="pp-ll" class="form-label mt-3">Link button label</label>
                <input type="text" class="form-control" id="pp-ll" name="link_label" maxlength="80" placeholder="Learn more" value="<?= k2_e($val('link_label')) ?>">
                <div class="form-text">Defaults to “Learn more” if empty.</div>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pp-vis" name="is_visible" value="1" <?= $visChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="pp-vis">Visible on pricing page</label>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-k2-accent"><?= $isNew ? 'Create plan' : 'Save changes' ?></button>
            <a class="btn btn-outline-secondary ms-2" href="<?= k2_e(k2_url('/admin/pricing')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
