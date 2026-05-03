<?php

declare(strict_types=1);

function k2_flash_set(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

/**
 * @return mixed|null
 */
function k2_flash_pull(string $key): mixed
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}
