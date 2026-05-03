<?php

declare(strict_types=1);

$flash = k2_flash_pull('admin_login');
$base = k2_base_path();
$assetBase = k2_e($base === '' ? '' : $base);
$favicon = k2_favicon();
$pageTitle = 'Sign in';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= k2_e($pageTitle) ?> — Admin · K2</title>
    <link rel="icon" href="<?= k2_e($favicon['href']) ?>" type="<?= k2_e($favicon['type']) ?>" sizes="any">
    <meta name="theme-color" content="#092950">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= $assetBase ?>/assets/css/app.css" rel="stylesheet">
    <link href="<?= $assetBase ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="k2-admin-login-body">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5 col-xl-4">
                <div class="text-center mb-4">
                    <a href="<?= k2_e(k2_home_url()) ?>" class="d-inline-block">
                        <img src="<?= k2_e(k2_logo_dark_url()) ?>" alt="K2" class="k2-logo-footer mx-auto" width="168" height="40" decoding="async">
                    </a>
                    <p class="text-muted small mt-3 mb-0">Administrator sign-in</p>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <?php if (is_array($flash) && isset($flash['error'])) : ?>
                            <div class="alert alert-danger small py-2 mb-4" role="alert"><?= k2_e((string) $flash['error']) ?></div>
                        <?php endif; ?>
                        <form method="post" action="<?= k2_e(k2_url('/admin/login')) ?>" autocomplete="on">
                            <?= k2_csrf_field() ?>
                            <div class="mb-3">
                                <label for="admin-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="admin-email" name="email" required autocomplete="username" autofocus>
                            </div>
                            <div class="mb-4">
                                <label for="admin-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="admin-password" name="password" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-k2-accent w-100">Sign in</button>
                        </form>
                    </div>
                </div>
                <p class="text-center small text-muted mt-4 mb-0">
                    <a href="<?= k2_e(k2_home_url()) ?>" class="link-secondary">← Back to site</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
