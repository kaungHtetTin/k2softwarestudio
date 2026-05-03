<?php

declare(strict_types=1);

function k2_admin_logged_in(): bool
{
    return isset($_SESSION['admin_uid']) && (int) $_SESSION['admin_uid'] > 0;
}

/**
 * @return array{id: int, email: string}|null
 */
function k2_admin_user(): ?array
{
    if (!k2_admin_logged_in()) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['admin_uid'],
        'email' => (string) ($_SESSION['admin_email'] ?? ''),
    ];
}

/**
 * Redirect to login if not authenticated (§5.1).
 */
function k2_admin_require(): void
{
    if (k2_admin_logged_in()) {
        return;
    }

    header('Location: ' . k2_url('/admin/login'), true, 302);
    exit;
}

/**
 * Attempt login against `users` table. Regenerates session ID on success (§6.1).
 */
function k2_admin_attempt_login(string $email, string $password): bool
{
    $email = strtolower(trim($email));
    if ($email === '' || $password === '') {
        return false;
    }

    if (!k2_login_allowed($email)) {
        return false;
    }

    try {
        $pdo = k2_db();
        $stmt = $pdo->prepare('SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('K2 admin login: ' . $e->getMessage());

        return false;
    }

    if (!$row || !is_string($row['password_hash'] ?? null) || !password_verify($password, $row['password_hash'])) {
        k2_login_register_failure($email);

        return false;
    }

    k2_login_clear_failures($email);

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['admin_uid'] = (int) $row['id'];
    $_SESSION['admin_email'] = (string) $row['email'];

    return true;
}

function k2_admin_logout(): void
{
    unset($_SESSION['admin_uid'], $_SESSION['admin_email']);

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
