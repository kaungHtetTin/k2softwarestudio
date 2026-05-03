<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $app */
/** @var list<array<string, mixed>> $screenshots */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($app, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($app !== null && array_key_exists($k, $app)) {
        return (string) $app[$k];
    }

    return '';
};

$visChecked = false;
if (array_key_exists('is_visible', $old)) {
    $visChecked = $old['is_visible'] !== '' && $old['is_visible'] !== '0';
} elseif ($app !== null) {
    $visChecked = (int) ($app['is_visible'] ?? 0) === 1;
}

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/apps')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>All apps</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New app' : 'Edit app' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/apps/new') : k2_url('/admin/apps/edit') . '?id=' . (int) $editId) ?>" enctype="multipart/form-data" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="ap-title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="ap-title" name="title" required maxlength="255" value="<?= k2_e($val('title')) ?>">
            </div>
            <div class="col-md-6">
                <label for="ap-slug" class="form-label">Slug</label>
                <input type="text" class="form-control font-monospace small" id="ap-slug" name="slug" maxlength="255" placeholder="auto from title" value="<?= k2_e($val('slug')) ?>">
                <div class="form-text">URL segment under /apps/ — leave blank to derive from title.</div>
            </div>
            <div class="col-md-3">
                <label for="ap-sort" class="form-label">Sort order</label>
                <input type="number" class="form-control" id="ap-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="ap-vis" name="is_visible" value="1" <?= $visChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ap-vis">Visible on site</label>
                </div>
            </div>
            <div class="col-12">
                <label for="ap-short" class="form-label">Short description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="ap-short" name="short_description" rows="2" maxlength="512" required placeholder="One-line summary for cards"><?= k2_e($val('short_description')) ?></textarea>
            </div>
            <div class="col-12">
                <label for="ap-long" class="form-label">Long description (HTML)</label>
                <textarea class="form-control font-monospace small" id="ap-long" name="long_description" rows="10" placeholder="Optional rich text; sanitized on save"><?= k2_e($val('long_description')) ?></textarea>
                <div class="form-text">Allowed HTML is sanitized like blog posts.</div>
            </div>
            <div class="col-12">
                <label for="ap-url" class="form-label">External URL</label>
                <input type="url" class="form-control" id="ap-url" name="external_url" placeholder="https://…" value="<?= k2_e($val('external_url')) ?>">
                <div class="form-text">Optional link (store listing, product page). Must be http(s).</div>
            </div>
            <div class="col-md-6">
                <label for="ap-icon" class="form-label">Icon</label>
                <input type="file" class="form-control" id="ap-icon" name="icon" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if ($app !== null && !empty($app['icon_path'])) : ?>
                    <p class="small mt-2 mb-0 d-flex align-items-center gap-2">
                        <img src="<?= k2_e(k2_asset((string) $app['icon_path'])) ?>" alt="" width="48" height="48" class="rounded border object-fit-cover" loading="lazy">
                        <span class="text-muted">Current icon — upload a new file to replace.</span>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="ap-shots" class="form-label">Add screenshots</label>
                <input type="file" class="form-control" id="ap-shots" name="screenshots[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                <div class="form-text">JPEG, PNG, WebP, or GIF. Multiple files allowed.</div>
            </div>
        </div>

        <?php if (!$isNew && $screenshots !== []) : ?>
            <hr class="my-4">
            <p class="fw-medium mb-2">Screenshots</p>
            <div class="row g-3">
                <?php foreach ($screenshots as $s) :
                    $sid = (int) ($s['id'] ?? 0);
                    $pid = (int) ($editId ?? 0);
                    ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="border rounded overflow-hidden position-relative">
                            <div class="ratio ratio-16x9 bg-light">
                                <img src="<?= k2_e(k2_asset((string) ($s['image_path'] ?? ''))) ?>" alt="" class="object-fit-cover w-100 h-100" loading="lazy">
                            </div>
                            <form method="post" action="<?= k2_e(k2_url('/admin/apps/screenshot-delete')) ?>" class="p-2 border-top bg-white" onsubmit="return confirm('Remove this screenshot?');">
                                <?= k2_csrf_field() ?>
                                <input type="hidden" name="screenshot_id" value="<?= (int) $sid ?>">
                                <input type="hidden" name="app_id" value="<?= (int) $pid ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-k2-accent">Save</button>
            <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/apps')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
