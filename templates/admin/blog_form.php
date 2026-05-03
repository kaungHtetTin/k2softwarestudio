<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $post */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

if (!isset($editId) && $post !== null) {
    $editId = (int) ($post['id'] ?? 0);
}
$isNew = ($editId === null || $editId <= 0);

$val = static function (string $k) use ($post, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($post !== null && isset($post[$k])) {
        return (string) $post[$k];
    }

    return '';
};

$pubLocal = '';
$pubRaw = $val('published_at');
if ($pubRaw !== '') {
    $pubLocal = $pubRaw;
} elseif ($post !== null && !empty($post['published_at'])) {
    $pubLocal = date('Y-m-d\TH:i', strtotime((string) $post['published_at']));
}

$statusVal = $val('status');
if ($statusVal === '' && $post !== null) {
    $statusVal = (string) ($post['status'] ?? 'draft');
}
if ($statusVal === '') {
    $statusVal = 'draft';
}

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/blog')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>All posts</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New post' : 'Edit post' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/blog/new') : k2_url('/admin/blog/edit') . '?id=' . (int) $editId) ?>" enctype="multipart/form-data" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && isset($editId)) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="bp-title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="bp-title" name="title" required maxlength="255" value="<?= k2_e($val('title')) ?>">
            </div>
            <div class="col-md-6">
                <label for="bp-slug" class="form-label">Slug</label>
                <input type="text" class="form-control font-monospace small" id="bp-slug" name="slug" maxlength="255" placeholder="auto from title" value="<?= k2_e($val('slug')) ?>">
                <div class="form-text">URL segment — leave blank to derive from title.</div>
            </div>
            <div class="col-md-6">
                <label for="bp-status" class="form-label">Status</label>
                <select class="form-select" id="bp-status" name="status">
                    <option value="draft" <?= $statusVal === 'published' ? '' : 'selected' ?>>Draft</option>
                    <option value="published" <?= $statusVal === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="bp-published" class="form-label">Publish date</label>
                <input type="datetime-local" class="form-control" id="bp-published" name="published_at" value="<?= k2_e($pubLocal) ?>">
                <div class="form-text">Used when status is Published (defaults to now if empty).</div>
            </div>
            <div class="col-12">
                <label for="bp-excerpt" class="form-label">Excerpt</label>
                <textarea class="form-control" id="bp-excerpt" name="excerpt" rows="2" maxlength="2000" placeholder="Short summary for listings & SEO"><?= k2_e($val('excerpt')) ?></textarea>
            </div>
            <div class="col-12">
                <label for="bp-body" class="form-label">Body (HTML) <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace small" id="bp-body" name="body" rows="16" required><?= k2_e($val('body')) ?></textarea>
                <div class="form-text">Sanitized on save (safe subset of HTML).</div>
            </div>
            <div class="col-12">
                <label for="bp-img" class="form-label">Featured image</label>
                <input type="file" class="form-control" id="bp-img" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if ($post !== null && !empty($post['featured_image'])) : ?>
                    <p class="small mt-2 mb-0">
                        Current:
                        <a href="<?= k2_e(k2_asset((string) $post['featured_image'])) ?>" target="_blank" rel="noopener">view</a>
                        — upload a new file to replace.
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-k2-accent"><?= $isNew ? 'Create post' : 'Save changes' ?></button>
            <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/blog')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
