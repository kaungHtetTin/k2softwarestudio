<?php

declare(strict_types=1);

/**
 * PDO singleton — prepared statements only (ATTR_EMULATE_PREPARES false).
 */
function k2_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (K2_DB_NAME === '') {
        throw new RuntimeException('Database is not configured (set DB_DATABASE in .env).');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        K2_DB_HOST,
        K2_DB_PORT,
        K2_DB_NAME
    );

    $pdo = new PDO($dsn, K2_DB_USER, K2_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
