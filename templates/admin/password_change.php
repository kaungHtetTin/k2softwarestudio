<?php

declare(strict_types=1);

/** @var list<string> $errors */
/** @var array<string, mixed>|null $flashNotice */

if (!isset($flashNotice)) {
    $flashNotice = null;
}

ob_start();
?>
<div class="mb-4">
    <p class="text-uppercase small text-muted mb-1 letter-spacing">Account</p>
    <h2 class="h4 mb-0 text-dark">Change password</h2>
    <p class="text-muted small mb-0 mt-2">Use a strong password you do not reuse elsewhere.</p>
</div>

<?php if (is_array($flashNotice) && isset($flashNotice['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flashNotice['ok']) ?></div>
<?php endif; ?>

<?php if ($errors !== []) : ?>
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3 small">
            <?php foreach ($errors as $err) : ?>
                <li><?= k2_e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= k2_e(k2_url('/admin/password')) ?>" class="card border-0 shadow-sm" autocomplete="off">
    <?= k2_csrf_field() ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="pw-current" class="form-label">Current password</label>
                <input type="password" class="form-control" id="pw-current" name="current_password" required autocomplete="current-password">
            </div>
            <div class="col-12 col-md-6">
                <label for="pw-new" class="form-label">New password</label>
                <input type="password" class="form-control" id="pw-new" name="new_password" required minlength="<?= (int) K2_ADMIN_PASSWORD_MIN_LEN ?>" autocomplete="new-password" aria-describedby="pw-hint">
                <p id="pw-hint" class="form-text small mb-0">At least <?= (int) K2_ADMIN_PASSWORD_MIN_LEN ?> characters.</p>
            </div>
            <div class="col-12 col-md-6">
                <label for="pw-confirm" class="form-label">Confirm new password</label>
                <input type="password" class="form-control" id="pw-confirm" name="new_password_confirm" required minlength="<?= (int) K2_ADMIN_PASSWORD_MIN_LEN ?>" autocomplete="new-password">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-k2-accent">Update password</button>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
