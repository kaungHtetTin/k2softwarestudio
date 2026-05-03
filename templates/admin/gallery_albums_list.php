<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing">Media</p>
        <h2 class="h4 mb-0 text-dark">Photo albums</h2>
    </div>
    <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/admin/gallery/new')) ?>">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New album
    </a>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<?php if ($rows === []) : ?>
    <p class="text-muted">No albums yet. Create one and upload photos.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Photos</th>
                    <th scope="col">Sort</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $rid = (int) ($r['id'] ?? 0);
                    $total = (int) ($r['photo_count'] ?? 0);
                    $vis = (int) ($r['visible_count'] ?? 0);
                    ?>
                    <tr>
                        <td class="fw-medium"><?= k2_e((string) ($r['title'] ?? '')) ?></td>
                        <td class="small text-muted"><code><?= k2_e((string) ($r['slug'] ?? '')) ?></code></td>
                        <td class="small text-muted"><?= (int) $vis ?> visible / <?= (int) $total ?> total</td>
                        <td class="small text-muted"><?= (int) ($r['sort_order'] ?? 0) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= k2_e(k2_url('/admin/gallery/edit') . '?id=' . $rid) ?>">Edit</a>
                            <form method="post" action="<?= k2_e(k2_url('/admin/gallery/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this album and all of its photos?');">
                                <?= k2_csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $rid ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
