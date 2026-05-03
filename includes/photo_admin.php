<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/blog_core.php';
require_once K2_ROOT . '/includes/photo_core.php';

function k2_photo_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/gallery' && $method === 'GET') {
        k2_photo_admin_list_screen();
        exit;
    }

    if ($path === '/admin/gallery/new' && $method === 'GET') {
        k2_photo_admin_form_screen(null);
        exit;
    }

    if ($path === '/admin/gallery/new' && $method === 'POST') {
        k2_photo_admin_create();
        exit;
    }

    if ($path === '/admin/gallery/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_photo_admin_redirect_list();
        }
        k2_photo_admin_form_screen($id);
        exit;
    }

    if ($path === '/admin/gallery/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['album_id'] ?? 0);
        if ($id <= 0) {
            k2_photo_admin_redirect_list();
        }
        k2_photo_admin_update($id);
        exit;
    }

    if ($path === '/admin/gallery/delete' && $method === 'POST') {
        k2_photo_admin_album_delete();
        exit;
    }

    if ($path === '/admin/gallery/photo-delete' && $method === 'POST') {
        k2_photo_admin_photo_delete();
        exit;
    }

    header('Location: ' . k2_url('/admin/gallery'), true, 302);
    exit;
}

function k2_photo_admin_redirect_list(): void
{
    header('Location: ' . k2_url('/admin/gallery'), true, 302);
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_photo_admin_fetch_album(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM photo_albums WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

/**
 * @return array<string, mixed>|null
 */
function k2_photo_admin_fetch_photo(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM photos WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_photo_admin_list_screen(): void
{
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT a.id, a.title, a.slug, a.sort_order, a.created_at,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id) AS photo_count,
            (SELECT COUNT(*) FROM photos p WHERE p.album_id = a.id AND p.is_visible = 1) AS visible_count
         FROM photo_albums a
         ORDER BY a.sort_order ASC, a.title ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $flash = k2_flash_pull('photo_admin');
    $GLOBALS['adminNavActive'] = 'gallery';
    $pageTitle = 'Photo gallery';
    require K2_ROOT . '/templates/admin/gallery_albums_list.php';
    exit;
}

function k2_photo_admin_form_screen(?int $albumId): void
{
    $album = null;
    $photos = [];
    if ($albumId !== null) {
        $album = k2_photo_admin_fetch_album($albumId);
        if ($album === null) {
            k2_flash_set('photo_admin', ['error' => 'Album not found.']);
            k2_photo_admin_redirect_list();
        }
        $photos = k2_photo_list_for_album_admin($albumId);
    }

    $flash = k2_flash_pull('photo_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $adminFlash = k2_flash_pull('photo_admin');

    $GLOBALS['adminNavActive'] = 'gallery';
    $pageTitle = $albumId === null ? 'New album' : 'Edit album';
    require K2_ROOT . '/templates/admin/gallery_album_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_photo_admin_validate_album(array $data): array
{
    $errors = [];
    if (trim($data['title'] ?? '') === '') {
        $errors[] = 'Title is required.';
    }

    return $errors;
}

function k2_photo_admin_create(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('photo_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/gallery/new'), true, 303);
        exit;
    }

    $data = k2_photo_admin_collect_album_input();
    $errors = k2_photo_admin_validate_album($data);
    if ($errors !== []) {
        k2_flash_set('photo_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/gallery/new'), true, 303);
        exit;
    }

    $pdo = k2_db();
    $title = trim($data['title']);
    $slugInput = trim($data['slug'] ?? '');
    $baseSlug = $slugInput !== '' ? k2_blog_slugify($slugInput) : k2_blog_slugify($title);
    $slug = k2_photo_album_unique_slug($pdo, $baseSlug, null);
    $sort = (int) ($data['sort_order'] ?? 0);

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO photo_albums (title, slug, sort_order) VALUES (:title, :slug, :sort)'
        );
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':sort' => $sort,
        ]);
        $newId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('K2 album create: ' . $e->getMessage());
        k2_flash_set('photo_form', ['errors' => ['Could not save the album.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/gallery/new'), true, 303);
        exit;
    }

    k2_flash_set('photo_admin', ['ok' => 'Album created. Add photos below.']);
    header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $newId, true, 303);
    exit;
}

function k2_photo_admin_update(int $albumId): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('photo_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $albumId, true, 303);
        exit;
    }

    $existing = k2_photo_admin_fetch_album($albumId);
    if ($existing === null) {
        k2_flash_set('photo_admin', ['error' => 'Album not found.']);
        k2_photo_admin_redirect_list();
    }

    $data = k2_photo_admin_collect_album_input();
    $errors = k2_photo_admin_validate_album($data);
    if ($errors !== []) {
        k2_flash_set('photo_form', ['errors' => $errors, 'old' => $data + ['album_id' => (string) $albumId]]);
        header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $albumId, true, 303);
        exit;
    }

    $pdo = k2_db();
    $title = trim($data['title']);
    $slugInput = trim($data['slug'] ?? '');
    $baseSlug = $slugInput !== '' ? k2_blog_slugify($slugInput) : k2_blog_slugify($title);
    $slug = k2_photo_album_unique_slug($pdo, $baseSlug, $albumId);
    $sort = (int) ($data['sort_order'] ?? 0);

    try {
        $stmt = $pdo->prepare(
            'UPDATE photo_albums SET title = :title, slug = :slug, sort_order = :sort WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':sort' => $sort,
            ':id' => $albumId,
        ]);
    } catch (Throwable $e) {
        error_log('K2 album update: ' . $e->getMessage());
        k2_flash_set('photo_form', ['errors' => ['Could not update the album.'], 'old' => $data + ['album_id' => (string) $albumId]]);
        header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $albumId, true, 303);
        exit;
    }

    k2_photo_admin_apply_photo_meta_updates($pdo, $albumId);

    try {
        k2_photo_admin_save_new_uploads($pdo, $albumId);
    } catch (RuntimeException $e) {
        k2_flash_set('photo_admin', ['error' => 'Album saved; uploads: ' . $e->getMessage()]);
        header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $albumId, true, 303);
        exit;
    }

    k2_flash_set('photo_admin', ['ok' => 'Album saved.']);
    header('Location: ' . k2_url('/admin/gallery'), true, 303);
    exit;
}

function k2_photo_admin_apply_photo_meta_updates(PDO $pdo, int $albumId): void
{
    $captionMap = $_POST['photo_caption'] ?? null;
    $sortMap = $_POST['photo_sort'] ?? null;
    $ids = $_POST['photo_id'] ?? null;
    if (!is_array($captionMap) || !is_array($sortMap)) {
        return;
    }
    if (!is_array($ids)) {
        $ids = $ids !== null && $ids !== '' ? [$ids] : [];
    }

    $upd = $pdo->prepare(
        'UPDATE photos SET caption = :cap, sort_order = :ord, is_visible = :vis
         WHERE id = :pid AND album_id = :aid'
    );

    $visibleMap = $_POST['photo_visible'] ?? [];
    if (!is_array($visibleMap)) {
        $visibleMap = [];
    }

    foreach ($ids as $pidRaw) {
        $pid = (int) $pidRaw;
        if ($pid <= 0) {
            continue;
        }
        $key = (string) $pid;
        $cap = isset($captionMap[$key]) ? trim((string) $captionMap[$key]) : '';
        if (mb_strlen($cap) > 512) {
            $cap = mb_substr($cap, 0, 512);
        }
        $ord = isset($sortMap[$key]) ? (int) $sortMap[$key] : 0;
        $vis = isset($visibleMap[$key]) ? 1 : 0;

        $upd->execute([
            ':cap' => $cap === '' ? null : $cap,
            ':ord' => $ord,
            ':vis' => $vis,
            ':pid' => $pid,
            ':aid' => $albumId,
        ]);
    }
}

/**
 * @throws RuntimeException
 */
function k2_photo_admin_save_new_uploads(PDO $pdo, int $albumId): void
{
    if (!isset($_FILES['photos']) || !is_array($_FILES['photos'])) {
        return;
    }

    $files = $_FILES['photos'];
    if (!is_array($files['name'] ?? null)) {
        return;
    }

    $n = count($files['name']);
    $maxAdd = 36;
    $count = 0;

    $ordStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM photos WHERE album_id = :aid');
    $ordStmt->execute([':aid' => $albumId]);
    $maxSort = (int) $ordStmt->fetchColumn();

    $ins = $pdo->prepare(
        'INSERT INTO photos (album_id, image_path, caption, sort_order, is_visible)
         VALUES (:aid, :path, NULL, :ord, 1)'
    );

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

        $path = k2_gallery_save_image_upload_from_array($chunk);
        if ($path === null) {
            continue;
        }

        ++$maxSort;
        ++$count;
        $ins->execute([
            ':aid' => $albumId,
            ':path' => $path,
            ':ord' => $maxSort,
        ]);
    }
}

function k2_photo_admin_album_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_photo_admin_redirect_list();
    }

    $album = k2_photo_admin_fetch_album($id);
    if ($album === null) {
        k2_photo_admin_redirect_list();
    }

    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT id, image_path FROM photos WHERE album_id = :aid');
    $stmt->execute([':aid' => $id]);
    $shots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    try {
        $pdo->prepare('DELETE FROM photos WHERE album_id = :aid')->execute([':aid' => $id]);
        $pdo->prepare('DELETE FROM photo_albums WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 album delete: ' . $e->getMessage());
        k2_flash_set('photo_admin', ['error' => 'Could not delete the album.']);
        header('Location: ' . k2_url('/admin/gallery'), true, 303);
        exit;
    }

    foreach ($shots as $s) {
        k2_gallery_delete_upload(isset($s['image_path']) && is_string($s['image_path']) ? $s['image_path'] : null);
    }

    k2_flash_set('photo_admin', ['ok' => 'Album deleted.']);
    header('Location: ' . k2_url('/admin/gallery'), true, 303);
    exit;
}

function k2_photo_admin_photo_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $photoId = (int) ($_POST['photo_id'] ?? 0);
    $albumId = (int) ($_POST['album_id'] ?? 0);
    if ($photoId <= 0 || $albumId <= 0) {
        k2_photo_admin_redirect_list();
    }

    $row = k2_photo_admin_fetch_photo($photoId);
    if ($row === null || (int) ($row['album_id'] ?? 0) !== $albumId) {
        k2_photo_admin_redirect_list();
    }

    $pdo = k2_db();
    try {
        $pdo->prepare('DELETE FROM photos WHERE id = :id AND album_id = :aid')->execute([
            ':id' => $photoId,
            ':aid' => $albumId,
        ]);
    } catch (Throwable $e) {
        error_log('K2 photo delete: ' . $e->getMessage());
        k2_flash_set('photo_admin', ['error' => 'Could not remove photo.']);
        header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $albumId, true, 303);
        exit;
    }

    k2_gallery_delete_upload(isset($row['image_path']) && is_string($row['image_path']) ? $row['image_path'] : null);
    header('Location: ' . k2_url('/admin/gallery/edit') . '?id=' . $albumId, true, 303);
    exit;
}

/**
 * @return array<string, string>
 */
function k2_photo_admin_collect_album_input(): array
{
    return [
        'title' => (string) ($_POST['title'] ?? ''),
        'slug' => (string) ($_POST['slug'] ?? ''),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
    ];
}

/**
 * @param array<string, mixed> $f
 *
 * @throws RuntimeException
 */
function k2_gallery_save_image_upload_from_array(array $f): ?string
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
    $dir = K2_ROOT . '/public/uploads/gallery';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Could not save image.');
    }

    return 'uploads/gallery/' . $name;
}

function k2_gallery_delete_upload(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $relativePath = str_replace('\\', '/', $relativePath);
    if (!str_starts_with($relativePath, 'uploads/gallery/')) {
        return;
    }

    $full = K2_ROOT . '/public/' . $relativePath;
    $realFile = realpath($full);
    $base = realpath(K2_ROOT . '/public/uploads/gallery');
    if ($realFile === false || !is_file($realFile) || $base === false || !str_starts_with($realFile, $base)) {
        return;
    }

    unlink($realFile);
}
