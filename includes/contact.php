<?php

declare(strict_types=1);

function k2_contact_redirect_after_post(): void
{
    header('Location: ' . k2_url('/contact'), true, 303);
    exit;
}

function k2_contact_handle_post(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('contact', [
            'ok' => false,
            'errors' => ['Your session expired. Please refresh the page and try again.'],
            'old' => k2_contact_sanitize_old($_POST),
        ]);
        k2_contact_redirect_after_post();
    }

    // Honeypot — must stay empty
    if (!empty($_POST['website'])) {
        k2_flash_set('contact', ['ok' => true, 'mail_ok' => true, 'silent' => true]);
        k2_contact_redirect_after_post();
    }

    if (!k2_contact_allowed()) {
        k2_flash_set('contact', [
            'ok' => false,
            'errors' => ['Too many submissions from this network. Please try again later.'],
            'old' => k2_contact_sanitize_old($_POST),
        ]);
        k2_contact_redirect_after_post();
    }

    $name = k2_contact_trim((string) ($_POST['name'] ?? ''));
    $email = k2_contact_trim((string) ($_POST['email'] ?? ''));
    $phone = k2_contact_trim((string) ($_POST['phone'] ?? ''));
    $subject = k2_contact_trim((string) ($_POST['subject'] ?? ''));
    $message = k2_contact_trim((string) ($_POST['message'] ?? ''));

    $errors = [];
    if ($name === '' || mb_strlen($name) > 255) {
        $errors[] = 'Please enter your name (max 255 characters).';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($phone) > 64) {
        $errors[] = 'Phone is too long.';
    }
    if (mb_strlen($subject) > 255) {
        $errors[] = 'Subject is too long.';
    }
    if ($message === '') {
        $errors[] = 'Please enter a message.';
    } elseif (mb_strlen($message) > 20000) {
        $errors[] = 'Message is too long (max 20,000 characters).';
    }

    if ($errors !== []) {
        k2_flash_set('contact', [
            'ok' => false,
            'errors' => $errors,
            'old' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
            ],
        ]);
        k2_contact_redirect_after_post();
    }

    $phoneNull = $phone === '' ? null : $phone;
    $subjectNull = $subject === '' ? null : $subject;

    try {
        $pdo = k2_db();
        $stmt = $pdo->prepare(
            'INSERT INTO contact_submissions (name, email, phone, subject, message, ip_address, user_agent)
             VALUES (:name, :email, :phone, :subject, :message, :ip, :ua)'
        );
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ua = is_string($ua) ? mb_substr($ua, 0, 512) : '';
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phoneNull,
            ':subject' => $subjectNull,
            ':message' => $message,
            ':ip' => k2_client_ip(),
            ':ua' => $ua,
        ]);
        $id = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('K2 contact DB: ' . $e->getMessage());
        k2_flash_set('contact', [
            'ok' => false,
            'errors' => ['We could not save your message. Please try again shortly.'],
            'old' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
            ],
        ]);
        k2_contact_redirect_after_post();
    }

    k2_contact_register_submission();

    $mailOk = false;
    if (K2_CONTACT_MAIL_TO !== '' && K2_MAIL_FROM !== '') {
        $mailOk = k2_contact_send_notification($id, $name, $email, $phoneNull, $subjectNull, $message);
        if ($mailOk) {
            try {
                $u = $pdo->prepare('UPDATE contact_submissions SET email_sent_at = NOW() WHERE id = :id');
                $u->execute([':id' => $id]);
            } catch (Throwable $e) {
                error_log('K2 contact mail timestamp: ' . $e->getMessage());
            }
        }
    }

    $needsMailConfig = K2_CONTACT_MAIL_TO === '' || K2_MAIL_FROM === '';
    $smtpMissing = !$needsMailConfig && K2_SMTP_HOST === '' && !$mailOk;
    $mailFailed = !$needsMailConfig && K2_SMTP_HOST !== '' && !$mailOk;

    k2_flash_set('contact', [
        'ok' => true,
        'mail_ok' => $mailOk,
        'needs_mail_config' => $needsMailConfig,
        'smtp_missing' => $smtpMissing,
        'mail_failed' => $mailFailed,
    ]);

    k2_contact_redirect_after_post();
}

/**
 * @param array<string, mixed> $post
 *
 * @return array{name?: string, email?: string, phone?: string, subject?: string, message?: string}
 */
function k2_contact_sanitize_old(array $post): array
{
    $out = [];
    foreach (['name', 'email', 'phone', 'subject', 'message'] as $k) {
        if (!isset($post[$k])) {
            continue;
        }
        $v = $post[$k];
        $out[$k] = is_string($v) ? $v : '';
    }

    return $out;
}

function k2_contact_trim(string $s): string
{
    return trim(preg_replace('/\s+/u', ' ', $s) ?? '');
}
