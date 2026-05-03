<?php

declare(strict_types=1);

$demoEmail = 'demo@example.com';
$status = k2_login_throttle_status($demoEmail);

$pageTitle = 'Security tests';
ob_start();

$flashCsrf = $_GET['csrf'] ?? '';
$flashThrottle = $_GET['throttle'] ?? '';
$flashDb = $_GET['db'] ?? '';
?>
<div class="container py-4">
    <h1 class="h3 mb-4">Phase 1 — security plumbing</h1>
    <p class="text-muted small">Available only when <code>APP_DEBUG=true</code>. Remove or hide this route in production builds.</p>

    <?php if ($flashCsrf === 'ok') : ?>
        <div class="alert alert-success">CSRF token validated successfully.</div>
    <?php endif; ?>

    <?php if ($flashThrottle === 'hit') : ?>
        <div class="alert alert-warning">Recorded one failed login attempt for <code><?= k2_e($demoEmail) ?></code>.</div>
    <?php endif; ?>
    <?php if ($flashThrottle === 'locked') : ?>
        <div class="alert alert-danger">Throttle active — too many attempts in the window. Wait or reset below.</div>
    <?php endif; ?>
    <?php if ($flashThrottle === 'reset') : ?>
        <div class="alert alert-info">Throttle counters cleared for the demo identity.</div>
    <?php endif; ?>

    <?php if ($flashDb === 'ok') : ?>
        <div class="alert alert-success">Database ping: OK (<code>SELECT 1</code>).</div>
    <?php endif; ?>
    <?php if ($flashDb === 'fail') : ?>
        <div class="alert alert-danger">Database ping failed — check <code>.env</code> and MySQL.</div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5">CSRF</h2>
                    <p class="small text-muted">Forms that change state must include a valid session token.</p>
                    <form method="post" class="mb-3">
                        <?= k2_csrf_field() ?>
                        <input type="hidden" name="action" value="csrf_ok">
                        <button type="submit" class="btn btn-k2-accent btn-sm">POST with valid token</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="action" value="csrf_bad">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">POST without token (expect 403)</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5">Login throttle</h2>
                    <p class="small mb-2">
                        Identity: <code><?= k2_e($demoEmail) ?></code><br>
                        Window: <strong><?= (int) $status['window'] ?></strong>s · Max: <strong><?= (int) $status['max'] ?></strong><br>
                        Current attempts in window: <strong><?= (int) $status['attempts'] ?></strong>
                        <?php if ($status['blocked']) : ?>
                            <span class="text-danger"> — blocked<?php if ($status['retry_after'] !== null) : ?> (~<?= (int) $status['retry_after'] ?>s)<?php endif; ?></span>
                        <?php endif; ?>
                    </p>
                    <form method="post" class="mb-2">
                        <?= k2_csrf_field() ?>
                        <input type="hidden" name="email" value="<?= k2_e($demoEmail) ?>">
                        <input type="hidden" name="action" value="throttle_fail">
                        <button type="submit" class="btn btn-k2-accent btn-sm">Simulate failed login</button>
                    </form>
                    <form method="post">
                        <?= k2_csrf_field() ?>
                        <input type="hidden" name="email" value="<?= k2_e($demoEmail) ?>">
                        <input type="hidden" name="action" value="throttle_reset">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Reset throttle for demo email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h5">Database (PDO)</h2>
            <p class="small text-muted">Runs <code>SELECT 1</code> via <code>k2_db()</code> with CSRF-protected POST.</p>
            <form method="post">
                <?= k2_csrf_field() ?>
                <input type="hidden" name="action" value="db_ping">
                <button type="submit" class="btn btn-k2-accent btn-sm">Ping database</button>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
