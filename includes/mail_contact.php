<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send site notification for a contact submission. Returns true if mail accepted by transport.
 */
function k2_contact_send_notification(
    int $id,
    string $name,
    string $email,
    ?string $phone,
    ?string $subject,
    string $message
): bool {
    $to = K2_CONTACT_MAIL_TO;
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $from = K2_MAIL_FROM;
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($from, K2_MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo($email, $name);

        $subjLine = $subject !== null && $subject !== '' ? $subject : 'Website inquiry';
        $mail->Subject = '[Contact #' . $id . '] ' . $subjLine;

        $lines = [
            'New contact form submission',
            '',
            'ID: ' . $id,
            'Name: ' . $name,
            'Email: ' . $email,
            'Phone: ' . ($phone ?? ''),
            'Subject: ' . ($subject ?? ''),
            '',
            'Message:',
            $message,
        ];
        $mail->Body = implode("\n", $lines);
        $mail->isHTML(false);

        if (K2_SMTP_HOST !== '') {
            $mail->isSMTP();
            $mail->Host = K2_SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = K2_SMTP_USER;
            $mail->Password = K2_SMTP_PASS;
            $mail->Port = K2_SMTP_PORT;

            $enc = strtolower(K2_SMTP_ENCRYPTION);
            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
        } else {
            $mail->isMail();
        }

        $mail->send();

        return true;
    } catch (Throwable $e) {
        error_log('K2 contact mail: ' . $e->getMessage());

        return false;
    }
}
