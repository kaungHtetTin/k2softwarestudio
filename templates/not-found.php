<?php

declare(strict_types=1);

$pageTitle = 'Page not found';
ob_start();
?>
<div class="container py-5 text-center">
    <h1 class="display-6">404</h1>
    <p class="text-muted">This route is not defined yet.</p>
    <a class="btn btn-k2-accent" href="<?= k2_e(k2_home_url()) ?>">Home</a>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
