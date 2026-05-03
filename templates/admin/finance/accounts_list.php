<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var array<int, string> $balances */
/** @var array<string, mixed>|null $flash */

ob_start();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-uppercase small text-muted mb-1 letter-spacing"><a href="<?= k2_e(k2_url('/admin/finance')) ?>" class="text-muted text-decoration-none">Finance</a></p>
        <h2 class="h4 mb-0 text-dark">Accounts</h2>
    </div>
    <a class="btn btn-k2-accent" href="<?= k2_e(k2_url('/admin/finance/accounts/new')) ?>">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New account
    </a>
</div>

<?php if (is_array($flash) && isset($flash['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flash['ok']) ?></div>
<?php endif; ?>
<?php if (is_array($flash) && isset($flash['error'])) : ?>
    <div class="alert alert-danger border-0 shadow-sm py-2"><?= k2_e((string) $flash['error']) ?></div>
<?php endif; ?>

<?php if ($rows === []) : ?>
    <p class="text-muted">No accounts yet.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Opening</th>
                    <th scope="col">Active</th>
                    <th scope="col">Sort</th>
                    <th scope="col" class="text-end">Balance (<?= k2_e(k2_finance_currency()) ?>)</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $rid = (int) ($r['id'] ?? 0);
                    $bal = $balances[$rid] ?? '0.00';
                    $vis = (int) ($r['is_active'] ?? 0) === 1;
                    ?>
                    <tr>
                        <td class="fw-medium"><?= k2_e((string) ($r['name'] ?? '')) ?></td>
                        <td class="small font-monospace text-muted"><?= k2_e(k2_finance_format_amount((string) ($r['opening_balance'] ?? '0'))) ?></td>
                        <td><?= $vis ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Off</span>' ?></td>
                        <td class="small text-muted"><?= (int) ($r['sort_order'] ?? 0) ?></td>
                        <td class="text-end font-monospace"><?= k2_e(k2_finance_format_amount($bal)) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= k2_e(k2_url('/admin/finance/accounts/edit') . '?id=' . $rid) ?>">Edit</a>
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
