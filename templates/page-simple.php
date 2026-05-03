<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $pageHeading */
/** @var string $pageLead plain text */

ob_start();
?>
<div class="container py-5 k2-page-head">
    <p class="text-uppercase small text-muted mb-2 letter-spacing">K2</p>
    <h1 class="display-6 fw-bold mb-3"><?= k2_e($pageHeading) ?></h1>
    <p class="lead text-muted col-lg-8 mb-0"><?= k2_e($pageLead) ?></p>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
