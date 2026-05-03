<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/blog_core.php';
require_once K2_ROOT . '/includes/app_core.php';

function k2_app_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/apps' && $method === 'GET') {
        k2_app_admin_list_screen();
        exit;
    }

    if ($path === '/admin/apps/new' && $method === 'GET') {
        k2_app_admin_form_screen(null);
        exit;
    }

    if ($path === '/admin/apps/new' && $method === 'POST') {
        k2_app_admin_create();
        exit;
    }

    if ($path === '/admin/apps/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_app_admin_redirect_list();
        }
        k2_app_admin_form_screen($id);
        exit;
    }

    if ($path === '/admin/apps/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_app_admin_redirect_list();
        }
        k2_app_admin_update($id);
        exit;
    }

    if ($path === '/admin/apps/delete' && $method === 'POST') {
        k2_app_admin_delete();
        exit;
    }

    if ($path === '/admin/apps/screenshot-delete' && $method === 'POST') {
        k2_app_admin_screenshot_delete();
        exit;
    }

    header('Location: ' . k2_url('/admin/apps'), true, 302);
    exit;
}

function k2_app_admin_redirect_list(): void
{
    header('Location: ' . k2_url('/admin/apps'), true, 302);
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_app_admin_fetch_by_id(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM app_items WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_app_admin_list_screen(): void
{
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT id, title, slug, short_description, sort_order, is_visible, updated_at
         FROM app_items
         ORDER BY sort_order ASC, title ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $flash = k2_flash_pull('app_admin');
    $GLOBALS['adminNavActive'] = 'apps';
    $pageTitle = 'App gallery';
    require K2_ROOT . '/templates/admin/apps_list.php';
    exit;
}

function k2_app_admin_form_screen(?int $editId): void
{
    $app = null;
    $screenshots = [];
    if ($editId !== null) {
        $app = k2_app_admin_fetch_by_id($editId);
        if ($app === null) {
            k2_flash_set('app_admin', ['error' => 'App not found.']);
            k2_app_admin_redirect_list();
        }
        $screenshots = k2_app_screenshots_for($editId);
    }

    $flash = k2_flash_pull('app_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'apps';
    $pageTitle = $editId === null ? 'New app' : 'Edit app';
    require K2_ROOT . '/templates/admin/apps_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 * @return list<string>
 */
function k2_app_admin_validate(array $data): array
{
    $errors = [];
    if (trim($data['title'] ?? '') === '') {
        $errors[] = 'Title is required.';
    }
    if (trim($data['short_description'] ?? '') === '') {
        $errors[] = 'Short description is required.';
    }
    if (mb_strlen(trim($data['short_description'] ?? '')) > 512) {
        $errors[] = 'Short description must be at most 512 characters.';
    }

    $url = trim($data['external_url'] ?? '');
    if ($url !== '') {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = 'External URL must be a valid URL.';
        } else {
            $scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));
            if ($scheme !== 'http' && $scheme !== 'https') {
                $errors[] = 'External URL must use http:// or https://.';
            }
        }
    }

    return $errors;
}

function k2_app_admin_create(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('app_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/apps/new'), true, 303);
        exit;
    }

    $data = k2_app_admin_collect_input();
    $errors = k2_app_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('app_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/apps/new'), true, 303);
        exit;
    }

    $pdo = k2_db();
    $title = trim($data['title']);
    $slugInput = trim($data['slug'] ?? '');
    $baseSlug = $slugInput !== '' ? k2_blog_slugify($slugInput) : k2_blog_slugify($title);
    $slug = k2_app_unique_slug($pdo, $baseSlug, null);
    $short = trim($data['short_description']);
    $longRaw = (string) ($data['long_description'] ?? '');
    $long = $longRaw !== '' ? k2_blog_purify_html($longRaw) : '';
    $external = trim($data['external_url'] ?? '');
    $external = $external === '' ? null : $external;
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    $iconPath = null;
    try {
        $iconPath = k2_app_save_icon_upload('icon');
    } catch (RuntimeException $e) {
        k2_flash_set('app_form', ['errors' => [$e->getMessage()], 'old' => $data]);
        header('Location: ' . k2_url('/admin/apps/new'), true, 303);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO app_items (title, slug, short_description, long_description, icon_path, external_url, sort_order, is_visible)
             VALUES (:title, :slug, :short, :long, :icon, :ext, :sort, :vis)'
        );
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':short' => $short,
            ':long' => $long,
            ':icon' => $iconPath,
            ':ext' => $external,
            ':sort' => $sort,
            ':vis' => $visible,
        ]);
        $appId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('K2 app create: ' . $e->getMessage());
        if ($iconPath !== null) {
            k2_app_delete_upload($iconPath);
        }
        k2_flash_set('app_form', ['errors' => ['Could not save the app.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/apps/new'), true, 303);
        exit;
    }

    try {
        k2_app_admin_save_screenshot_uploads($pdo, $appId);
    } catch (RuntimeException $e) {
        error_log('K2 app screenshots: ' . $e->getMessage());
        k2_flash_set('app_admin', ['error' => 'App saved, but some screenshots failed: ' . $e->getMessage()]);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $appId, true, 303);
        exit;
    }

    k2_flash_set('app_admin', ['ok' => 'App created.']);
    header('Location: ' . k2_url('/admin/apps'), true, 303);
    exit;
}

function k2_app_admin_update(int $id): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('app_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $existing = k2_app_admin_fetch_by_id($id);
    if ($existing === null) {
        k2_flash_set('app_admin', ['error' => 'App not found.']);
        k2_app_admin_redirect_list();
    }

    $data = k2_app_admin_collect_input();
    $errors = k2_app_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('app_form', ['errors' => $errors, 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $pdo = k2_db();
    $title = trim($data['title']);
    $slugInput = trim($data['slug'] ?? '');
    $baseSlug = $slugInput !== '' ? k2_blog_slugify($slugInput) : k2_blog_slugify($title);
    $slug = k2_app_unique_slug($pdo, $baseSlug, $id);
    $short = trim($data['short_description']);
    $longRaw = (string) ($data['long_description'] ?? '');
    $long = $longRaw !== '' ? k2_blog_purify_html($longRaw) : '';
    $external = trim($data['external_url'] ?? '');
    $external = $external === '' ? null : $external;
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    $iconPath = $existing['icon_path'] ?? null;
    try {
        $newIcon = k2_app_save_icon_upload('icon');
        if ($newIcon !== null) {
            k2_app_delete_upload(is_string($iconPath) ? $iconPath : null);
            $iconPath = $newIcon;
        }
    } catch (RuntimeException $e) {
        k2_flash_set('app_form', ['errors' => [$e->getMessage()], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $id, true, 303);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE app_items SET title = :title, slug = :slug, short_description = :short, long_description = :long,
             icon_path = :icon, external_url = :ext, sort_order = :sort, is_visible = :vis WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':short' => $short,
            ':long' => $long,
            ':icon' => $iconPath,
            ':ext' => $external,
            ':sort' => $sort,
            ':vis' => $visible,
            ':id' => $id,
        ]);
    } catch (Throwable $e) {
        error_log('K2 app update: ' . $e->getMessage());
        k2_flash_set('app_form', ['errors' => ['Could not update the app.'], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $id, true, 303);
        exit;
    }

    try {
        k2_app_admin_save_screenshot_uploads($pdo, $id);
    } catch (RuntimeException $e) {
        k2_flash_set('app_admin', ['error' => 'App updated, but screenshots: ' . $e->getMessage()]);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $id, true, 303);
        exit;
    }

    k2_flash_set('app_admin', ['ok' => 'App updated.']);
    header('Location: ' . k2_url('/admin/apps'), true, 303);
    exit;
}

function k2_app_admin_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_app_admin_redirect_list();
    }

    $row = k2_app_admin_fetch_by_id($id);
    if ($row === null) {
        k2_app_admin_redirect_list();
    }

    $pdo = k2_db();
    $shots = k2_app_screenshots_for($id);

    try {
        $pdo->prepare('DELETE FROM app_items WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 app delete: ' . $e->getMessage());
        k2_flash_set('app_admin', ['error' => 'Could not delete the app.']);
        header('Location: ' . k2_url('/admin/apps'), true, 303);
        exit;
    }

    k2_app_delete_upload(isset($row['icon_path']) && is_string($row['icon_path']) ? $row['icon_path'] : null);
    foreach ($shots as $s) {
        k2_app_delete_upload(isset($s['image_path']) && is_string($s['image_path']) ? $s['image_path'] : null);
    }

    k2_flash_set('app_admin', ['ok' => 'App deleted.']);
    header('Location: ' . k2_url('/admin/apps'), true, 303);
    exit;
}

function k2_app_admin_screenshot_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $sid = (int) ($_POST['screenshot_id'] ?? 0);
    $appId = (int) ($_POST['app_id'] ?? 0);
    if ($sid <= 0 || $appId <= 0) {
        k2_app_admin_redirect_list();
    }

    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT id, app_id, image_path FROM app_screenshots WHERE id = :sid LIMIT 1');
    $stmt->execute([':sid' => $sid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false || (int) $row['app_id'] !== $appId) {
        k2_app_admin_redirect_list();
    }

    try {
        $pdo->prepare('DELETE FROM app_screenshots WHERE id = :id')->execute([':id' => $sid]);
    } catch (Throwable $e) {
        error_log('K2 screenshot delete: ' . $e->getMessage());
        k2_flash_set('app_admin', ['error' => 'Could not remove screenshot.']);
        header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $appId, true, 303);
        exit;
    }

    k2_app_delete_upload((string) $row['image_path']);
    header('Location: ' . k2_url('/admin/apps/edit') . '?id=' . $appId, true, 303);
    exit;
}

/**
 * @param array<string, string> $data
 */
function k2_app_admin_collect_input(): array
{
    return [
        'title' => (string) ($_POST['title'] ?? ''),
        'slug' => (string) ($_POST['slug'] ?? ''),
        'short_description' => (string) ($_POST['short_description'] ?? ''),
        'long_description' => (string) ($_POST['long_description'] ?? ''),
        'external_url' => (string) ($_POST['external_url'] ?? ''),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
        'is_visible' => (string) ($_POST['is_visible'] ?? ''),
    ];
}

/**
 * @throws RuntimeException
 */
function k2_app_save_icon_upload(string $fieldName): ?string
{
    return k2_app_save_image_upload_internal($fieldName, 'icons');
}

/**
 * @throws RuntimeException
 */
function k2_app_admin_save_screenshot_uploads(PDO $pdo, int $appId): void
{
    if (!isset($_FILES['screenshots']) || !is_array($_FILES['screenshots'])) {
        return;
    }

    $files = $_FILES['screenshots'];
    if (!is_array($files['name'] ?? null)) {
        return;
    }

    $n = count($files['name']);
    $maxAdd = 24;
    $count = 0;

    $ordStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM app_screenshots WHERE app_id = :aid');
    $ordStmt->execute([':aid' => $appId]);
    $maxSort = (int) $ordStmt->fetchColumn();

    for ($i = 0; $i < $n && $count < $maxAdd; ++$i) {
        $chunk = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
        if (($chunk['error'] ?? 0) === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $path = k2_app_save_image_upload_from_array($chunk, 'screenshots');
        if ($path === null) {
            continue;
        }

        ++$maxSort;
        ++$count;
        $ins = $pdo->prepare(
            'INSERT INTO app_screenshots (app_id, image_path, sort_order) VALUES (:aid, :path, :ord)'
        );
        $ins->execute([':aid' => $appId, ':path' => $path, ':ord' => $maxSort]);
    }
}

/**
 * @throws RuntimeException
 */
function k2_app_save_image_upload_internal(string $fieldName, string $subfolder): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    return k2_app_save_image_upload_from_array($_FILES[$fieldName], $subfolder);
}

/**
 * @param array<string, mixed> $f
 *
 * @throws RuntimeException
 */
function k2_app_save_image_upload_from_array(array $f, string $subfolder): ?string
{
    if (($f['error'] ?? 0) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($f['error'] ?? 0) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    if (($f['size'] ?? 0) > K2_UPLOAD_MAX_IMAGE_BYTES) {
        throw new RuntimeException('Image is too large.');
    }

    $tmp = (string) ($f['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Invalid upload.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($map[$mime])) {
        throw new RuntimeException('Allowed images: JPEG, PNG, WebP, GIF.');
    }

    $ext = $map[$mime];
    $subfolder = trim(str_replace(['..', '\\'], '', $subfolder), '/');
    if ($subfolder !== 'icons' && $subfolder !== 'screenshots') {
        throw new RuntimeException('Invalid upload target.');
    }

    $dir = K2_ROOT . '/public/uploads/apps/' . $subfolder;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Could not save image.');
    }

    return 'uploads/apps/' . $subfolder . '/' . $name;
}

function k2_app_delete_upload(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $relativePath = str_replace('\\', '/', $relativePath);
    if (
        !str_starts_with($relativePath, 'uploads/apps/icons/')
        && !str_starts_with($relativePath, 'uploads/apps/screenshots/')
    ) {
        return;
    }

    $full = K2_ROOT . '/public/' . $relativePath;
    $realFile = realpath($full);
    $baseIcons = realpath(K2_ROOT . '/public/uploads/apps/icons');
    $baseShots = realpath(K2_ROOT . '/public/uploads/apps/screenshots');
    if ($realFile === false || !is_file($realFile)) {
        return;
    }
    $ok = ($baseIcons !== false && str_starts_with($realFile, $baseIcons))
        || ($baseShots !== false && str_starts_with($realFile, $baseShots));
    if (!$ok) {
        return;
    }

    unlink($realFile);
}
