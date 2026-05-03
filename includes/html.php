<?php

declare(strict_types=1);

/**
 * Escape output for HTML text nodes and attributes (UTF-8).
 */
function k2_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
