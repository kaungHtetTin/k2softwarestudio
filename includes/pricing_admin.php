<?php

declare(strict_types=1);

require_once K2_ROOT . '/includes/pricing_core.php';

function k2_pricing_admin_dispatch(string $path): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/admin/pricing' && $method === 'GET') {
        k2_pricing_admin_list_screen();
        exit;
    }

    if ($path === '/admin/pricing/new' && $method === 'GET') {
        k2_pricing_admin_form_screen(null);
        exit;
    }

    if ($path === '/admin/pricing/new' && $method === 'POST') {
        k2_pricing_admin_create();
        exit;
    }

    if ($path === '/admin/pricing/edit' && $method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            k2_pricing_admin_redirect_list();
        }
        k2_pricing_admin_form_screen($id);
        exit;
    }

    if ($path === '/admin/pricing/edit' && $method === 'POST') {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            k2_pricing_admin_redirect_list();
        }
        k2_pricing_admin_update($id);
        exit;
    }

    if ($path === '/admin/pricing/delete' && $method === 'POST') {
        k2_pricing_admin_delete();
        exit;
    }

    header('Location: ' . k2_url('/admin/pricing'), true, 302);
    exit;
}

function k2_pricing_admin_redirect_list(): void
{
    header('Location: ' . k2_url('/admin/pricing'), true, 302);
    exit;
}

/**
 * @return array<string, mixed>|null
 */
function k2_pricing_admin_fetch_by_id(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $pdo = k2_db();
    $stmt = $pdo->prepare('SELECT * FROM pricing_plans WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

/**
 * @return list<string>
 */
function k2_pricing_admin_features_lines(int $planId): array
{
    if ($planId <= 0) {
        return [];
    }
    try {
        $pdo = k2_db();
        $stmt = $pdo->prepare(
            'SELECT feature_text FROM pricing_plan_features WHERE plan_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([':id' => $planId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!is_array($rows)) {
            return [];
        }

        return array_map(static fn ($x): string => (string) $x, $rows);
    } catch (Throwable $e) {
        error_log('K2 pricing features read: ' . $e->getMessage());

        return [];
    }
}

function k2_pricing_admin_list_screen(): void
{
    $pdo = k2_db();
    $rows = $pdo->query(
        'SELECT id, project_type, title, price_display, sort_order, is_visible, updated_at
         FROM pricing_plans
         ORDER BY sort_order ASC, id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    $flash = k2_flash_pull('pricing_admin');
    $GLOBALS['adminNavActive'] = 'pricing';
    $pageTitle = 'Pricing';
    require K2_ROOT . '/templates/admin/pricing_list.php';
    exit;
}

function k2_pricing_admin_form_screen(?int $editId): void
{
    $plan = null;
    $featureLines = [];
    if ($editId !== null) {
        $plan = k2_pricing_admin_fetch_by_id($editId);
        if ($plan === null) {
            k2_flash_set('pricing_admin', ['error' => 'Plan not found.']);
            k2_pricing_admin_redirect_list();
        }
        $featureLines = k2_pricing_admin_features_lines($editId);
    }

    $flash = k2_flash_pull('pricing_form');
    $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
    $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];

    $GLOBALS['adminNavActive'] = 'pricing';
    $pageTitle = $editId === null ? 'New pricing plan' : 'Edit pricing plan';
    require K2_ROOT . '/templates/admin/pricing_form.php';
    exit;
}

/**
 * @param array<string, string> $data
 *
 * @return list<string>
 */
function k2_pricing_admin_validate(array $data): array
{
    $errors = [];
    if (trim($data['project_type'] ?? '') === '') {
        $errors[] = 'Project type is required.';
    }
    if (mb_strlen(trim($data['project_type'] ?? '')) > 128) {
        $errors[] = 'Project type must be at most 128 characters.';
    }
    if (trim($data['title'] ?? '') === '') {
        $errors[] = 'Title is required.';
    }
    if (mb_strlen(trim($data['title'] ?? '')) > 255) {
        $errors[] = 'Title must be at most 255 characters.';
    }
    if (trim($data['summary'] ?? '') === '') {
        $errors[] = 'Summary is required.';
    }
    if (mb_strlen(trim($data['summary'] ?? '')) > 512) {
        $errors[] = 'Summary must be at most 512 characters.';
    }
    if (trim($data['price_display'] ?? '') === '') {
        $errors[] = 'Price display is required (e.g. $2,500).';
    }
    if (mb_strlen(trim($data['price_display'] ?? '')) > 64) {
        $errors[] = 'Price display must be at most 64 characters.';
    }
    $note = trim((string) ($data['price_note'] ?? ''));
    if ($note !== '' && mb_strlen($note) > 128) {
        $errors[] = 'Price note must be at most 128 characters.';
    }

    $url = trim((string) ($data['external_url'] ?? ''));
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

    $linkLab = trim((string) ($data['link_label'] ?? ''));
    if ($linkLab !== '' && mb_strlen($linkLab) > 80) {
        $errors[] = 'Link label must be at most 80 characters.';
    }

    $features = k2_pricing_parse_features_text((string) ($data['features_text'] ?? ''));
    if ($features === []) {
        $errors[] = 'Add at least one feature (one per line).';
    }

    return $errors;
}

/**
 * @return array<string, string>
 */
function k2_pricing_admin_collect_input(): array
{
    return [
        'project_type' => (string) ($_POST['project_type'] ?? ''),
        'title' => (string) ($_POST['title'] ?? ''),
        'summary' => (string) ($_POST['summary'] ?? ''),
        'price_display' => (string) ($_POST['price_display'] ?? ''),
        'price_note' => (string) ($_POST['price_note'] ?? ''),
        'external_url' => (string) ($_POST['external_url'] ?? ''),
        'link_label' => (string) ($_POST['link_label'] ?? ''),
        'features_text' => (string) ($_POST['features_text'] ?? ''),
        'sort_order' => (string) ($_POST['sort_order'] ?? '0'),
        'is_visible' => (string) ($_POST['is_visible'] ?? ''),
        'remove_demo' => (string) ($_POST['remove_demo'] ?? ''),
    ];
}

/**
 * @param list<string> $features
 */
function k2_pricing_admin_save_features(PDO $pdo, int $planId, array $features): void
{
    $pdo->prepare('DELETE FROM pricing_plan_features WHERE plan_id = :id')->execute([':id' => $planId]);
    $ins = $pdo->prepare(
        'INSERT INTO pricing_plan_features (plan_id, feature_text, sort_order) VALUES (:pid, :txt, :ord)'
    );
    $ord = 0;
    foreach ($features as $line) {
        $ins->execute([':pid' => $planId, ':txt' => $line, ':ord' => $ord]);
        ++$ord;
    }
}

/**
 * @throws RuntimeException
 */
function k2_pricing_save_demo_upload(string $fieldName): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    return k2_pricing_save_demo_upload_from_array($_FILES[$fieldName]);
}

/**
 * @param array<string, mixed> $f
 *
 * @throws RuntimeException
 */
function k2_pricing_save_demo_upload_from_array(array $f): ?string
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
    $dir = K2_ROOT . '/public/uploads/pricing/demos';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Could not save image.');
    }

    return 'uploads/pricing/demos/' . $name;
}

function k2_pricing_delete_demo_file(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $relativePath = str_replace('\\', '/', $relativePath);
    if (!str_starts_with($relativePath, 'uploads/pricing/demos/')) {
        return;
    }

    $full = K2_ROOT . '/public/' . $relativePath;
    $realFile = realpath($full);
    $base = realpath(K2_ROOT . '/public/uploads/pricing/demos');
    if ($realFile === false || !is_file($realFile) || $base === false || !str_starts_with($realFile, $base)) {
        return;
    }

    unlink($realFile);
}

function k2_pricing_admin_create(): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('pricing_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/pricing/new'), true, 303);
        exit;
    }

    $data = k2_pricing_admin_collect_input();
    $errors = k2_pricing_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('pricing_form', ['errors' => $errors, 'old' => $data]);
        header('Location: ' . k2_url('/admin/pricing/new'), true, 303);
        exit;
    }

    $features = k2_pricing_parse_features_text((string) $data['features_text']);
    $pdo = k2_db();

    $demoPath = null;
    try {
        $demoPath = k2_pricing_save_demo_upload('demo_image');
    } catch (RuntimeException $e) {
        k2_flash_set('pricing_form', ['errors' => [$e->getMessage()], 'old' => $data]);
        header('Location: ' . k2_url('/admin/pricing/new'), true, 303);
        exit;
    }

    $projectType = trim($data['project_type']);
    $title = trim($data['title']);
    $summary = trim($data['summary']);
    $priceDisplay = trim($data['price_display']);
    $priceNote = trim($data['price_note']);
    $priceNote = $priceNote === '' ? null : $priceNote;
    $external = trim($data['external_url']);
    $external = $external === '' ? null : $external;
    $linkLabel = trim($data['link_label']);
    $linkLabel = $linkLabel === '' ? null : $linkLabel;
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO pricing_plans (project_type, title, summary, price_display, price_note, demo_image_path, external_url, link_label, sort_order, is_visible)
             VALUES (:pt, :t, :s, :pd, :pn, :demo, :ext, :ll, :sort, :vis)'
        );
        $stmt->execute([
            ':pt' => $projectType,
            ':t' => $title,
            ':s' => $summary,
            ':pd' => $priceDisplay,
            ':pn' => $priceNote,
            ':demo' => $demoPath,
            ':ext' => $external,
            ':ll' => $linkLabel,
            ':sort' => $sort,
            ':vis' => $visible,
        ]);
        $planId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('K2 pricing create: ' . $e->getMessage());
        if ($demoPath !== null) {
            k2_pricing_delete_demo_file($demoPath);
        }
        k2_flash_set('pricing_form', ['errors' => ['Could not save the plan.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/pricing/new'), true, 303);
        exit;
    }

    try {
        k2_pricing_admin_save_features($pdo, $planId, $features);
    } catch (Throwable $e) {
        error_log('K2 pricing features: ' . $e->getMessage());
        $pdo->prepare('DELETE FROM pricing_plans WHERE id = :id')->execute([':id' => $planId]);
        if ($demoPath !== null) {
            k2_pricing_delete_demo_file($demoPath);
        }
        k2_flash_set('pricing_form', ['errors' => ['Could not save features.'], 'old' => $data]);
        header('Location: ' . k2_url('/admin/pricing/new'), true, 303);
        exit;
    }

    k2_flash_set('pricing_admin', ['ok' => 'Pricing plan created.']);
    header('Location: ' . k2_url('/admin/pricing'), true, 303);
    exit;
}

function k2_pricing_admin_update(int $id): void
{
    if (!k2_csrf_verify()) {
        k2_flash_set('pricing_form', ['errors' => ['Invalid session. Try again.'], 'old' => $_POST]);
        header('Location: ' . k2_url('/admin/pricing/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $existing = k2_pricing_admin_fetch_by_id($id);
    if ($existing === null) {
        k2_flash_set('pricing_admin', ['error' => 'Plan not found.']);
        k2_pricing_admin_redirect_list();
    }

    $data = k2_pricing_admin_collect_input();
    $errors = k2_pricing_admin_validate($data);
    if ($errors !== []) {
        k2_flash_set('pricing_form', ['errors' => $errors, 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/pricing/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $features = k2_pricing_parse_features_text((string) $data['features_text']);
    $pdo = k2_db();

    $demoPath = isset($existing['demo_image_path']) && is_string($existing['demo_image_path'])
        ? $existing['demo_image_path']
        : null;

    $removeDemo = !empty($data['remove_demo']);
    if ($removeDemo && $demoPath !== null) {
        k2_pricing_delete_demo_file($demoPath);
        $demoPath = null;
    }

    try {
        $newDemo = k2_pricing_save_demo_upload('demo_image');
        if ($newDemo !== null) {
            if ($demoPath !== null) {
                k2_pricing_delete_demo_file($demoPath);
            }
            $demoPath = $newDemo;
        }
    } catch (RuntimeException $e) {
        k2_flash_set('pricing_form', ['errors' => [$e->getMessage()], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/pricing/edit') . '?id=' . $id, true, 303);
        exit;
    }

    $projectType = trim($data['project_type']);
    $title = trim($data['title']);
    $summary = trim($data['summary']);
    $priceDisplay = trim($data['price_display']);
    $priceNote = trim($data['price_note']);
    $priceNote = $priceNote === '' ? null : $priceNote;
    $external = trim($data['external_url']);
    $external = $external === '' ? null : $external;
    $linkLabel = trim($data['link_label']);
    $linkLabel = $linkLabel === '' ? null : $linkLabel;
    $sort = (int) ($data['sort_order'] ?? 0);
    $visible = !empty($data['is_visible']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare(
            'UPDATE pricing_plans SET project_type = :pt, title = :t, summary = :s, price_display = :pd, price_note = :pn,
             demo_image_path = :demo, external_url = :ext, link_label = :ll, sort_order = :sort, is_visible = :vis WHERE id = :id'
        );
        $stmt->execute([
            ':pt' => $projectType,
            ':t' => $title,
            ':s' => $summary,
            ':pd' => $priceDisplay,
            ':pn' => $priceNote,
            ':demo' => $demoPath,
            ':ext' => $external,
            ':ll' => $linkLabel,
            ':sort' => $sort,
            ':vis' => $visible,
            ':id' => $id,
        ]);
    } catch (Throwable $e) {
        error_log('K2 pricing update: ' . $e->getMessage());
        k2_flash_set('pricing_form', ['errors' => ['Could not update the plan.'], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/pricing/edit') . '?id=' . $id, true, 303);
        exit;
    }

    try {
        k2_pricing_admin_save_features($pdo, $id, $features);
    } catch (Throwable $e) {
        error_log('K2 pricing features update: ' . $e->getMessage());
        k2_flash_set('pricing_form', ['errors' => ['Could not update features.'], 'old' => $data + ['id' => (string) $id]]);
        header('Location: ' . k2_url('/admin/pricing/edit') . '?id=' . $id, true, 303);
        exit;
    }

    k2_flash_set('pricing_admin', ['ok' => 'Pricing plan updated.']);
    header('Location: ' . k2_url('/admin/pricing'), true, 303);
    exit;
}

function k2_pricing_admin_delete(): void
{
    if (!k2_csrf_verify()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        k2_pricing_admin_redirect_list();
    }

    $row = k2_pricing_admin_fetch_by_id($id);
    if ($row === null) {
        k2_pricing_admin_redirect_list();
    }

    $pdo = k2_db();
    try {
        $pdo->prepare('DELETE FROM pricing_plans WHERE id = :id')->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('K2 pricing delete: ' . $e->getMessage());
        k2_flash_set('pricing_admin', ['error' => 'Could not delete the plan.']);
        header('Location: ' . k2_url('/admin/pricing'), true, 303);
        exit;
    }

    $demo = isset($row['demo_image_path']) && is_string($row['demo_image_path']) ? $row['demo_image_path'] : null;
    k2_pricing_delete_demo_file($demo);

    k2_flash_set('pricing_admin', ['ok' => 'Pricing plan deleted.']);
    header('Location: ' . k2_url('/admin/pricing'), true, 303);
    exit;
}
