<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/blog_core.php';

function k2_faq_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/faq' && $method === 'GET') {
        k2_faq_admin_list_screen();
        exit;
    }

    if ($path === '/admin/faq/new' && $method === 'GET') {
        k2_faq_admin_form_screen(null);
        exit;
    }

    if ($path === '/admin/faq/new' && $method === 'POST') {
        k2_faq_admin_create();
        exit;
    }

    if ($path === '/admin/faq/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_faq_admin_redirect_list();
        }
        k2_faq_admin_form_screen($id);
        exit;
    }

    if ($path === '/admin/faq/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_faq_admin_redirect_list();
        }
        k2_faq_admin_update($id);
        exit;
    }

    if ($path === '/admin/faq/delete' && $method === 'POST') {
        k2_faq_admin_delete();
        exit;
    }

    header('Location: ' . k2_url('/admin/faq'), true, 302);
    exit;
}

function k2_faq_admin_redirect_list(): void
{
    header('Location: ' . k2_url('/admin/faq'), true, 302);
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_faq_admin_fetch_by_id(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM faq_items WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_faq_admin_list_screen(): void
{
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT id, question, sort_order, is_visible, updated_at
         FROM faq_items
         ORDER BY sort_order ASC, id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $flash = k2_flash_pull('faq_admin');
    $GLOBALS['adminNavActive'] = 'faq';
    $pageTitle = 'FAQ';
    require K2_ROOT . '/templates/admin/faq_list.php';
    exit;
}

function k2_faq_admin_form_screen(?int $editId): void
{
    $faq = null;
    if ($editId !== null) {
        $faq = k2_faq_admin_fetch_by_id($editId);
        if ($faq === null) {
            k2_flash_set('faq_admin', ['error' => 'FAQ entry not found.']);
            k2_faq_admin_redirect_list();
        }
    }

    $flash = k2_flash_pull('faq_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'faq';
    $pageTitle = $editId === null ? 'New FAQ' : 'Edit FAQ';
    require K2_ROOT . '/templates/admin/faq_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_faq_admin_validate(array $data): array
{
    $errors = [];
    if (trim($data['question'] ?? '') === '') {
        $errors[] = 'Question is required.';
    }
    if (mb_strlen(trim($data['question'] ?? '')) > 512) {
        $errors[] = 'Question must be at most 512 characters.';
    }
    if (trim($data['answer'] ?? '') === '') {
        $errors[] = 'Answer is required.';
    }

    return $errors;
}

function k2_faq_admin_create(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('faq_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/faq/new'), true, 303);
        exit;
    }

    $data = k2_faq_admin_collect_input();
    $errors = k2_faq_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('faq_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/faq/new'), true, 303);
        exit;
    }

    $question = trim($data['question']);
    $answerRaw = (string) ($data['answer'] ?? '');
    $answer = k2_blog_purify_html($answerRaw);
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    $pdo = k2_db();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO faq_items (question, answer, sort_order, is_visible)
             VALUES (:q, :a, :sort, :vis)'
        );
        $stmt->execute([
            ':q' => $question,
            ':a' => $answer,
            ':sort' => $sort,
            ':vis' => $visible,
        ]);
    } catch (Throwable $e) {
        error_log('K2 FAQ create: ' . $e->getMessage());
        k2_flash_set('faq_form', ['errors' => ['Could not save the FAQ entry.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/faq/new'), true, 303);
        exit;
    }

    k2_flash_set('faq_admin', ['ok' => 'FAQ entry created.']);
    header('Location: ' . k2_url('/admin/faq'), true, 303);
    exit;
}

function k2_faq_admin_update(int $id): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('faq_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/faq/edit') . '?id=' . $id, true, 303);
        exit;
    }

    if (k2_faq_admin_fetch_by_id($id) === null) {
        k2_flash_set('faq_admin', ['error' => 'FAQ entry not found.']);
        k2_faq_admin_redirect_list();
    }

    $data = k2_faq_admin_collect_input();
    $errors = k2_faq_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('faq_form', ['errors' => $errors, 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/faq/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $question = trim($data['question']);
    $answerRaw = (string) ($data['answer'] ?? '');
    $answer = k2_blog_purify_html($answerRaw);
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    $pdo = k2_db();
    try {
        $stmt = $pdo->prepare(
            'UPDATE faq_items SET question = :q, answer = :a, sort_order = :sort, is_visible = :vis WHERE id = :id'
        );
        $stmt->execute([
            ':q' => $question,
            ':a' => $answer,
            ':sort' => $sort,
            ':vis' => $visible,
            ':id' => $id,
        ]);
    } catch (Throwable $e) {
        error_log('K2 FAQ update: ' . $e->getMessage());
        k2_flash_set('faq_form', ['errors' => ['Could not update the FAQ entry.'], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/faq/edit') . '?id=' . $id, true, 303);
        exit;
    }

    k2_flash_set('faq_admin', ['ok' => 'FAQ entry updated.']);
    header('Location: ' . k2_url('/admin/faq'), true, 303);
    exit;
}

function k2_faq_admin_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_faq_admin_redirect_list();
    }

    $pdo = k2_db();
    try {
        $pdo->prepare('DELETE FROM faq_items WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 FAQ delete: ' . $e->getMessage());
        k2_flash_set('faq_admin', ['error' => 'Could not delete the FAQ entry.']);
        header('Location: ' . k2_url('/admin/faq'), true, 303);
        exit;
    }

    k2_flash_set('faq_admin', ['ok' => 'FAQ entry deleted.']);
    header('Location: ' . k2_url('/admin/faq'), true, 303);
    exit;
}

/**
 * @return array<string, string>
 */
function k2_faq_admin_collect_input(): array
{
    return [
        'question' => (string) ($_POST['question'] ?? ''),
        'answer' => (string) ($_POST['answer'] ?? ''),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
        'is_visible' => (string) ($_POST['is_visible'] ?? ''),
    ];
}
