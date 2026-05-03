<?php

declare(strict_types=1);

function k2_finance_currency(): string
{
    return 'MMK';
}

/**
 * Format amount for MMK (whole kyats, grouped thousands).
 */
function k2_finance_format_amount(string $amount): string
{
    $amount = trim($amount);
    if ($amount === '' || !is_numeric($amount)) {
        return '0';
    }

    return number_format((float) $amount, 0, '.', ',');
}

/**
 * Parse user-entered amount; returns normalized decimal string or null.
 */
function k2_finance_parse_amount(string $raw): ?string
{
    $s = preg_replace('/[^\d.-]/', '', str_replace(',', '', trim($raw))) ?? '';
    if ($s === '' || !is_numeric($s)) {
        return null;
    }
    $v = (float) $s;
    if ($v < 0 || $v > 999999999999.99) {
        return null;
    }

    return number_format($v, 2, '.', '');
}

/**
 * Current balance for one account (opening + income − expense + transfers).
 */
function k2_finance_account_balance(PDO $pdo, int $accountId): string
{
    if ($accountId <= 0) {
        return '0.00';
    }

    $stmt = $pdo->prepare(
        'SELECT opening_balance FROM finance_accounts WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $accountId]);
    $ob = $stmt->fetchColumn();
    $opening = is_scalar($ob) && $ob !== '' ? (string) $ob : '0.00';

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE type = :inc AND account_id = :aid'
    );
    $stmt->execute([':inc' => 'income', ':aid' => $accountId]);
    $inc = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE type = :ex AND account_id = :aid'
    );
    $stmt->execute([':ex' => 'expense', ':aid' => $accountId]);
    $ex = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE type = :tr AND transfer_to_id = :aid'
    );
    $stmt->execute([':tr' => 'transfer', ':aid' => $accountId]);
    $tin = $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE type = :tr AND transfer_from_id = :aid'
    );
    $stmt->execute([':tr' => 'transfer', ':aid' => $accountId]);
    $tout = $stmt->fetchColumn();

    $bal = (float) $opening + (float) $inc - (float) $ex + (float) $tin - (float) $tout;

    return number_format($bal, 2, '.', '');
}

/**
 * @return list<array<string, mixed>>
 */
function k2_finance_accounts_for_admin(PDO $pdo, bool $activeOnly = false): array
{
    $sql = 'SELECT id, name, currency, opening_balance, sort_order, is_active, created_at FROM finance_accounts';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

/**
 * @return list<array<string, mixed>>
 */
function k2_finance_categories_for_admin(PDO $pdo, bool $activeOnly = false): array
{
    $sql = 'SELECT id, name, sort_order, is_active FROM finance_categories';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, name ASC';

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

/**
 * @return list<array<string, mixed>>
 */
function k2_finance_transactions_fetch(
    PDO $pdo,
    ?string $dateFrom,
    ?string $dateTo,
    ?int $accountId,
    ?string $type,
    int $limit = 500
): array {
    $limit = max(1, min(2000, $limit));
    $where = ['1=1'];
    $params = [];
    if ($dateFrom !== null && $dateFrom !== '') {
        $where[] = 't.occurred_at >= :df';
        $params[':df'] = $dateFrom;
    }
    if ($dateTo !== null && $dateTo !== '') {
        $where[] = 't.occurred_at <= :dt';
        $params[':dt'] = $dateTo;
    }
    if ($accountId !== null && $accountId > 0) {
        $where[] = '(t.account_id = :aid OR t.transfer_from_id = :aidb OR t.transfer_to_id = :aidc)';
        $params[':aid'] = $accountId;
        $params[':aidb'] = $accountId;
        $params[':aidc'] = $accountId;
    }
    if ($type !== null && $type !== '' && in_array($type, ['income', 'expense', 'transfer'], true)) {
        $where[] = 't.type = :tp';
        $params[':tp'] = $type;
    }

    $sql = 'SELECT t.*, c.name AS category_name,
            af.name AS from_account_name, att.name AS to_account_name, ac.name AS account_name
            FROM finance_transactions t
            LEFT JOIN finance_categories c ON c.id = t.category_id
            LEFT JOIN finance_accounts af ON af.id = t.transfer_from_id
            LEFT JOIN finance_accounts att ON att.id = t.transfer_to_id
            LEFT JOIN finance_accounts ac ON ac.id = t.account_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY t.occurred_at DESC, t.id DESC
            LIMIT ' . (int) $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

/**
 * @return list<array{name: string, total: string}>
 */
function k2_finance_report_expense_by_category(PDO $pdo, string $dateFrom, string $dateTo): array
{
    $stmt = $pdo->prepare(
        'SELECT c.name, COALESCE(SUM(t.amount), 0) AS total
         FROM finance_transactions t
         INNER JOIN finance_categories c ON c.id = t.category_id
         WHERE t.type = :ex AND t.occurred_at >= :df AND t.occurred_at <= :dt
         GROUP BY c.id, c.name
         ORDER BY total DESC'
    );
    $stmt->execute([':ex' => 'expense', ':df' => $dateFrom, ':dt' => $dateTo]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($rows)) {
        return [];
    }

    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'name' => (string) ($r['name'] ?? ''),
            'total' => number_format((float) ($r['total'] ?? 0), 2, '.', ''),
        ];
    }

    return $out;
}

/**
 * @return array{income: string, expense: string}
 */
function k2_finance_report_totals(PDO $pdo, string $dateFrom, string $dateTo): array
{
    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE type = :inc AND occurred_at >= :df AND occurred_at <= :dt'
    );
    $stmt->execute([':inc' => 'income', ':df' => $dateFrom, ':dt' => $dateTo]);
    $inc = (string) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(amount), 0) FROM finance_transactions WHERE type = :ex AND occurred_at >= :df AND occurred_at <= :dt'
    );
    $stmt->execute([':ex' => 'expense', ':df' => $dateFrom, ':dt' => $dateTo]);
    $ex = (string) $stmt->fetchColumn();

    return [
        'income' => number_format((float) $inc, 2, '.', ''),
        'expense' => number_format((float) $ex, 2, '.', ''),
    ];
}
