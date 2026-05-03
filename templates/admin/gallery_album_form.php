<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $album */
/** @var list<array<string, mixed>> $photos */
/** @var int|null $albumId */
/** @var list<string> $errors */
/** @var array<string, string> $old */
/** @var array<string, mixed>|null $adminFlash */

if (!isset($adminFlash)) {
    $adminFlash = null;
}

$isNew = ($albumId === null || (int) $albumId <= 0);

$val = static function (string $k) use ($album, $old): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($album !== null && array_key_exists($k, $album)) {
        return (string) $album[$k];
    }

    return '';
};

ob_start();
?>
<div class="mb-4">
    <a href="<?= k2_e(k2_url('/admin/gallery')) ?>" class="small link-secondary text-decoration-none"><i class="bi bi-arrow-left me-1"></i>All albums</a>
    <h2 class="h4 mt-2 mb-0 text-dark"><?= $isNew ? 'New album' : 'Edit album' ?></h2>
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

<?php if (is_array($adminFlash) && isset($adminFlash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $adminFlash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($adminFlash) && isset($adminFlash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $adminFlash['error']) ?></div>
<?php endif; ?>

<?php if ($isNew) : ?>
    <form method="post" action="<?= k2_e(k2_url('/admin/gallery/new')) ?>" class="card border-0 shadow-sm">
        <?= k2_csrf_field() ?>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12">
                    <label for="ga-title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ga-title" name="title" required maxlength="255" value="<?= k2_e($val('title')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="ga-slug" class="form-label">Slug</label>
                    <input type="text" class="form-control font-monospace small" id="ga-slug" name="slug" maxlength="255" placeholder="auto from title" value="<?= k2_e($val('slug')) ?>">
                </div>
                <div class="col-md-6">
                    <label for="ga-sort" class="form-label">Sort order</label>
                    <input type="number" class="form-control" id="ga-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
                </div>
            </div>
            <div class="mt-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-k2-accent">Create album</button>
                <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/gallery')) ?>">Cancel</a>
            </div>
        </div>
    </form>
<?php else : ?>
    <?php $aid = (int) $albumId; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form id="k2-album-edit" method="post" action="<?= k2_e(k2_url('/admin/gallery/edit') . '?id=' . $aid) ?>" enctype="multipart/form-data">
                <?= k2_csrf_field() ?>
                <input type="hidden" name="album_id" value="<?= (int) $aid ?>">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="ga-title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ga-title" name="title" required maxlength="255" value="<?= k2_e($val('title')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="ga-slug" class="form-label">Slug</label>
                        <input type="text" class="form-control font-monospace small" id="ga-slug" name="slug" maxlength="255" placeholder="auto from title" value="<?= k2_e($val('slug')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="ga-sort" class="form-label">Sort order</label>
                        <input type="number" class="form-control" id="ga-sort" name="sort_order" value="<?= k2_e($val('sort_order') !== '' ? $val('sort_order') : '0') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="ga-photos" class="form-label">Add photos</label>
                    <input type="file" class="form-control" id="ga-photos" name="photos[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                    <div class="form-text">JPEG, PNG, WebP, or GIF — multiple files allowed.</div>
                </div>
            </form>

            <?php if ($photos !== []) : ?>
                <hr class="my-4">
                <p class="fw-medium mb-3">Photos in this album</p>
                <div class="row g-3">
                    <?php foreach ($photos as $p) :
                        $pid = (int) ($p['id'] ?? 0);
                        $pcap = (string) ($p['caption'] ?? '');
                        $psort = (int) ($p['sort_order'] ?? 0);
                        $pvis = (int) ($p['is_visible'] ?? 0) === 1;
                        ?>
                        <div class="col-12">
                            <div class="border rounded p-3 bg-white row g-3 align-items-start">
                                <input form="k2-album-edit" type="hidden" name="photo_id[]" value="<?= (int) $pid ?>">
                                <div class="col-md-3 col-lg-2">
                                    <div class="ratio ratio-1x1 bg-light rounded overflow-hidden">
                                        <img src="<?= k2_e(k2_asset((string) ($p['image_path'] ?? ''))) ?>" alt="" class="object-fit-cover" loading="lazy">
                                    </div>
                                </div>
                                <div class="col-md-9 col-lg-10">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12">
                                            <label class="form-label small mb-0" for="cap-<?= (int) $pid ?>">Caption</label>
                                            <input form="k2-album-edit" type="text" class="form-control form-control-sm" id="cap-<?= (int) $pid ?>" name="photo_caption[<?= (int) $pid ?>]" maxlength="512" value="<?= k2_e($pcap) ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small mb-0" for="sort-<?= (int) $pid ?>">Sort</label>
                                            <input form="k2-album-edit" type="number" class="form-control form-control-sm" id="sort-<?= (int) $pid ?>" name="photo_sort[<?= (int) $pid ?>]" value="<?= (int) $psort ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check mb-0">
                                                <input form="k2-album-edit" class="form-check-input" type="checkbox" id="vis-<?= (int) $pid ?>" name="photo_visible[<?= (int) $pid ?>]" value="1" <?= $pvis ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="vis-<?= (int) $pid ?>">Visible on site</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <form method="post" action="<?= k2_e(k2_url('/admin/gallery/photo-delete')) ?>" class="d-inline" onsubmit="return confirm('Remove this photo from the album?');">
                                                <?= k2_csrf_field() ?>
                                                <input type="hidden" name="photo_id" value="<?= (int) $pid ?>">
                                                <input type="hidden" name="album_id" value="<?= (int) $aid ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="mt-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-k2-accent" form="k2-album-edit">Save album</button>
                <a class="btn btn-outline-secondary" href="<?= k2_e(k2_url('/admin/gallery')) ?>">Back to list</a>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
