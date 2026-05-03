<?php

declare(strict_types=1);

$path = k2_request_path();
$slug = k2_gallery_album_slug_from_request_path($path);
if ($slug === null || $slug === '') {
    http_response_code(404);
    require K2_ROOT . '/templates/not-found.php';
    exit;
}

$album = k2_photo_album_fetch_by_slug($slug);
if ($album === null) {
    http_response_code(404);
    require K2_ROOT . '/templates/not-found.php';
    exit;
}

$albumId = (int) ($album['id'] ?? 0);
$photos = k2_photo_list_visible_for_album($albumId);
$title = (string) ($album['title'] ?? 'Album');
$pageTitle = $title;
$metaDescription = $photos !== []
    ? $title . ' — photo album · K2 gallery.'
    : $title . ' · K2 gallery.';

ob_start();
?>
<div class="k2-page-head border-0 bg-transparent px-0 pt-0">
    <div class="container py-4 py-lg-5">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= k2_e(k2_url('/gallery')) ?>" class="text-decoration-none">Gallery</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= k2_e($title) ?></li>
            </ol>
        </nav>
        <h1 class="display-6 fw-bold text-dark mb-0"><?= k2_e($title) ?></h1>
    </div>
</div>

<div class="container pb-5">
    <?php if ($photos === []) : ?>
        <p class="text-muted mb-0">No photos in this album yet.</p>
    <?php else : ?>
        <div class="row g-3 g-md-4">
            <?php foreach ($photos as $p) :
                $full = k2_asset((string) ($p['image_path'] ?? ''));
                $cap = trim((string) ($p['caption'] ?? ''));
                $alt = $cap !== '' ? $cap : $title;
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <button type="button"
                        class="btn btn-link p-0 border-0 shadow-sm rounded overflow-hidden w-100 ratio ratio-1x1 k2-gallery-thumb"
                        style="background: #e9ecef;"
                        data-bs-toggle="modal"
                        data-bs-target="#k2GalleryModal"
                        data-k2-full="<?= k2_e($full) ?>"
                        data-k2-caption="<?= k2_e($cap) ?>"
                        aria-label="<?= k2_e($alt !== '' ? 'View: ' . $alt : 'View photo') ?>">
                        <img src="<?= k2_e($full) ?>" alt="<?= k2_e($alt) ?>" class="object-fit-cover w-100 h-100" loading="lazy" width="400" height="400">
                    </button>
                    <?php if ($cap !== '') : ?>
                        <p class="small text-muted mt-2 mb-0 text-center text-md-start"><?= k2_e($cap) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="modal fade" id="k2GalleryModal" tabindex="-1" aria-labelledby="k2GalleryModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
                <div class="modal-content bg-dark text-white border-0">
                    <div class="modal-header border-secondary border-opacity-25">
                        <h2 class="modal-title h6 mb-0" id="k2GalleryModalTitle">Photo</h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-2 p-md-4">
                        <img src="" alt="" class="img-fluid rounded shadow" id="k2GalleryModalImg" width="1200" height="800" loading="lazy" style="max-height: 85vh; object-fit: contain;">
                        <p class="small text-white-50 mt-3 mb-0 px-2" id="k2GalleryModalCaption"></p>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var modal = document.getElementById('k2GalleryModal');
            if (!modal) return;
            modal.addEventListener('show.bs.modal', function (e) {
                var btn = e.relatedTarget;
                if (!btn || !btn.getAttribute) return;
                var src = btn.getAttribute('data-k2-full') || '';
                var cap = btn.getAttribute('data-k2-caption') || '';
                var img = document.getElementById('k2GalleryModalImg');
                var capEl = document.getElementById('k2GalleryModalCaption');
                var titleEl = document.getElementById('k2GalleryModalTitle');
                if (img) {
                    img.src = src;
                    img.alt = cap || '';
                }
                if (capEl) {
                    capEl.textContent = cap;
                    capEl.classList.toggle('d-none', !cap);
                }
                if (titleEl) {
                    titleEl.textContent = cap || 'Photo';
                }
            });
            modal.addEventListener('hidden.bs.modal', function () {
                var img = document.getElementById('k2GalleryModalImg');
                if (img) img.src = '';
            });
        })();
        </script>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
