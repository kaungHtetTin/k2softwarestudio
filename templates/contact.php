<?php

declare(strict_types=1);

$pageTitle = 'Contact';
$metaDescription = 'Reach K2 about your product roadmap, engagement model, or technical questions.';
$flash = k2_flash_pull('contact');
$ci = k2_contact_info_all();

$hasAddress = trim($ci['address']) !== '';
$hasPhone = trim($ci['phone']) !== '';
$hasSocial = trim($ci['facebook_url']) !== ''
    || trim($ci['telegram_url']) !== ''
    || trim($ci['tiktok_url']) !== ''
    || trim($ci['youtube_url']) !== '';
$showAside = $hasAddress || $hasPhone || $hasSocial;

ob_start();
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="<?= $showAside ? 'col-xl-10' : 'col-lg-8' ?>">
            <p class="text-uppercase small text-muted mb-2 letter-spacing">Contact</p>
            <h1 class="display-6 fw-bold mb-3">Tell us what you are building</h1>
            <p class="lead text-muted mb-4">
                Share a short brief — we store inquiries securely and notify our inbox when email is configured.
            </p>

            <?php if (is_array($flash) && !empty($flash['ok']) && empty($flash['silent'])) : ?>
                <div class="alert alert-success border-0 shadow-sm" role="status">
                    <strong>Thank you.</strong> Your message was received.
                    <?php if (!empty($flash['needs_mail_config'])) : ?>
                        <span class="d-block mt-2 small mb-0">Email notifications are not fully configured yet (<code>CONTACT_MAIL_TO</code> / <code>MAIL_FROM_ADDRESS</code>). Your submission is still saved in the database.</span>
                    <?php elseif (!empty($flash['smtp_missing'])) : ?>
                        <span class="d-block mt-2 small mb-0">No SMTP server is configured — your message was saved, but outbound mail was not sent. Add <code>SMTP_*</code> in <code>.env</code> (recommended on Windows/XAMPP).</span>
                    <?php elseif (!empty($flash['mail_failed'])) : ?>
                        <span class="d-block mt-2 small mb-0">Your message was saved, but sending mail failed. Check SMTP credentials and server logs.</span>
                    <?php elseif (!empty($flash['mail_ok'])) : ?>
                        <span class="d-block mt-2 small mb-0">A notification was sent to our team.</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (is_array($flash) && empty($flash['ok']) && !empty($flash['errors'])) : ?>
                <div class="alert alert-danger border-0 shadow-sm" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($flash['errors'] as $err) : ?>
                            <li><?= k2_e((string) $err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
            $old = (is_array($flash) && isset($flash['old']) && is_array($flash['old'])) ? $flash['old'] : [];
            $v = static function (string $key) use ($old): string {
                $x = $old[$key] ?? '';

                return is_string($x) ? $x : '';
            };
            ?>

            <div class="row g-4 g-xl-5 align-items-start">
                <?php if ($showAside) : ?>
                    <aside class="col-lg-4 order-2 order-lg-1">
                        <div class="card border-0 shadow-sm k2-contact-aside h-100">
                            <div class="card-body p-4">
                                <h2 class="h6 text-uppercase text-muted letter-spacing mb-3">Reach us</h2>
                                <?php if ($hasAddress) : ?>
                                    <div class="d-flex gap-3 mb-4">
                                        <span class="flex-shrink-0 text-primary"><i class="bi bi-geo-alt fs-5" aria-hidden="true"></i></span>
                                        <div class="small text-muted">
                                            <?= nl2br(k2_e(trim($ci['address'])), false) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($hasPhone) : ?>
                                    <?php
                                    $phoneRaw = trim($ci['phone']);
                                    $telDigits = preg_replace('/[^\d\+]/u', '', $phoneRaw) ?? '';
                                    ?>
                                    <div class="d-flex gap-3 mb-4">
                                        <span class="flex-shrink-0 text-primary"><i class="bi bi-telephone fs-5" aria-hidden="true"></i></span>
                                        <div class="small">
                                            <?php if ($telDigits !== '') : ?>
                                                <a class="link-dark text-decoration-none" href="<?= k2_e('tel:' . $telDigits) ?>"><?= k2_e($phoneRaw) ?></a>
                                            <?php else : ?>
                                                <?= k2_e($phoneRaw) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($hasSocial) : ?>
                                    <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                        <?php if (trim($ci['facebook_url']) !== '') : ?>
                                            <li>
                                                <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3" href="<?= k2_e(trim($ci['facebook_url'])) ?>" rel="noopener noreferrer" target="_blank">
                                                    <img src="<?= k2_e(k2_asset('assets/img/ic_facebook.svg')) ?>" alt="" width="20" height="20" decoding="async" loading="lazy">
                                                    <span>Facebook</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (trim($ci['telegram_url']) !== '') : ?>
                                            <li>
                                                <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3" href="<?= k2_e(trim($ci['telegram_url'])) ?>" rel="noopener noreferrer" target="_blank">
                                                    <img src="<?= k2_e(k2_asset('assets/img/ic_telegram.svg')) ?>" alt="" width="20" height="20" decoding="async" loading="lazy">
                                                    <span>Telegram</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (trim($ci['tiktok_url']) !== '') : ?>
                                            <li>
                                                <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3" href="<?= k2_e(trim($ci['tiktok_url'])) ?>" rel="noopener noreferrer" target="_blank">
                                                    <img src="<?= k2_e(k2_asset('assets/img/ic_tiktok.svg')) ?>" alt="" width="20" height="20" decoding="async" loading="lazy">
                                                    <span>TikTok</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (trim($ci['youtube_url']) !== '') : ?>
                                            <li>
                                                <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3" href="<?= k2_e(trim($ci['youtube_url'])) ?>" rel="noopener noreferrer" target="_blank">
                                                    <img src="<?= k2_e(k2_asset('assets/img/ic_youtube.svg')) ?>" alt="" width="20" height="20" decoding="async" loading="lazy">
                                                    <span>YouTube</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </aside>
                <?php endif; ?>

                <div class="<?= $showAside ? 'col-lg-8 order-1 order-lg-2' : 'col-12' ?>">
                    <form method="post" action="<?= k2_e(k2_url('/contact')) ?>" class="k2-contact-form card border-0 shadow-sm" novalidate>
                        <?= k2_csrf_field() ?>
                        <div class="card-body p-4 p-md-5">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="contact-name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contact-name" name="name" required maxlength="255" autocomplete="name" value="<?= k2_e($v('name')) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="contact-email" name="email" required maxlength="255" autocomplete="email" value="<?= k2_e($v('email')) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="contact-phone" name="phone" maxlength="64" autocomplete="tel" value="<?= k2_e($v('phone')) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="contact-subject" name="subject" maxlength="255" value="<?= k2_e($v('subject')) ?>">
                                </div>
                                <div class="col-12">
                                    <label for="contact-message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="contact-message" name="message" rows="6" required maxlength="20000" placeholder="Tell us about timelines, stack, and goals."><?= k2_e($v('message')) ?></textarea>
                                </div>
                            </div>

                            <?php /* Honeypot — hidden from users */ ?>
                            <div class="k2-honeypot" aria-hidden="true">
                                <label for="contact-website">Website</label>
                                <input type="text" id="contact-website" name="website" tabindex="-1" autocomplete="off" value="">
                            </div>

                            <div class="mt-4 d-flex flex-wrap gap-3 align-items-center">
                                <button type="submit" class="btn btn-k2-accent btn-lg">Send message</button>
                                <p class="small text-muted mb-0"><span class="text-danger">*</span> Required fields.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
