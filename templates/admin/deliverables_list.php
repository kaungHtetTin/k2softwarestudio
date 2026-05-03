<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing">Home page</p>
        <h2 class="h4 mb-0 text-dark">What we deliver</h2>
    </div>
    <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/admin/deliverables/new')) ?>">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New card
    </a>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<?php if ($rows === []) : ?>
    <p class="text-muted">No items yet. Add cards for the “What we deliver” section on the home page.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Icon</th>
                    <th scope="col">Visible</th>
                    <th scope="col">Sort</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $rid = (int) ($r['id'] ?? 0);
                    $vis = (int) ($r['is_visible'] ?? 0) === 1;
                    $ic = k2_deliverable_icon_sanitize((string) ($r['icon_name'] ?? ''));
                    ?>
                    <tr>
                        <td class="fw-medium"><?= k2_e((string) ($r['title'] ?? '')) ?></td>
                        <td class="small text-muted font-monospace"><code>bi-<?= k2_e($ic) ?></code></td>
                        <td>
                            <?php if ($vis) : ?>
                                <span class="badge text-bg-success">Yes</span>
                            <?php else : ?>
                                <span class="badge text-bg-secondary">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= (int) ($r['sort_order'] ?? 0) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= k2_e(k2_url('/admin/deliverables/edit') . '?id=' . $rid) ?>">Edit</a>
                            <form method="post" action="<?= k2_e(k2_url('/admin/deliverables/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this card?');">
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
