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

/** Minimum length for a new admin password. */
const K2_ADMIN_PASSWORD_MIN_LEN = 8;

/**
 * @return list<string>
 */
function k2_admin_password_change_validate(string $current, string $new, string $confirm): array
{
    $errors = [];
    if ($current === '') {
        $errors[] = 'Enter your current password.';
    }
    if ($new === '') {
        $errors[] = 'Enter a new password.';
    } elseif (mb_strlen($new) < K2_ADMIN_PASSWORD_MIN_LEN) {
        $errors[] = 'New password must be at least ' . (string) K2_ADMIN_PASSWORD_MIN_LEN . ' characters.';
    }
    if ($new !== $confirm) {
        $errors[] = 'New password and confirmation do not match.';
    }
    if ($new !== '' && $current !== '' && hash_equals($current, $new)) {
        $errors[] = 'New password must be different from your current password.';
    }

    return $errors;
}

/**
 * Verifies the current password and sets a new hash for the given user.
 *
 * @return 'ok'|'wrong_password'|'error'
 */
function k2_admin_change_password(int $userId, string $currentPassword, string $newPassword): string
{
    try {
        $pdo = k2_db();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('K2 admin change password (read): ' . $e->getMessage());

        return 'error';
    }

    if (
        !$row
        || !is_string($row['password_hash'] ?? null)
        || !password_verify($currentPassword, $row['password_hash'])
    ) {
        return 'wrong_password';
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($hash === false) {
        return 'error';
    }

    try {
        $upd = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
        $upd->execute([':h' => $hash, ':id' => $userId]);
    } catch (Throwable $e) {
        error_log('K2 admin change password (write): ' . $e->getMessage());

        return 'error';
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    return 'ok';
}
