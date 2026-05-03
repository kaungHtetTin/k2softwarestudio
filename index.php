<?php

declare(strict_types=1);

$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base === '/' || $base === '.' || $base === '') {
    $base = '';
}
header('Location: ' . $base . '/public/index.php', true, 302);
exit;
