<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/finance_core.php';

function k2_finance_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/finance' && $method === 'GET') {
        k2_finance_admin_dashboard();
        exit;
    }

    if ($path === '/admin/finance/accounts' && $method === 'GET') {
        k2_finance_admin_accounts_list();
        exit;
    }

    if ($path === '/admin/finance/accounts/new' && $method === 'GET') {
        k2_finance_admin_account_form(null);
        exit;
    }

    if ($path === '/admin/finance/accounts/new' && $method === 'POST') {
        k2_finance_admin_account_save(null);
        exit;
    }

    if ($path === '/admin/finance/accounts/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_finance_admin_redirect_accounts();
        }
        k2_finance_admin_account_form($id);
        exit;
    }

    if ($path === '/admin/finance/accounts/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_finance_admin_redirect_accounts();
        }
        k2_finance_admin_account_save($id);
        exit;
    }

    if ($path === '/admin/finance/categories' && $method === 'GET') {
        k2_finance_admin_categories_list();
        exit;
    }

    if ($path === '/admin/finance/categories/new' && $method === 'GET') {
        k2_finance_admin_category_form(null);
        exit;
    }

    if ($path === '/admin/finance/categories/new' && $method === 'POST') {
        k2_finance_admin_category_save(null);
        exit;
    }

    if ($path === '/admin/finance/categories/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_finance_admin_redirect_categories();
        }
        k2_finance_admin_category_form($id);
        exit;
    }

    if ($path === '/admin/finance/categories/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_finance_admin_redirect_categories();
        }
        k2_finance_admin_category_save($id);
        exit;
    }

    if ($path === '/admin/finance/categories/delete' && $method === 'POST') {
        k2_finance_admin_category_delete();
        exit;
    }

    if ($path === '/admin/finance/transactions' && $method === 'GET') {
        k2_finance_admin_transactions_list();
        exit;
    }

    if ($path === '/admin/finance/transactions/new' && $method === 'GET') {
        k2_finance_admin_transaction_form(null);
        exit;
    }

    if ($path === '/admin/finance/transactions/new' && $method === 'POST') {
        k2_finance_admin_transaction_save(null);
        exit;
    }

    if ($path === '/admin/finance/transactions/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_finance_admin_redirect_transactions();
        }
        k2_finance_admin_transaction_form($id);
        exit;
    }

    if ($path === '/admin/finance/transactions/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_finance_admin_redirect_transactions();
        }
        k2_finance_admin_transaction_save($id);
        exit;
    }

    if ($path === '/admin/finance/transactions/delete' && $method === 'POST') {
        k2_finance_admin_transaction_delete();
        exit;
    }

    if ($path === '/admin/finance/transfer/new' && $method === 'GET') {
        k2_finance_admin_transfer_form(null);
        exit;
    }

    if ($path === '/admin/finance/transfer/new' && $method === 'POST') {
        k2_finance_admin_transfer_save(null);
        exit;
    }

    if ($path === '/admin/finance/transfer/edit' && $method === 'GET') {
        $tid = (int) ($_GET['id'] ?? 0);
        if ($tid <= 0) {
            header('Location: ' . k2_url('/admin/finance/transactions'), true, 302);
            exit;
        }
        k2_finance_admin_transfer_form($tid);
        exit;
    }

    if ($path === '/admin/finance/transfer/edit' && $method === 'POST') {
        $tid = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($tid <= 0) {
            header('Location: ' . k2_url('/admin/finance/transactions'), true, 302);
            exit;
        }
        k2_finance_admin_transfer_save($tid);
        exit;
    }

    if ($path === '/admin/finance/reports' && $method === 'GET') {
        k2_finance_admin_reports();
        exit;
    }

    header('Location: ' . k2_url('/admin/finance'), true, 302);
    exit;
}

function k2_finance_admin_redirect_accounts(): void
{
    header('Location: ' . k2_url('/admin/finance/accounts'), true, 302);
    exit;
}

function k2_finance_admin_redirect_categories(): void
{
    header('Location: ' . k2_url('/admin/finance/categories'), true, 302);
    exit;
}

function k2_finance_admin_redirect_transactions(): void
{
    header('Location: ' . k2_url('/admin/finance/transactions'), true, 302);
    exit;
}

function k2_finance_admin_dashboard(): void
{
    $pdo = k2_db();
    $accounts = k2_finance_accounts_for_admin($pdo, true);
    $balances = [];
    $totalRemaining = 0.0;
    foreach ($accounts as $a) {
        $aid = (int) ($a['id'] ?? 0);
        if ($aid > 0) {
            $b = k2_finance_account_balance($pdo, $aid);
            $balances[$aid] = $b;
            $totalRemaining += (float) $b;
        }
    }

    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');
    $monthTotals = k2_finance_report_totals($pdo, $monthStart, $monthEnd);
    $monthNet = number_format((float) $monthTotals['income'] - (float) $monthTotals['expense'], 2, '.', '');
    $monthLabel = date('F Y');
    $totalRemaining = number_format($totalRemaining, 2, '.', '');

    $flash = k2_flash_pull('finance_admin');
    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = 'Finance';
    require K2_ROOT . '/templates/admin/finance/dashboard.php';
    exit;
}

function k2_finance_admin_accounts_list(): void
{
    $pdo = k2_db();
    $rows = k2_finance_accounts_for_admin($pdo, false);
    $balances = [];
    foreach ($rows as $a) {
        $aid = (int) ($a['id'] ?? 0);
        if ($aid > 0) {
            $balances[$aid] = k2_finance_account_balance($pdo, $aid);
        }
    }

    $flash = k2_flash_pull('finance_admin');
    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = 'Accounts';
    require K2_ROOT . '/templates/admin/finance/accounts_list.php';
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_finance_admin_fetch_account(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM finance_accounts WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_finance_admin_account_form(?int $editId): void
{
    $account = null;
    if ($editId !== null) {
        $account = k2_finance_admin_fetch_account($editId);
        if ($account === null) {
            k2_flash_set('finance_admin', ['error' => 'Account not found.']);
            k2_finance_admin_redirect_accounts();
        }
    }

    $flash = k2_flash_pull('finance_account_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = $editId === null ? 'New account' : 'Edit account';
    require K2_ROOT . '/templates/admin/finance/account_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_finance_admin_validate_account(array $data): array
{
    $errors = [];
    if (trim($data['name'] ?? '') === '') {
        $errors[] = 'Account name is required.';
    }
    if (mb_strlen(trim($data['name'] ?? '')) > 128) {
        $errors[] = 'Name must be at most 128 characters.';
    }
    $ob = k2_finance_parse_amount(trim((string) ($data['opening_balance'] ?? '')) === '' ? '0' : (string) $data['opening_balance']);
    if ($ob === null) {
        $errors[] = 'Opening balance must be a valid non-negative amount.';
    }

    return $errors;
}

function k2_finance_admin_account_save(?int $editId): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('finance_account_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/accounts/new' : '/admin/finance/accounts/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $data = [
        'name' => (string) ($_POST['name'] ?? ''),
        'opening_balance' => (string) ($_POST['opening_balance'] ?? '0'),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
        'is_active' => (string) ($_POST['is_active'] ?? ''),
    ];

    $errors = k2_finance_admin_validate_account($data);
    if ($errors !== []) {
        k2_flash_set('finance_account_form', ['errors' => $errors, 'old' => $data + ($editId !== null ? ['id' => (string) $editId] : [])]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/accounts/new' : '/admin/finance/accounts/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $name = trim($data['name']);
    $obParsed = k2_finance_parse_amount((string) $data['opening_balance']);
    $ob = $obParsed ?? '0.00';
    $sort = (int) ($data['sort_order'] ?? 0);
    $active = !empty($data['is_active']) ? 1 : 0;
    $pdo = k2_db();

    if ($editId === null) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO finance_accounts (name, currency, opening_balance, sort_order, is_active)
                 VALUES (:n, :cur, :ob, :so, :ia)'
            );
            $stmt->execute([
                ':n' => $name,
                ':cur' => k2_finance_currency(),
                ':ob' => $ob,
                ':so' => $sort,
                ':ia' => $active,
            ]);
        } catch (Throwable $e) {
            error_log('K2 finance account create: ' . $e->getMessage());
            k2_flash_set('finance_account_form', ['errors' => ['Could not save account.'], 'old' => $data]);
            header('Location: ' . k2_url('/admin/finance/accounts/new'), true, 303);
            exit;
        }

        k2_flash_set('finance_admin', ['ok' => 'Account created.']);
        header('Location: ' . k2_url('/admin/finance/accounts'), true, 303);
        exit;
    }

    $existing = k2_finance_admin_fetch_account($editId);
    if ($existing === null) {
        k2_flash_set('finance_admin', ['error' => 'Account not found.']);
        k2_finance_admin_redirect_accounts();
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE finance_accounts SET name = :n, opening_balance = :ob, sort_order = :so, is_active = :ia WHERE id = :id'
        );
        $stmt->execute([
            ':n' => $name,
            ':ob' => $ob,
            ':so' => $sort,
            ':ia' => $active,
            ':id' => $editId,
        ]);
    } catch (Throwable $e) {
        error_log('K2 finance account update: ' . $e->getMessage());
        k2_flash_set('finance_account_form', ['errors' => ['Could not update account.'], 'old' => $data + ['id' => (string) $editId]]);
        header('Location: ' . k2_url('/admin/finance/accounts/edit') . '?id=' . $editId, true, 303);
        exit;
    }

    k2_flash_set('finance_admin', ['ok' => 'Account updated.']);
    header('Location: ' . k2_url('/admin/finance/accounts'), true, 303);
    exit;
}

function k2_finance_admin_categories_list(): void
{
    $pdo = k2_db();
    $rows = k2_finance_categories_for_admin($pdo, false);

    $flash = k2_flash_pull('finance_admin');
    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = 'Categories';
    require K2_ROOT . '/templates/admin/finance/categories_list.php';
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_finance_admin_fetch_category(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM finance_categories WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_finance_admin_category_form(?int $editId): void
{
    $category = null;
    if ($editId !== null) {
        $category = k2_finance_admin_fetch_category($editId);
        if ($category === null) {
            k2_flash_set('finance_admin', ['error' => 'Category not found.']);
            k2_finance_admin_redirect_categories();
        }
    }

    $flash = k2_flash_pull('finance_category_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = $editId === null ? 'New category' : 'Edit category';
    require K2_ROOT . '/templates/admin/finance/category_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_finance_admin_validate_category(array $data): array
{
    $errors = [];
    if (trim($data['name'] ?? '') === '') {
        $errors[] = 'Category name is required.';
    }
    if (mb_strlen(trim($data['name'] ?? '')) > 128) {
        $errors[] = 'Name must be at most 128 characters.';
    }

    return $errors;
}

function k2_finance_admin_category_save(?int $editId): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('finance_category_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/categories/new' : '/admin/finance/categories/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $data = [
        'name' => (string) ($_POST['name'] ?? ''),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
        'is_active' => (string) ($_POST['is_active'] ?? ''),
    ];

    $errors = k2_finance_admin_validate_category($data);
    if ($errors !== []) {
        k2_flash_set('finance_category_form', ['errors' => $errors, 'old' => $data + ($editId !== null ? ['id' => (string) $editId] : [])]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/categories/new' : '/admin/finance/categories/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $name = trim($data['name']);
    $sort = (int) ($data['sort_order'] ?? 0);
    $active = !empty($data['is_active']) ? 1 : 0;
    $pdo = k2_db();

    if ($editId === null) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO finance_categories (name, sort_order, is_active) VALUES (:n, :so, :ia)'
            );
            $stmt->execute([':n' => $name, ':so' => $sort, ':ia' => $active]);
        } catch (Throwable $e) {
            error_log('K2 finance category create: ' . $e->getMessage());
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'That category name already exists.' : 'Could not save category.';
            k2_flash_set('finance_category_form', ['errors' => [$msg], 'old' => $data]);
            header('Location: ' . k2_url('/admin/finance/categories/new'), true, 303);
            exit;
        }

        k2_flash_set('finance_admin', ['ok' => 'Category created.']);
        header('Location: ' . k2_url('/admin/finance/categories'), true, 303);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE finance_categories SET name = :n, sort_order = :so, is_active = :ia WHERE id = :id'
        );
        $stmt->execute([':n' => $name, ':so' => $sort, ':ia' => $active, ':id' => $editId]);
    } catch (Throwable $e) {
        error_log('K2 finance category update: ' . $e->getMessage());
        $msg = str_contains($e->getMessage(), 'Duplicate') ? 'That category name already exists.' : 'Could not update category.';
        k2_flash_set('finance_category_form', ['errors' => [$msg], 'old' => $data + ['id' => (string) $editId]]);
        header('Location: ' . k2_url('/admin/finance/categories/edit') . '?id=' . $editId, true, 303);
        exit;
    }

    k2_flash_set('finance_admin', ['ok' => 'Category updated.']);
    header('Location: ' . k2_url('/admin/finance/categories'), true, 303);
    exit;
}

function k2_finance_admin_category_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_finance_admin_redirect_categories();
    }

    $pdo = k2_db();
    try {
        $pdo->prepare('DELETE FROM finance_categories WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 finance category delete: ' . $e->getMessage());
        k2_flash_set('finance_admin', ['error' => 'Cannot delete — category is used by existing transactions.']);
        header('Location: ' . k2_url('/admin/finance/categories'), true, 303);
        exit;
    }

    k2_flash_set('finance_admin', ['ok' => 'Category deleted.']);
    header('Location: ' . k2_url('/admin/finance/categories'), true, 303);
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_finance_admin_validate_transaction(array $data): array
{
    $errors = [];
    $type = (string) ($data['type'] ?? '');
    if (!in_array($type, ['income', 'expense', 'transfer'], true)) {
        $errors[] = 'Select a valid transaction type.';
    }

    $amt = k2_finance_parse_amount((string) ($data['amount'] ?? ''));
    if ($amt === null) {
        $errors[] = 'Enter a valid positive amount.';
    }

    $dateRaw = trim((string) ($data['occurred_at'] ?? ''));
    if ($dateRaw === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRaw)) {
        $errors[] = 'Enter a valid date.';
    }

    $desc = trim((string) ($data['description'] ?? ''));
    if (mb_strlen($desc) > 512) {
        $errors[] = 'Description is too long.';
    }

    if ($type === 'income') {
        $aid = (int) ($data['account_id'] ?? 0);
        if ($aid <= 0) {
            $errors[] = 'Select the account that receives this income.';
        }
    } elseif ($type === 'expense') {
        $aid = (int) ($data['account_id'] ?? 0);
        if ($aid <= 0) {
            $errors[] = 'Select the account that pays this expense.';
        }
        $cid = (int) ($data['category_id'] ?? 0);
        if ($cid <= 0) {
            $errors[] = 'Select a category for this expense.';
        }
    } elseif ($type === 'transfer') {
        $from = (int) ($data['transfer_from_id'] ?? 0);
        $to = (int) ($data['transfer_to_id'] ?? 0);
        if ($from <= 0 || $to <= 0) {
            $errors[] = 'Select both accounts for the transfer.';
        } elseif ($from === $to) {
            $errors[] = 'Transfer must be between two different accounts.';
        }
    }

    return $errors;
}

/**
 * @return array<string, string|null>
 */
function k2_finance_admin_collect_transaction(): array
{
    return [
        'type' => (string) ($_POST['type'] ?? 'expense'),
        'amount' => (string) ($_POST['amount'] ?? ''),
        'occurred_at' => (string) ($_POST['occurred_at'] ?? ''),
        'description' => (string) ($_POST['description'] ?? ''),
        'account_id' => (string) ($_POST['account_id'] ?? ''),
        'category_id' => (string) ($_POST['category_id'] ?? ''),
        'transfer_from_id' => (string) ($_POST['transfer_from_id'] ?? ''),
        'transfer_to_id' => (string) ($_POST['transfer_to_id'] ?? ''),
    ];
}

/**
 * @param array<string, string|null> $d
 */
function k2_finance_admin_transaction_row_payload(array $d, string $amountNorm): array
{
    $type = (string) $d['type'];
    $desc = trim((string) $d['description']);
    $dt = trim((string) $d['occurred_at']);

    if ($type === 'income') {
        $acc = (int) $d['account_id'];
        $cid = (int) ($d['category_id'] ?? 0);

        return [
            'type' => 'income',
            'amount' => $amountNorm,
            'occurred_at' => $dt,
            'description' => $desc,
            'category_id' => $cid > 0 ? $cid : null,
            'account_id' => $acc,
            'transfer_from_id' => null,
            'transfer_to_id' => null,
        ];
    }

    if ($type === 'expense') {
        $acc = (int) $d['account_id'];
        $cid = (int) ($d['category_id'] ?? 0);

        return [
            'type' => 'expense',
            'amount' => $amountNorm,
            'occurred_at' => $dt,
            'description' => $desc,
            'category_id' => $cid,
            'account_id' => $acc,
            'transfer_from_id' => null,
            'transfer_to_id' => null,
        ];
    }

    return [
        'type' => 'transfer',
        'amount' => $amountNorm,
        'occurred_at' => $dt,
        'description' => $desc,
        'category_id' => null,
        'account_id' => null,
        'transfer_from_id' => (int) $d['transfer_from_id'],
        'transfer_to_id' => (int) $d['transfer_to_id'],
    ];
}

function k2_finance_admin_transactions_list(): void
{
    $pdo = k2_db();
    $df = trim((string) ($_GET['date_from'] ?? ''));
    $dt = trim((string) ($_GET['date_to'] ?? ''));
    $aid = (int) ($_GET['account_id'] ?? 0);
    $tp = trim((string) ($_GET['type'] ?? ''));

    $df = $df !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $df) ? $df : '';
    $dt = $dt !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt) ? $dt : '';
    $tp = in_array($tp, ['income', 'expense', 'transfer'], true) ? $tp : '';

    $rows = k2_finance_transactions_fetch(
        $pdo,
        $df !== '' ? $df : null,
        $dt !== '' ? $dt : null,
        $aid > 0 ? $aid : null,
        $tp !== '' ? $tp : null
    );

    $accounts = k2_finance_accounts_for_admin($pdo, false);

    $flash = k2_flash_pull('finance_admin');
    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = 'Transactions';
    require K2_ROOT . '/templates/admin/finance/transactions_list.php';
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_finance_admin_fetch_transaction(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM finance_transactions WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_finance_admin_transaction_form(?int $editId): void
{
    $pdo = k2_db();
    $tx = null;
    if ($editId !== null) {
        $tx = k2_finance_admin_fetch_transaction($editId);
        if ($tx === null) {
            k2_flash_set('finance_admin', ['error' => 'Transaction not found.']);
            k2_finance_admin_redirect_transactions();
        }
        if (($tx['type'] ?? '') === 'transfer') {
            header('Location: ' . k2_url('/admin/finance/transfer/edit') . '?id=' . $editId, true, 303);
            exit;
        }
    }

    $accounts = k2_finance_accounts_for_admin($pdo, true);
    $categories = k2_finance_categories_for_admin($pdo, $editId === null);

    $flash = k2_flash_pull('finance_transaction_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = $editId === null ? 'New transaction' : 'Edit transaction';
    require K2_ROOT . '/templates/admin/finance/transaction_form.php';
    exit;
}

function k2_finance_admin_transaction_save(?int $editId): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('finance_transaction_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/transactions/new' : '/admin/finance/transactions/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $data = k2_finance_admin_collect_transaction();
    if (($data['type'] ?? '') === 'transfer') {
        k2_flash_set('finance_transaction_form', ['errors' => ['Balance transfers use the dedicated transfer form.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/finance/transactions/new'), true, 303);
        exit;
    }

    $errors = k2_finance_admin_validate_transaction($data);
    $amt = k2_finance_parse_amount((string) $data['amount']);
    if ($amt !== null && (float) $amt <= 0) {
        $amt = null;
    }
    if ($errors !== [] || $amt === null) {
        k2_flash_set('finance_transaction_form', ['errors' => $errors !== [] ? $errors : ['Enter a valid positive amount.'], 'old' => $data + ($editId !== null ? ['id' => (string) $editId] : [])]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/transactions/new' : '/admin/finance/transactions/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $row = k2_finance_admin_transaction_row_payload($data, $amt);
    $pdo = k2_db();

    if ($editId === null) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO finance_transactions (type, amount, occurred_at, description, category_id, account_id, transfer_from_id, transfer_to_id)
                 VALUES (:ty, :am, :oc, :ds, :cid, :aid, :tf, :tt)'
            );
            $stmt->execute([
                ':ty' => $row['type'],
                ':am' => $row['amount'],
                ':oc' => $row['occurred_at'],
                ':ds' => $row['description'],
                ':cid' => $row['category_id'],
                ':aid' => $row['account_id'],
                ':tf' => $row['transfer_from_id'],
                ':tt' => $row['transfer_to_id'],
            ]);
        } catch (Throwable $e) {
            error_log('K2 finance tx create: ' . $e->getMessage());
            k2_flash_set('finance_transaction_form', ['errors' => ['Could not save transaction.'], 'old' => $data]);
            header('Location: ' . k2_url('/admin/finance/transactions/new'), true, 303);
            exit;
        }

        k2_flash_set('finance_admin', ['ok' => 'Transaction recorded.']);
        header('Location: ' . k2_url('/admin/finance/transactions'), true, 303);
        exit;
    }

    $existing = k2_finance_admin_fetch_transaction($editId);
    if ($existing === null) {
        k2_flash_set('finance_admin', ['error' => 'Transaction not found.']);
        k2_finance_admin_redirect_transactions();
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE finance_transactions SET type = :ty, amount = :am, occurred_at = :oc, description = :ds,
             category_id = :cid, account_id = :aid, transfer_from_id = :tf, transfer_to_id = :tt WHERE id = :id'
        );
        $stmt->execute([
            ':ty' => $row['type'],
            ':am' => $row['amount'],
            ':oc' => $row['occurred_at'],
            ':ds' => $row['description'],
            ':cid' => $row['category_id'],
            ':aid' => $row['account_id'],
            ':tf' => $row['transfer_from_id'],
            ':tt' => $row['transfer_to_id'],
            ':id' => $editId,
        ]);
    } catch (Throwable $e) {
        error_log('K2 finance tx update: ' . $e->getMessage());
        k2_flash_set('finance_transaction_form', ['errors' => ['Could not update transaction.'], 'old' => $data + ['id' => (string) $editId]]);
        header('Location: ' . k2_url('/admin/finance/transactions/edit') . '?id=' . $editId, true, 303);
        exit;
    }

    k2_flash_set('finance_admin', ['ok' => 'Transaction updated.']);
    header('Location: ' . k2_url('/admin/finance/transactions'), true, 303);
    exit;
}

/**
 * @return array<string, string>
 */
function k2_finance_admin_transfer_collect(): array
{
    return [
        'transfer_from_id' => (string) ($_POST['transfer_from_id'] ?? ''),
        'transfer_to_id' => (string) ($_POST['transfer_to_id'] ?? ''),
        'amount' => (string) ($_POST['amount'] ?? ''),
        'occurred_at' => (string) ($_POST['occurred_at'] ?? ''),
        'description' => (string) ($_POST['description'] ?? ''),
    ];
}

function k2_finance_admin_transfer_form(?int $editId): void
{
    $pdo = k2_db();
    $tx = null;
    if ($editId !== null) {
        $tx = k2_finance_admin_fetch_transaction($editId);
        if ($tx === null) {
            k2_flash_set('finance_admin', ['error' => 'Transfer not found.']);
            k2_finance_admin_redirect_transactions();
        }
        if (($tx['type'] ?? '') !== 'transfer') {
            header('Location: ' . k2_url('/admin/finance/transactions/edit') . '?id=' . $editId, true, 303);
            exit;
        }
    }

    $accounts = k2_finance_accounts_for_admin($pdo, true);
    $canTransfer = count($accounts) >= 2;

    $flash = k2_flash_pull('finance_transfer_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = $editId === null ? 'Balance transfer' : 'Edit transfer';
    require K2_ROOT . '/templates/admin/finance/transfer_form.php';
    exit;
}

function k2_finance_admin_transfer_save(?int $editId): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('finance_transfer_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/transfer/new' : '/admin/finance/transfer/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $raw = k2_finance_admin_transfer_collect();
    $data = array_merge($raw, [
        'type' => 'transfer',
        'account_id' => '',
        'category_id' => '',
    ]);

    $errors = k2_finance_admin_validate_transaction($data);
    $amt = k2_finance_parse_amount((string) $data['amount']);
    if ($amt !== null && (float) $amt <= 0) {
        $amt = null;
    }
    if ($errors !== [] || $amt === null) {
        k2_flash_set('finance_transfer_form', [
            'errors' => $errors !== [] ? $errors : ['Enter a valid positive amount.'],
            'old' => $raw + ($editId !== null ? ['id' => (string) $editId] : []),
        ]);
        header('Location: ' . k2_url($editId === null ? '/admin/finance/transfer/new' : '/admin/finance/transfer/edit') . ($editId !== null ? '?id=' . $editId : ''), true, 303);
        exit;
    }

    $row = k2_finance_admin_transaction_row_payload($data, $amt);
    $pdo = k2_db();

    if ($editId === null) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO finance_transactions (type, amount, occurred_at, description, category_id, account_id, transfer_from_id, transfer_to_id)
                 VALUES (:ty, :am, :oc, :ds, :cid, :aid, :tf, :tt)'
            );
            $stmt->execute([
                ':ty' => $row['type'],
                ':am' => $row['amount'],
                ':oc' => $row['occurred_at'],
                ':ds' => $row['description'],
                ':cid' => $row['category_id'],
                ':aid' => $row['account_id'],
                ':tf' => $row['transfer_from_id'],
                ':tt' => $row['transfer_to_id'],
            ]);
        } catch (Throwable $e) {
            error_log('K2 finance transfer create: ' . $e->getMessage());
            k2_flash_set('finance_transfer_form', ['errors' => ['Could not save transfer.'], 'old' => $raw]);
            header('Location: ' . k2_url('/admin/finance/transfer/new'), true, 303);
            exit;
        }

        k2_flash_set('finance_admin', ['ok' => 'Transfer recorded.']);
        header('Location: ' . k2_url('/admin/finance/transactions'), true, 303);
        exit;
    }

    $existing = k2_finance_admin_fetch_transaction($editId);
    if ($existing === null || ($existing['type'] ?? '') !== 'transfer') {
        k2_flash_set('finance_admin', ['error' => 'Transfer not found.']);
        k2_finance_admin_redirect_transactions();
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE finance_transactions SET type = :ty, amount = :am, occurred_at = :oc, description = :ds,
             category_id = :cid, account_id = :aid, transfer_from_id = :tf, transfer_to_id = :tt WHERE id = :id'
        );
        $stmt->execute([
            ':ty' => $row['type'],
            ':am' => $row['amount'],
            ':oc' => $row['occurred_at'],
            ':ds' => $row['description'],
            ':cid' => $row['category_id'],
            ':aid' => $row['account_id'],
            ':tf' => $row['transfer_from_id'],
            ':tt' => $row['transfer_to_id'],
            ':id' => $editId,
        ]);
    } catch (Throwable $e) {
        error_log('K2 finance transfer update: ' . $e->getMessage());
        k2_flash_set('finance_transfer_form', ['errors' => ['Could not update transfer.'], 'old' => $raw + ['id' => (string) $editId]]);
        header('Location: ' . k2_url('/admin/finance/transfer/edit') . '?id=' . $editId, true, 303);
        exit;
    }

    k2_flash_set('finance_admin', ['ok' => 'Transfer updated.']);
    header('Location: ' . k2_url('/admin/finance/transactions'), true, 303);
    exit;
}

function k2_finance_admin_transaction_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_finance_admin_redirect_transactions();
    }

    $pdo = k2_db();
    try {
        $pdo->prepare('DELETE FROM finance_transactions WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 finance tx delete: ' . $e->getMessage());
        k2_flash_set('finance_admin', ['error' => 'Could not delete transaction.']);
        header('Location: ' . k2_url('/admin/finance/transactions'), true, 303);
        exit;
    }

    k2_flash_set('finance_admin', ['ok' => 'Transaction deleted.']);
    header('Location: ' . k2_url('/admin/finance/transactions'), true, 303);
    exit;
}

function k2_finance_admin_reports(): void
{
    $pdo = k2_db();

    $df = trim((string) ($_GET['date_from'] ?? ''));
    $dt = trim((string) ($_GET['date_to'] ?? ''));
    if ($df === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
        $df = date('Y-m-01');
    }
    if ($dt === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt)) {
        $dt = date('Y-m-t');
    }

    $totals = k2_finance_report_totals($pdo, $df, $dt);
    $byCat = k2_finance_report_expense_by_category($pdo, $df, $dt);

    $accounts = k2_finance_accounts_for_admin($pdo, true);
    $balances = [];
    foreach ($accounts as $a) {
        $aid = (int) ($a['id'] ?? 0);
        if ($aid > 0) {
            $balances[$aid] = k2_finance_account_balance($pdo, $aid);
        }
    }

    $incFloat = (float) $totals['income'];
    $expFloat = (float) $totals['expense'];
    $net = number_format($incFloat - $expFloat, 2, '.', '');

    $flash = k2_flash_pull('finance_admin');
    $GLOBALS['adminNavActive'] = 'finance';
    $pageTitle = 'Reports';
    require K2_ROOT . '/templates/admin/finance/reports.php';
    exit;
}
