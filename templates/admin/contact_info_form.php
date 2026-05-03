<?php

declare(strict_types=1);

/** @var array<string, string> $data */
/** @var list<string> $errors */
/** @var array<string, mixed>|null $flashAdmin */

if (!isset($flashAdmin)) {
    $flashAdmin = null;
}

ob_start();
?>
<div class="mb-4">
    <p class="text-uppercase small text-muted mb-1 letter-spacing">Site</p>
    <h2 class="h4 mb-0 text-dark">Contact page information</h2>
    <p class="text-muted small mb-0 mt-2">Shown on the public <a href="<?= k2_e(k2_url('/contact')) ?>">Contact</a> page and in the site footer (address, phone, social links).</p>
</div>

<?php if (is_array($flashAdmin) && isset($flashAdmin['ok'])) : ?>
    <div class="alert alert-success border-0 shadow-sm py-2"><?= k2_e((string) $flashAdmin['ok']) ?></div>
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

<form method="post" action="<?= k2_e(k2_url('/admin/contact-info')) ?>" class="card border-0 shadow-sm">
    <?= k2_csrf_field() ?>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12">
                <label for="ci-address" class="form-label">Address</label>
                <textarea class="form-control" id="ci-address" name="contact_address" rows="3" maxlength="2000" placeholder="Street, city, region"><?= k2_e($data['contact_address'] ?? '') ?></textarea>
            </div>
            <div class="col-12 col-md-6">
                <label for="ci-phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="ci-phone" name="contact_phone" maxlength="120" placeholder="+1 …" value="<?= k2_e($data['contact_phone'] ?? '') ?>">
            </div>
            <div class="col-12">
                <hr class="my-2 opacity-25">
                <p class="small text-muted mb-3">Social links — leave blank to hide. Use full <code>https://</code> URLs.</p>
            </div>
            <div class="col-12 col-md-6">
                <label for="ci-fb" class="form-label">Facebook</label>
                <input type="url" class="form-control" id="ci-fb" name="contact_facebook_url" placeholder="https://facebook.com/…" value="<?= k2_e($data['contact_facebook_url'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label for="ci-tg" class="form-label">Telegram</label>
                <input type="url" class="form-control" id="ci-tg" name="contact_telegram_url" placeholder="https://t.me/…" value="<?= k2_e($data['contact_telegram_url'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label for="ci-tt" class="form-label">TikTok</label>
                <input type="url" class="form-control" id="ci-tt" name="contact_tiktok_url" placeholder="https://tiktok.com/…" value="<?= k2_e($data['contact_tiktok_url'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label for="ci-yt" class="form-label">YouTube</label>
                <input type="url" class="form-control" id="ci-yt" name="contact_youtube_url" placeholder="https://youtube.com/…" value="<?= k2_e($data['contact_youtube_url'] ?? '') ?>">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-k2-accent">Save</button>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
