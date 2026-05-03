<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing"><a href="<?= k2_e(k2_url('/admin/finance')) ?>" class="text-muted text-decoration-none">Finance</a></p>
        <h2 class="h4 mb-0 text-dark">Categories</h2>
        <p class="small text-muted mb-0 mt-1">One global list for tagging income and expenses.</p>
    </div>
    <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/admin/finance/categories/new')) ?>">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New category
    </a>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<?php if ($rows === []) : ?>
    <p class="text-muted">No categories yet.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Active</th>
                    <th scope="col">Sort</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $rid = (int) ($r['id'] ?? 0);
                    $vis = (int) ($r['is_active'] ?? 0) === 1;
                    ?>
                    <tr>
                        <td class="fw-medium"><?= k2_e((string) ($r['name'] ?? '')) ?></td>
                        <td><?= $vis ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
                        <td class="small text-muted"><?= (int) ($r['sort_order'] ?? 0) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= k2_e(k2_url('/admin/finance/categories/edit') . '?id=' . $rid) ?>">Edit</a>
                            <form method="post" action="<?= k2_e(k2_url('/admin/finance/categories/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this category? It must not be used by any transaction.');">
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
