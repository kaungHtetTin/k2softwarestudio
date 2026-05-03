<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/deliverables_core.php';

function k2_deliverables_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/deliverables' && $method === 'GET') {
        k2_deliverables_admin_list_screen();
        exit;
    }

    if ($path === '/admin/deliverables/new' && $method === 'GET') {
        k2_deliverables_admin_form_screen(null);
        exit;
    }

    if ($path === '/admin/deliverables/new' && $method === 'POST') {
        k2_deliverables_admin_create();
        exit;
    }

    if ($path === '/admin/deliverables/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_deliverables_admin_redirect_list();
        }
        k2_deliverables_admin_form_screen($id);
        exit;
    }

    if ($path === '/admin/deliverables/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_deliverables_admin_redirect_list();
        }
        k2_deliverables_admin_update($id);
        exit;
    }

    if ($path === '/admin/deliverables/delete' && $method === 'POST') {
        k2_deliverables_admin_delete();
        exit;
    }

    header('Location: ' . k2_url('/admin/deliverables'), true, 302);
    exit;
}

function k2_deliverables_admin_redirect_list(): void
{
    header('Location: ' . k2_url('/admin/deliverables'), true, 302);
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_deliverables_admin_fetch_by_id(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM home_deliverables WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_deliverables_admin_list_screen(): void
{
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT id, title, icon_name, sort_order, is_visible, updated_at
         FROM home_deliverables
         ORDER BY sort_order ASC, id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $flash = k2_flash_pull('deliverables_admin');
    $GLOBALS['adminNavActive'] = 'deliverables';
    $pageTitle = 'What we deliver';
    require K2_ROOT . '/templates/admin/deliverables_list.php';
    exit;
}

function k2_deliverables_admin_form_screen(?int $editId): void
{
    $deliverable = null;
    if ($editId !== null) {
        $deliverable = k2_deliverables_admin_fetch_by_id($editId);
        if ($deliverable === null) {
            k2_flash_set('deliverables_admin', ['error' => 'Item not found.']);
            k2_deliverables_admin_redirect_list();
        }
    }

    $flash = k2_flash_pull('deliverables_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'deliverables';
    $pageTitle = $editId === null ? 'New deliverable' : 'Edit deliverable';
    require K2_ROOT . '/templates/admin/deliverables_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_deliverables_admin_validate(array $data): array
{
    $errors = [];
    if (trim($data['title'] ?? '') === '') {
        $errors[] = 'Title is required.';
    }
    if (mb_strlen(trim($data['title'] ?? '')) > 255) {
        $errors[] = 'Title must be at most 255 characters.';
    }
    if (trim($data['description'] ?? '') === '') {
        $errors[] = 'Description is required.';
    }
    if (mb_strlen(trim($data['description'] ?? '')) > 512) {
        $errors[] = 'Description must be at most 512 characters.';
    }

    $iconRaw = trim((string) ($data['icon_name'] ?? ''));
    if ($iconRaw !== '') {
        $norm = k2_deliverable_icon_normalize_slug($iconRaw);
        if ($norm === '' || !preg_match('/^[a-z0-9][a-z0-9-]{0,47}$/', $norm)) {
            $errors[] = 'Icon must be a Bootstrap Icons slug (e.g. 4-circle). You can paste bi-4-circle; the bi- part is removed automatically.';
        }
    }

    return $errors;
}

/**
 * @return array<string, string>
 */
function k2_deliverables_admin_collect_input(): array
{
    return [
        'title' => (string) ($_POST['title'] ?? ''),
        'description' => (string) ($_POST['description'] ?? ''),
        'icon_name' => (string) ($_POST['icon_name'] ?? ''),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
        'is_visible' => (string) ($_POST['is_visible'] ?? ''),
    ];
}

function k2_deliverables_admin_create(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('deliverables_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/deliverables/new'), true, 303);
        exit;
    }

    $data = k2_deliverables_admin_collect_input();
    $errors = k2_deliverables_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('deliverables_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/deliverables/new'), true, 303);
        exit;
    }

    $title = trim($data['title']);
    $description = trim($data['description']);
    $icon = k2_deliverable_icon_sanitize((string) $data['icon_name']);
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    $pdo = k2_db();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO home_deliverables (title, description, icon_name, sort_order, is_visible)
             VALUES (:t, :d, :icon, :sort, :vis)'
        );
        $stmt->execute([
            ':t' => $title,
            ':d' => $description,
            ':icon' => $icon,
            ':sort' => $sort,
            ':vis' => $visible,
        ]);
    } catch (Throwable $e) {
        error_log('K2 deliverable create: ' . $e->getMessage());
        k2_flash_set('deliverables_form', ['errors' => ['Could not save the item.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/deliverables/new'), true, 303);
        exit;
    }

    k2_flash_set('deliverables_admin', ['ok' => 'Deliverable created.']);
    header('Location: ' . k2_url('/admin/deliverables'), true, 303);
    exit;
}

function k2_deliverables_admin_update(int $id): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('deliverables_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/deliverables/edit') . '?id=' . $id, true, 303);
        exit;
    }

    if (k2_deliverables_admin_fetch_by_id($id) === null) {
        k2_flash_set('deliverables_admin', ['error' => 'Item not found.']);
        k2_deliverables_admin_redirect_list();
    }

    $data = k2_deliverables_admin_collect_input();
    $errors = k2_deliverables_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('deliverables_form', ['errors' => $errors, 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/deliverables/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $title = trim($data['title']);
    $description = trim($data['description']);
    $icon = k2_deliverable_icon_sanitize((string) $data['icon_name']);
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    $pdo = k2_db();
    try {
        $stmt = $pdo->prepare(
            'UPDATE home_deliverables SET title = :t, description = :d, icon_name = :icon, sort_order = :sort, is_visible = :vis WHERE id = :id'
        );
        $stmt->execute([
            ':t' => $title,
            ':d' => $description,
            ':icon' => $icon,
            ':sort' => $sort,
            ':vis' => $visible,
            ':id' => $id,
        ]);
    } catch (Throwable $e) {
        error_log('K2 deliverable update: ' . $e->getMessage());
        k2_flash_set('deliverables_form', ['errors' => ['Could not update the item.'], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/deliverables/edit') . '?id=' . $id, true, 303);
        exit;
    }

    k2_flash_set('deliverables_admin', ['ok' => 'Deliverable updated.']);
    header('Location: ' . k2_url('/admin/deliverables'), true, 303);
    exit;
}

function k2_deliverables_admin_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_deliverables_admin_redirect_list();
    }

    $pdo = k2_db();
    try {
        $pdo->prepare('DELETE FROM home_deliverables WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 deliverable delete: ' . $e->getMessage());
        k2_flash_set('deliverables_admin', ['error' => 'Could not delete the item.']);
        header('Location: ' . k2_url('/admin/deliverables'), true, 303);
        exit;
    }

    k2_flash_set('deliverables_admin', ['ok' => 'Deliverable deleted.']);
    header('Location: ' . k2_url('/admin/deliverables'), true, 303);
    exit;
}
