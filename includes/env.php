<?php

declare(strict_types=1);

/**
 * Minimal .env loader (no Composer). Lines: KEY=value, # comments, blank lines ignored.
 */
function k2_load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            if (str_ends_with($value, $quote) && strlen($value) >= 2) {
                $value = substr($value, 1, -1);
            }
        }
        $_ENV[$name] = $value;
        putenv($name . '=' . $value);
    }
}
