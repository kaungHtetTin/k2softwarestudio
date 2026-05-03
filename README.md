# K2 — Company portfolio & CMS

PHP · Bootstrap 5 · MySQL. See `SOFTWARE_SPECIFICATION.md` and `DEVELOPMENT_ROADMAP.md`.

## Requirements

- PHP 8.0+ (8.1+ recommended)
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled (for clean URLs under `public/`)

## Phase 0 setup

### 1. Document root

Point Apache **DocumentRoot** at the `public/` folder (recommended):

- Example: `C:/xampp/htdocs/k2/public` → `http://localhost/` if vhost is configured
- Subfolder example: `http://localhost/k2/public/` — uncomment and set `RewriteBase` in `public/.htaccess` if rewrite rules fail

### 2. Dependencies & environment

From the project root:

```text
composer install
copy .env.example .env
```

Edit `.env`:

- Set `APP_URL` to your base URL (no trailing slash), e.g. `http://localhost/k2/public`
- Set MySQL `DB_*` values
- For contact notifications: `CONTACT_MAIL_TO`, `MAIL_FROM_ADDRESS`, and usually **`SMTP_*`** on Windows/XAMPP

### 3. Database

1. Create a database (e.g. `k2_portfolio`) with charset **utf8mb4**.
2. Import `database/schema.sql` (phpMyAdmin **Import** or `mysql -u root -p k2_portfolio < database/schema.sql`).

### 4. Default administrator (after schema import)

| Field    | Value               |
| -------- | ------------------- |
| Email    | `admin@example.com` |
| Password | `ChangeMe!Admin123` |

Change this password when the login UI exists (`SOFTWARE_SPECIFICATION.md` §6.1).

### 5. Verify

Open `APP_URL` in a browser — you should see the placeholder home page.  
Try a nonsense path (e.g. `/missing-page`) — you should see the 404 template.

## Phase 1 — Security plumbing

Each request (`public/index.php`):

1. **`k2_send_security_headers()`** — CSP (allows Bootstrap on jsDelivr), `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`.
2. **`k2_session_start()`** — HttpOnly, SameSite=Lax, `Secure` when HTTPS; cookie path scoped to the app base path in subdirectories.
3. **`k2_db()`** — PDO singleton, `ERRMODE_EXCEPTION`, `ATTR_EMULATE_PREPARES => false` (use prepared statements only).
4. **`k2_e()`** — HTML escaping for templates.
5. **CSRF** — `k2_csrf_token()`, `k2_csrf_field()`, `k2_csrf_verify()` (session-backed).
6. **Login throttle** — `k2_login_allowed()`, `k2_login_register_failure()`, `k2_login_clear_failures()`, `k2_login_throttle_status()`; files under `storage/rate_limit/` (gitignored). Limits: `LOGIN_MAX_ATTEMPTS`, `LOGIN_LOCKOUT_WINDOW` in `.env`.

### Dev-only security checks (`APP_DEBUG=true`)

Visit **`/security-tests`** (e.g. `http://localhost/k2/public/security-tests`):

- POST with valid CSRF vs without token (403).
- Simulate failed logins until throttle locks; reset clears counters.
- Optional DB ping (`SELECT 1`) with CSRF-protected POST.

Disable debug in production (`APP_DEBUG=false`) — the route returns **404** when debug is off.

## Phase 2 — Public shell & Home

- **Layout:** Sticky primary nav with **active link** state, skip link, marketing footer (Product / Company / Legal), brand tokens on all public pages.
- **Home:** Hero (gradient + panel), “What we deliver” cards, social-proof band, **FAQ** accordion with anchor `#faq`, bottom CTA band.
- **Motion:** `public/assets/js/app.js` — navbar shadow on scroll; `.k2-animate` sections use `IntersectionObserver`. **`prefers-reduced-motion`** disables reveal animations and smooth-scroll.
- **Stub pages:** The **`/pricing`** route is not linked in the nav and returns **404** until you add it in `public/index.php` (planned Phase 8). **`/blog`**, **`/apps`**, **`/gallery`**, and **`/contact`** are live. Contact is a real form — **Phase 3**.

## Project layout

| Path                  | Role                                                                                                                                                                                         |
| --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `public/`             | Web root — `index.php` front controller, `assets/css/app.css`, `assets/css/admin.css`, `assets/js/app.js`                                                                                    |
| `public/assets/logo.png` | **Primary navbar/footer logo** (also accepts `assets/logo.webp`; falls back to legacy paths under `assets/img/`). |
| `public/assets/img/`     | **Hero / favicon / optional `logo-dark.*`** (`hero.webp` \| `.jpg` \| `.svg`), favicon, optional footer-specific marks |
| `includes/`           | Bootstrap, config, env loader (not web-accessible if DocumentRoot is `public/`)                                                                                                              |
| `templates/`          | PHP templates / views                                                                                                                                                                        |
| `storage/uploads/`    | Future media (blocked by `.htaccess` if exposed)                                                                                                                                             |
| `storage/rate_limit/` | Login throttle state (blocked by `.htaccess`; not committed)                                                                                                                                 |
| `database/schema.sql` | Schema + admin seed (+ default FAQ rows)                                                                                                                                                     |
| `database/migrations/add_faq_items.sql` | Adds **`faq_items`** if upgrading an older DB                                                                                                                                        |
| `database/migrations/add_home_deliverables.sql` | Adds **`home_deliverables`** if upgrading an older DB                                                                                                                      |
| `vendor/`             | Composer dependencies (PHPMailer, HTMLPurifier) — run `composer install` after clone                                                                                                           |
| `public/uploads/blog/` | Featured images (blog); scripts blocked by `.htaccess`                                                                                                                                       |
| `public/uploads/apps/` | App icons & screenshots (`icons/`, `screenshots/`); scripts blocked by `.htaccess`                                                                                                            |
| `public/uploads/gallery/` | Photo gallery uploads; scripts blocked by `.htaccess`                                                                                                                                     |
| `storage/cache/`       | HTMLPurifier serializer cache (contents gitignored)                                                                                                                                          |

## Phase 3 — Contact form

- **`/contact`** — GET shows `templates/contact.php`; POST is handled by `k2_contact_handle_post()` (`includes/contact.php`).
- **Persistence:** rows in **`contact_submissions`** (`database/schema.sql`).
- **Email:** [**PHPMailer**](https://github.com/PHPMailer/PHPMailer) via Composer (`composer install`). Uses **`SMTP_*`** when `SMTP_HOST` is set; otherwise PHP **`mail()`** (often unreliable on Windows — configure SMTP).
- **Env:** `CONTACT_MAIL_TO` (notification inbox), `MAIL_FROM_*`, optional `SMTP_*`, `CONTACT_THROTTLE_MAX`, `CONTACT_THROTTLE_WINDOW`.
- **Security:** CSRF, honeypot field `website`, IP-based rate limit (`k2_contact_allowed()` in `includes/rate_limit.php`), server-side validation.
- **Contact details:** Address, phone, Facebook / Telegram / TikTok / YouTube URLs are edited under **`/admin/contact-info`** and stored in **`site_settings`** (`includes/contact_info.php`). The Contact page reads them with **`k2_contact_info_all()`** and shows social icons from **`public/assets/img/ic_*.svg`**.

## Phase 4 — Admin authentication & shell

- Sign in: **`/admin/login`** — authenticates against the **`users`** table (`password_verify`). Same login throttle as Phase 1 (`LOGIN_*` per email + IP).
- After login: **`session_regenerate_id(true)`** (§6.1). Session keys: `admin_uid`, `admin_email`.
- **`/admin`** — dashboard hub (cards). **`/admin/deliverables`** — home page “What we deliver” cards. **`/admin/contact-info`** — contact page address & social links. **`/admin/contacts`** — contact submissions table (read-only).
- **Blog** (Phase 5), **Apps** (Phase 6), **Photo gallery** (Phase 7), **FAQ**, and **Deliverables** (below) are implemented in the sections below.
- Sign out: POST **`/admin/logout`** (CSRF). Brand styling: `assets/css/admin.css` + shared tokens from `app.css`.

Use the seeded admin from Phase 0 (`database/schema.sql`) or your own row in **`users`**.

## Phase 5 — Blog (public + CMS)

- **Public:** **`/blog`** — paginated list (`BLOG_PER_PAGE`, default 10). **`/blog/{slug}`** — single post (`templates/blog/post.php`). Only **`status = published`** rows are visible.
- **Admin:** **`/admin/blog`** — list all posts; **`/admin/blog/new`** — create; **`/admin/blog/edit?id=`** — edit; POST **`/admin/blog/delete`** — delete (CSRF). Handlers in **`includes/blog_admin.php`**.
- **HTML:** Body is sanitized with [**HTMLPurifier**](https://github.com/ezyang/htmlpurifier) (`k2_blog_purify_html()` in **`includes/blog_core.php`**). Cache directory: **`storage/cache/htmlpurifier/`**.
- **Images:** Featured uploads → **`public/uploads/blog/`** (JPEG/PNG/WebP/GIF, max size **`UPLOAD_MAX_IMAGE_BYTES`**).
- **SEO:** Per-post `<meta name="description">` when excerpt or generated plain-text preview is available.

## Phase 6 — App gallery (public + CMS)

- **Public:** **`/apps`** — grid of visible apps (`is_visible = 1`, ordered by `sort_order`, title). **`/apps/{slug}`** — detail with carousel (`app_screenshots`), optional long HTML description, external link. Helpers in **`includes/app_core.php`**; templates under **`templates/apps/`**.
- **Admin:** **`/admin/apps`** — list; **`/admin/apps/new`**, **`/admin/apps/edit?id=`** — create/update (CSRF, multipart); POST **`/admin/apps/delete`**; POST **`/admin/apps/screenshot-delete`** removes one screenshot. Handlers in **`includes/app_admin.php`**.
- **HTML:** Long description sanitized with **`k2_blog_purify_html()`** (same as blog).
- **Images:** Icons → **`public/uploads/apps/icons/`**; screenshots → **`public/uploads/apps/screenshots/`** (JPEG/PNG/WebP/GIF, max **`UPLOAD_MAX_IMAGE_BYTES`**).
- **Validation:** External URL must be empty or **http/https**.

## Phase 7 — Photo gallery (public + CMS)

- **Data model:** **`photo_albums`** (title, slug, sort) and **`photos`** (image path, optional caption, sort, `is_visible`). Helpers in **`includes/photo_core.php`**; public views in **`templates/gallery/`**.
- **Public:** **`/gallery`** — album cards (cover = first visible photo). **`/gallery/{slug}`** — masonry-style thumbnail grid; click opens a **Bootstrap modal** lightbox (lazy-loaded thumbnails).
- **Admin:** **`/admin/gallery`** — album list; **`/admin/gallery/new`** → **`/admin/gallery/edit?id=`** — metadata + multi-file uploads + per-photo caption/sort/visibility; POST **`/admin/gallery/delete`** (album + files); POST **`/admin/gallery/photo-delete`**. Handlers in **`includes/photo_admin.php`**.
- **Images:** **`public/uploads/gallery/`** (flat storage; JPEG/PNG/WebP/GIF; max **`UPLOAD_MAX_IMAGE_BYTES`**).

## FAQ (home + CMS)

- **Public:** The home page **#faq** accordion loads visible rows from **`faq_items`** (`k2_faq_list_visible()` in **`includes/faq_core.php`**). Answers are stored HTML, sanitized in admin (same purifier as blog).
- **Admin:** **`/admin/faq`** — list; **`/admin/faq/new`**, **`/admin/faq/edit?id=`** — create/update; POST **`/admin/faq/delete`**. Handlers in **`includes/faq_admin.php`**.
- **Database:** Table **`faq_items`** is in **`database/schema.sql`**. Existing databases should run **`database/migrations/add_faq_items.sql`** once.

## Home — “What we deliver” (CMS)

- **Public:** **`templates/home.php`** reads **`k2_deliverables_list_visible()`** from **`includes/deliverables_core.php`**. Each card has title, short description, and a **Bootstrap Icons** slug (e.g. `window-stack` → `bi-window-stack`).
- **Admin:** **`/admin/deliverables`** — CRUD with sort order and visibility. Handlers in **`includes/deliverables_admin.php`**.
- **Database:** Table **`home_deliverables`** is in **`database/schema.sql`**. Upgrades can run **`database/migrations/add_home_deliverables.sql`**.

## Routing

All HTTP requests go to `public/index.php`. `k2_request_path()` maps the URL to an internal path (`/` today). Add routes in `public/index.php` as phases progress — see `DEVELOPMENT_ROADMAP.md`.

## Security notes

- Never commit `.env`.
- If DocumentRoot were mistakenly set to the repo root, `includes/` could be exposed — keep DocumentRoot on `public/` only.
