<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $faq */
/** @var int|null $editId */
/** @var list<string> $errors */
/** @var array<string, string> $old */

$isNew = ($editId === null || (int) $editId <= 0);

$val = static function (string $k) use ($faq, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($faq !== null && array_key_exists($k, $faq)) {
        return (string) $faq[$k];
    }

    return '';
};

$visChecked = false;
if (array_key_exists('is_visible', $old)) {
    $visChecked = $old['is_visible'] !== '' && $old['is_visible'] !== '0';
} elseif ($faq !== null) {
    $visChecked = (int) ($faq['is_visible'] ?? 0) === 1;
}

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/faq')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>All FAQs</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New FAQ' : 'Edit FAQ' ?></h2>
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

<form method="post" action="<?= k2_e($isNew ? k2_url('/admin/faq/new') : k2_url('/admin/faq/edit') . '?id=' . (int) $editId) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <?php if (!$isNew && $editId !== null) : ?>
        <input type="hidden" name="id" value="<?= (int) $editId ?>">
    <?php endif; ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="fq-q" class="form-label">Question <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fq-q" name="question" required maxlength="512" value="<?= k2_e($val('question')) ?>">
            </div>
            <div class="col-md-6">
                <label for="fq-sort" class="form-label">Sort order</label>
                <input type="number" class="form-control" id="fq-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="fq-vis" name="is_visible" value="1" <?= $visChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="fq-vis">Visible on home page</label>
                </div>
            </div>
            <div class="col-12">
                <label for="fq-a" class="form-label">Answer (HTML) <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace small" id="fq-a" name="answer" rows="12" required><?= k2_e($val('answer')) ?></textarea>
                <div class="form-text">Sanitized on save (safe subset of HTML), same as blog posts.</div>
            </div>
        </div>
        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-k2-accent">Save</button>
            <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/faq')) ?>">Cancel</a>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
