<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing">Content</p>
        <h2 class="h4 mb-0 text-dark">All posts</h2>
    </div>
    <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/admin/blog/new')) ?>">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New post
    </a>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<?php if ($rows === []) : ?>
    <p class="text-muted">No posts yet. Create one to get started.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Status</th>
                    <th scope="col">Published</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $rid = (int) ($r['id'] ?? 0);
                    $st = (string) ($r['status'] ?? '');
                    ?>
                    <tr>
                        <td class="fw-medium"><?= k2_e((string) ($r['title'] ?? '')) ?></td>
                        <td class="small text-muted"><code><?= k2_e((string) ($r['slug'] ?? '')) ?></code></td>
                        <td>
                            <?php if ($st === 'published') : ?>
                                <span class="badge text-bg-success">Published</span>
                            <?php else : ?>
                                <span class="badge text-bg-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= k2_e((string) ($r['published_at'] ?? '—')) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= k2_e(k2_url('/admin/blog/edit') . '?id=' . $rid) ?>">Edit</a>
                            <form method="post" action="<?= k2_e(k2_url('/admin/blog/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this post permanently?');">
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
