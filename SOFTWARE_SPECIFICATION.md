# Software Specification  
## Company Portfolio & Content Management Web Application

**Document version:** 1.2  
**Last updated:** 2026-05-03  
**Status:** Draft — partial stakeholder confirmation (see §9)  

---

## 1. Purpose & Scope

### 1.1 Purpose

Deliver a **company portfolio website** for a software development firm with **public marketing pages**, **animated responsive UI** aligned with a modern “big tech” aesthetic, and **admin-facing systems** to manage blog posts, app gallery entries, and photo gallery content. Data persists in **MySQL**.

### 1.2 In Scope

| Area | Description |
|------|-------------|
| Public site | Home (hero, FAQ), Contact, Pricing, App Gallery (browse), Photo Gallery (browse), Blog (list & article) |
| Admin / CMS | Blog management, App gallery management, Photo gallery management |
| Cross-cutting | Responsive layout, animations, accessibility baseline, primary + secondary brand colors, **consistent UI** across public + admin, **high-security** baseline (see §6) |

### 1.3 Out of Scope (unless agreed later)

- Native mobile apps  
- Multi-tenant SaaS or customer login for the public site  
- Payment gateway checkout (pricing may be informational only)  
- Full i18n / multiple languages  
- Email marketing automation  

---

## 2. Technology Stack

| Layer | Technology |
|-------|------------|
| Markup & structure | HTML5 |
| Styling | CSS3, **Bootstrap 5** (confirmed) |
| Client behavior | JavaScript (ES6+), **AJAX** where appropriate for admin or progressive enhancement |
| Server | **PHP** — **accepted** for all server logic (MySQL, forms, admin, email sending) |
| Database | **MySQL** |

**Note:** **PHP** is the chosen backend for XAMPP/shared hosting compatibility and CMS requirements; scope remains “plain PHP” unless a framework is agreed later.

---

## 3. Design System & UX Direction

### 3.1 Brand & Theme

- **Primary theme color:** `#092950` (deep navy).  
- **Secondary / accent color:** `#DCB361` (gold) — CTAs, highlights, icons, and interactive accents; pair with primary for buttons, links on dark backgrounds, and emphasis bands.  
- **Derived palette:** complementary neutrals (off-white / light gray backgrounds); ensure **contrast** when placing `#DCB361` text on light backgrounds or white text on `#092950` (**WCAG AA**).  
- **Typography:** modern sans-serif stack (e.g. system UI or a webfont such as Inter / DM Sans — **to be confirmed**).  
- **Imagery:** crisp product/app screenshots, subtle gradients or mesh backgrounds optional for “big tech” feel.

### 3.2 “Big Tech” Modern Animated UI

- **Responsive:** mobile-first; breakpoints consistent with Bootstrap.  
- **Motion:** purposeful animations — hero entrance, scroll-triggered section reveals, subtle hover/focus states on cards and buttons; avoid excessive motion (respect `prefers-reduced-motion`).  
- **Layout:** generous whitespace, card-based sections, clear hierarchy, sticky navigation optional.  
- **Components:** hero with headline/subhead/primary CTA; FAQ as accordion; pricing as tier cards; galleries as responsive grids with lightbox or detail views.

### 3.3 Accessibility & Quality Bar

- Semantic HTML landmarks (`header`, `main`, `nav`, `footer`).  
- Sufficient color contrast for text on `#092950`, `#DCB361`, and neutral backgrounds (**WCAG AA target**).  
- Keyboard navigable interactive components (menus, modals, accordions).

### 3.4 Consistent UI (public + admin)

- **Single design language:** same Bootstrap 5 grid, spacing scale, button styles (primary/secondary), card patterns, form controls, and typography on marketing pages and `/admin` screens.  
- **Shared layout artifacts:** common header/navigation patterns where applicable; admin uses the same color tokens (`#092950`, `#DCB361`) and neutral grays — avoid a visually unrelated “default Bootstrap” admin skin unless overridden to match brand.  
- **Predictable patterns:** list → edit/create flows, confirmation before destructive actions, consistent success/error feedback (alerts or toasts).  
- **Responsive parity:** admin must remain usable on tablet width minimum; public site fully mobile-first.

---

## 4. Functional Requirements

### 4.1 Public — Home Page

- **Hero:** company positioning, primary CTA(s), optional background animation or gradient.  
- **FAQ:** expandable/collapsible questions (Bootstrap accordion or equivalent).  
- **Additional sections (recommended):** brief services/value props, logos/social proof placeholder, footer with links and legal placeholders.

### 4.2 Public — Blog

- **Blog index:** paginated or “load more” list of posts (title, excerpt, date, optional thumbnail).  
- **Article view:** full content, publication date, optional author display.  
- **SEO basics:** unique `<title>` and meta description per article (**implementation detail**).

### 4.3 Public — App Gallery

- **Gallery index:** grid/list of apps (name, short description, icon/screenshot, link or detail page).  
- **App detail (optional):** extended description, screenshots carousel, external store/link.

### 4.4 Public — Photo Gallery

- **Browsable gallery:** categories or albums (**to be confirmed**), thumbnail grid, enlarged view (modal/lightbox).  
- **Performance:** lazy loading for images where feasible.

### 4.5 Public — Pricing

- **Pricing page:** tier cards (name, price, feature list, CTA).  
- **Currency & billing period:** display rules (**to be confirmed** — e.g. USD monthly).

### 4.6 Public — Contact

- **Contact form:** fields at minimum: name, email, message; optional phone/subject.  
- **Server-side validation** and spam mitigation baseline (honeypot or rate limiting — **to be confirmed**).  
- **Outcome (confirmed):** **both** — persist each submission to **MySQL** (`contact_submissions` or equivalent) **and** send notification via **email** (e.g. PHP `mail()` or SMTP — **SMTP preferred** for production reliability).

---

## 5. Admin / Management Systems

### 5.1 Authentication

- **Single administrator account** (confirmed): one set of credentials for CMS access — no multi-user roles in v1 unless explicitly expanded later.  
- **Protected admin area:** login, session-based auth, logout; all `/admin` routes require authentication.  
- **Account hygiene:** strong password policy at setup/change; optional session timeout for idle admin (**implementation detail** under §6).

### 5.2 Blog Management System (CMS)

- CRUD for posts: title, slug, excerpt, body (rich text or Markdown/HTML — **to be confirmed**), featured image, published/draft, published_at.  
- Optional: categories/tags (**to be confirmed**).

### 5.3 App Gallery Management System

- CRUD for app entries: title, short description, long description, icon, screenshots, display order, visibility, optional external URL.

### 5.4 Photo Gallery Management System

- CRUD for photos/albums: upload or URL (**to be confirmed**), caption, sort order, visibility.  
- **Storage:** filesystem under web root with restricted upload types vs object storage (**to be confirmed** for production).

---

## 6. Non-Functional Requirements

| Category | Requirement |
|----------|-------------|
| Performance | Fast First Contentful Paint on Home; optimized images; minimal blocking scripts |
| Security | **High priority** — see §6.1 |
| UI consistency | Per §3.4 — Bootstrap 5 + brand tokens across public and admin |
| Compatibility | Latest two versions of major browsers; degraded but usable experience on older browsers |
| Hosting | Suitable for typical PHP + MySQL hosting (e.g. XAMPP dev, shared/VPS prod); HTTPS in production (**required** for secure cookies and transport) |

### 6.1 Security (high priority)

Non-exhaustive baseline; implementation must treat these as mandatory:

| Area | Requirement |
|------|-------------|
| **Transport** | **HTTPS** in production; no mixed-content assets; HSTS where host allows. |
| **Secrets** | DB password, SMTP, and any API keys **outside** web root; env-style config (e.g. `.env` not committed) or server-only includes. |
| **SQL** | **Prepared statements** (PDO) for all dynamic SQL; least-privilege DB user for the app. |
| **XSS** | **Context-appropriate escaping** for all output (`htmlspecialchars` in HTML; careful handling if rich HTML is allowed in blog body). |
| **CSRF** | **Tokens** on all state-changing forms (contact, admin login if POST, CMS create/update/delete). |
| **Sessions** | **Secure**, **HttpOnly**, **SameSite** session cookies; regenerate session ID on admin login; reasonable idle timeout. |
| **Passwords** | **`password_hash`** / **`password_verify`** (PASSWORD_DEFAULT); no plaintext storage. |
| **Auth hardening** | Rate limit or progressive delay on failed admin login; generic error messages (“Invalid credentials”); optional IP allowlist **only** if stable ops model. |
| **Uploads** | **Allowlist** MIME/extension; store outside web root or block script execution; rename files; size caps; virus scanning **optional** (host-dependent). |
| **Headers** | Baseline security headers where applicable (e.g. `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`; CSP **recommended** with tuning). |
| **Dependencies** | Minimal third-party PHP libs; keep Bootstrap/JS from trusted CDNs or self-host with integrity hashes if CDN used. |
| **Logging** | Log failed logins and server errors without leaking secrets; avoid exposing stack traces in production. |

---

## 7. Data Model (Conceptual — MySQL)

High-level entities (tables to be detailed in a schema design phase):

- `users` (single admin row / credential model for v1)  
- `blog_posts`  
- `app_items` (+ optional `app_screenshots`)  
- `photo_albums`, `photos`  
- `contact_submissions` (required — stores all inquiries in parallel with email notification)  
- `site_settings` (optional key/value for hero copy, etc.)

---

## 8. Sitemap (Draft)

```
/                 Home
/blog             Blog index
/blog/{slug}      Article
/apps             App gallery
/apps/{slug}      App detail (if used)
/gallery          Photo gallery
/pricing          Pricing
/contact          Contact
/faq              Optional dedicated FAQ or anchor-only on Home

/admin/*          Management UIs (behind auth)
```

---

## 9. Clarifications & Assumptions

### 9.1 Confirmed decisions

| Topic | Decision |
|-------|----------|
| **Backend** | **PHP** accepted for all server logic, CMS, and integrations. |
| **Brand colors** | Primary `#092950`, secondary / accent `#DCB361`. |
| **Contact form** | **Both:** save to **MySQL** and send **email** notification for each submission. |
| **Bootstrap** | **Bootstrap 5** for layout and components. |
| **Admin model** | **Single administrator** — one account for CMS in v1 (no multi-role editor split unless scope changes). |
| **Security** | **High priority** — requirements enumerated in **§6.1** (HTTPS, secrets handling, PDO, XSS/CSRF, session cookies, password hashing, upload rules, headers, logging). |
| **UI** | **Consistent** public + admin experience per **§3.4** (shared Bootstrap 5 patterns and brand tokens). |

### 9.2 Open items (pending confirmation)

1. **Blog authoring format:** WYSIWYG rich text vs Markdown vs HTML textarea.  
2. **Typography:** Google Fonts (e.g. Inter) vs system fonts only.  
3. **Internationalization:** English-only for v1?  
4. **Pricing:** Informational only vs “contact us” vs future Stripe — CTAs behavior.  
5. **Contact email:** SMTP host/credentials for production; optional admin UI to list/export submissions.  
6. **Photo gallery:** Single flat gallery vs multiple albums/categories; upload max size and allowed formats.  
7. **Legal pages:** Privacy policy / terms placeholders required for launch?  
8. **Spam / rate limiting:** Honeypot, session throttle, or third-party (e.g. reCAPTCHA) preference.

---

## 10. Deliverables (Implementation Phase — Reference)

- Deployable web application with public pages and admin sections  
- MySQL schema and seed data (optional demo content)  
- Configuration template for environment (DB credentials, base URL, **SMTP**, session settings) — **no secrets in repo**  
- Brief handover notes for deployment, HTTPS, backup, and security checklist (§6.1)  

---

## 11. Document Control

| Version | Date       | Author        | Notes        |
|---------|------------|---------------|--------------|
| 1.0     | 2026-05-03 | Specification | Initial draft |
| 1.1     | 2026-05-03 | Specification | PHP confirmed; secondary `#DCB361`; contact = DB + email |
| 1.2     | 2026-05-03 | Specification | Bootstrap 5; single admin; §6.1 security; §3.4 UI consistency |

---

*End of software specification.*
