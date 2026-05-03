<?php

declare(strict_types=1);

$pageTitle = 'Messages';

$rows = [];
try {
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT id, name, email, phone, subject, message, created_at, email_sent_at, ip_address
         FROM contact_submissions
         ORDER BY created_at DESC
         LIMIT 200'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('K2 admin contacts: ' . $e->getMessage());
}

ob_start();
?>
<?php if ($rows === []) : ?>
    <p class="text-muted mb-0">No submissions yet.</p>
<?php else : ?>
    <div class="table-responsive shadow-sm rounded-3 border">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Mail sent</th>
                    <th scope="col">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r) :
                    $msg = (string) ($r['message'] ?? '');
                    $excerpt = function_exists('mb_substr') ? mb_substr($msg, 0, 160) : substr($msg, 0, 160);
                    if (strlen($msg) > 160) {
                        $excerpt .= '…';
                    }
                    ?>
                    <tr>
                        <td class="text-nowrap small"><?= k2_e((string) ($r['created_at'] ?? '')) ?></td>
                        <td><?= k2_e((string) ($r['name'] ?? '')) ?></td>
                        <td><a href="mailto:<?= k2_e((string) ($r['email'] ?? '')) ?>"><?= k2_e((string) ($r['email'] ?? '')) ?></a></td>
                        <td class="small"><?= k2_e((string) ($r['phone'] ?? '')) ?></td>
                        <td class="small"><?= k2_e((string) ($r['subject'] ?? '')) ?></td>
                        <td class="small"><?= $r['email_sent_at'] !== null ? k2_e((string) $r['email_sent_at']) : '—' ?></td>
                        <td class="small text-muted" title="<?= k2_e($msg) ?>"><?= k2_e($excerpt) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/admin/layout.php';
