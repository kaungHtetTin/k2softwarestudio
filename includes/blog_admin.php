<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/blog_core.php';

function k2_blog_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/blog' && $method === 'GET') {
        k2_blog_admin_list_screen();
        exit;
    }

    if ($path === '/admin/blog/new' && $method === 'GET') {
        k2_blog_admin_form_screen(null);
        exit;
    }

    if ($path === '/admin/blog/new' && $method === 'POST') {
        k2_blog_admin_create();
        exit;
    }

    if ($path === '/admin/blog/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_blog_admin_redirect_list();
        }
        k2_blog_admin_form_screen($id);
        exit;
    }

    if ($path === '/admin/blog/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_blog_admin_redirect_list();
        }
        k2_blog_admin_update($id);
        exit;
    }

    if ($path === '/admin/blog/delete' && $method === 'POST') {
        k2_blog_admin_delete();
        exit;
    }

    header('Location: ' . k2_url('/admin/blog'), true, 302);
    exit;
}

function k2_blog_admin_redirect_list(): void
{
    header('Location: ' . k2_url('/admin/blog'), true, 302);
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_blog_admin_fetch_by_id(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function k2_blog_admin_list_screen(): void
{
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT id, title, slug, status, published_at, created_at, updated_at
         FROM blog_posts
         ORDER BY COALESCE(published_at, created_at) DESC, id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $flash = k2_flash_pull('blog_admin');
    $GLOBALS['adminNavActive'] = 'blog';
    $pageTitle = 'Blog posts';
    require K2_ROOT . '/templates/admin/blog_list.php';
    exit;
}

/**
 * @param int|null $editId null = new post
 */
function k2_blog_admin_form_screen(?int $editId): void
{
    $post = null;
    if ($editId !== null) {
        $post = k2_blog_admin_fetch_by_id($editId);
        if ($post === null) {
            k2_flash_set('blog_admin', ['error' => 'Post not found.']);
            k2_blog_admin_redirect_list();
        }
    }

    $flash = k2_flash_pull('blog_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'blog';
    $pageTitle = $editId === null ? 'New post' : 'Edit post';
    require K2_ROOT . '/templates/admin/blog_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 * @return list<string>
 */
function k2_blog_admin_validate(array $data, bool $isUpdate): array
{
    $errors = [];
    $title = $data['title'] ?? '';
    if (trim($title) === '') {
        $errors[] = 'Title is required.';
    }
    $body = $data['body'] ?? '';
    if (trim($body) === '') {
        $errors[] = 'Body is required.';
    }
    $status = $data['status'] ?? 'draft';
    if (!in_array($status, ['draft', 'published'], true)) {
        $errors[] = 'Invalid status.';
    }

    return $errors;
}

function k2_blog_admin_create(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('blog_form', ['errors' => ['Invalid session. Please try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/blog/new'), true, 303);
        exit;
    }

    $data = k2_blog_admin_collect_input();
    $errors = k2_blog_admin_validate($data, false);
    if ($errors !== []) {
        k2_flash_set('blog_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/blog/new'), true, 303);
        exit;
    }

    $pdo = k2_db();
    $title = trim($data['title']);
    $slugInput = trim($data['slug'] ?? '');
    $baseSlug = $slugInput !== '' ? k2_blog_slugify($slugInput) : k2_blog_slugify($title);
    $slug = k2_blog_unique_slug($pdo, $baseSlug, null);
    $excerpt = trim($data['excerpt'] ?? '') !== '' ? trim($data['excerpt']) : null;
    $body = k2_blog_purify_html($data['body']);
    $status = $data['status'];
    $publishedAt = k2_blog_admin_published_at_value($status, $data['published_at'] ?? '');

    $featured = null;
    try {
        $featured = k2_blog_handle_featured_upload('featured_image');
    } catch (RuntimeException $e) {
        k2_flash_set('blog_form', ['errors' => [$e->getMessage()], 'old' => $data]);
        header('Location: ' . k2_url('/admin/blog/new'), true, 303);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO blog_posts (title, slug, excerpt, body, featured_image, status, published_at)
             VALUES (:title, :slug, :excerpt, :body, :img, :status, :pub)'
        );
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':excerpt' => $excerpt,
            ':body' => $body,
            ':img' => $featured,
            ':status' => $status,
            ':pub' => $publishedAt,
        ]);
    } catch (Throwable $e) {
        error_log('K2 blog create: ' . $e->getMessage());
        k2_flash_set('blog_form', ['errors' => ['Could not save the post.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/blog/new'), true, 303);
        exit;
    }

    k2_flash_set('blog_admin', ['ok' => 'Post created.']);
    header('Location: ' . k2_url('/admin/blog'), true, 303);
    exit;
}

function k2_blog_admin_update(int $id): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('blog_form', ['errors' => ['Invalid session. Please try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/blog/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $existing = k2_blog_admin_fetch_by_id($id);
    if ($existing === null) {
        k2_flash_set('blog_admin', ['error' => 'Post not found.']);
        k2_blog_admin_redirect_list();
    }

    $data = k2_blog_admin_collect_input();
    $errors = k2_blog_admin_validate($data, true);
    if ($errors !== []) {
        k2_flash_set('blog_form', ['errors' => $errors, 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/blog/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $pdo = k2_db();
    $title = trim($data['title']);
    $slugInput = trim($data['slug'] ?? '');
    $baseSlug = $slugInput !== '' ? k2_blog_slugify($slugInput) : k2_blog_slugify($title);
    $slug = k2_blog_unique_slug($pdo, $baseSlug, $id);
    $excerpt = trim($data['excerpt'] ?? '') !== '' ? trim($data['excerpt']) : null;
    $body = k2_blog_purify_html($data['body']);
    $status = $data['status'];
    $publishedAt = k2_blog_admin_published_at_value($status, $data['published_at'] ?? '');

    $featured = $existing['featured_image'] ?? null;
    try {
        $newFile = k2_blog_handle_featured_upload('featured_image');
        if ($newFile !== null) {
            k2_blog_safe_delete_upload(is_string($featured) ? $featured : null);
            $featured = $newFile;
        }
    } catch (RuntimeException $e) {
        k2_flash_set('blog_form', ['errors' => [$e->getMessage()], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/blog/edit') . '?id=' . $id, true, 303);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE blog_posts SET title = :title, slug = :slug, excerpt = :excerpt, body = :body,
             featured_image = :img, status = :status, published_at = :pub WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':excerpt' => $excerpt,
            ':body' => $body,
            ':img' => $featured,
            ':status' => $status,
            ':pub' => $publishedAt,
            ':id' => $id,
        ]);
    } catch (Throwable $e) {
        error_log('K2 blog update: ' . $e->getMessage());
        k2_flash_set('blog_form', ['errors' => ['Could not update the post.'], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/blog/edit') . '?id=' . $id, true, 303);
        exit;
    }

    k2_flash_set('blog_admin', ['ok' => 'Post updated.']);
    header('Location: ' . k2_url('/admin/blog'), true, 303);
    exit;
}

function k2_blog_admin_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_blog_admin_redirect_list();
    }

    $row = k2_blog_admin_fetch_by_id($id);
    if ($row === null) {
        k2_blog_admin_redirect_list();
    }

    try {
        $pdo = k2_db();
        $pdo->prepare('DELETE FROM blog_posts WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 blog delete: ' . $e->getMessage());
        k2_flash_set('blog_admin', ['error' => 'Could not delete the post.']);
        header('Location: ' . k2_url('/admin/blog'), true, 303);
        exit;
    }

    k2_blog_safe_delete_upload(isset($row['featured_image']) && is_string($row['featured_image']) ? $row['featured_image'] : null);
    k2_flash_set('blog_admin', ['ok' => 'Post deleted.']);
    header('Location: ' . k2_url('/admin/blog'), true, 303);
    exit;
}

/**
 * @return array<string, string>
 */
function k2_blog_admin_collect_input(): array
{
    return [
        'title' => (string) ($_POST['title'] ?? ''),
        'slug' => (string) ($_POST['slug'] ?? ''),
        'excerpt' => (string) ($_POST['excerpt'] ?? ''),
        'body' => (string) ($_POST['body'] ?? ''),
        'status' => (string) ($_POST['status'] ?? 'draft'),
        'published_at' => (string) ($_POST['published_at'] ?? ''),
    ];
}

function k2_blog_admin_published_at_value(string $status, string $publishedAtInput): ?string
{
    if ($status !== 'published') {
        return null;
    }
    $publishedAtInput = trim($publishedAtInput);
    if ($publishedAtInput === '') {
        return date('Y-m-d H:i:s');
    }
    $ts = strtotime($publishedAtInput);
    if ($ts === false) {
        return date('Y-m-d H:i:s');
    }

    return date('Y-m-d H:i:s', $ts);
}

/**
 * @throws RuntimeException
 */
function k2_blog_handle_featured_upload(string $fieldName): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $f = $_FILES[$fieldName];
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($f['error'] ?? 0) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Featured image upload failed.');
    }
    if (($f['size'] ?? 0) > K2_UPLOAD_MAX_IMAGE_BYTES) {
        throw new RuntimeException('Featured image is too large.');
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
        throw new RuntimeException('Featured image must be JPEG, PNG, WebP, or GIF.');
    }

    $ext = $map[$mime];
    $dir = K2_ROOT . '/public/uploads/blog';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Could not save featured image.');
    }

    return 'uploads/blog/' . $name;
}

function k2_blog_safe_delete_upload(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $relativePath = str_replace('\\', '/', $relativePath);
    if (!str_starts_with($relativePath, 'uploads/blog/')) {
        return;
    }

    $full = K2_ROOT . '/public/' . $relativePath;
    $realFile = realpath($full);
    $baseDir = realpath(K2_ROOT . '/public/uploads/blog');
    if ($realFile === false || $baseDir === false || !is_file($realFile)) {
        return;
    }
    if (!str_starts_with($realFile, $baseDir)) {
        return;
    }

    unlink($realFile);
}
