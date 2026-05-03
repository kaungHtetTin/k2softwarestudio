# Development Roadmap  
## Company Portfolio & CMS (PHP, Bootstrap 5, MySQL)

**Document version:** 1.0  
**Last updated:** 2026-05-03  
**Related:** `SOFTWARE_SPECIFICATION.md`

---

## Principles

- **Vertical slices:** each phase delivers something **testable** (not “all DB first, then all UI”).
- **Security built in:** authentication, CSRF, PDO, sessions, and upload rules ship **with** the first features that need them—not only at the end (see spec §6.1).
- **One design system early:** shared layout + CSS variables (`#092950`, `#DCB361`) + Bootstrap 5 overrides before building many pages (see spec §3.4).

---

## Phase 0 — Project Foundation

**Goals:** Runnable app skeleton, configuration discipline, initial database.

| Task | Output |
|------|--------|
| Directory layout (e.g. `public` vs `includes`, `admin`, `uploads` with safe exposure) | Clear structure |
| Environment config (e.g. `.env` not committed + `.env.example`) | DB + SMTP placeholders |
| Composer optional—plain PHP acceptable unless libraries are needed | Decision documented |
| MySQL schema v1: `users`, `blog_posts`, app tables, photo/album tables, `contact_submissions` | SQL install/migrate script |
| Seed **single admin** (`password_hash`) + setup notes | Documented install |
| Routing approach (front controller vs page scripts) | Short architecture note |

**Exit criteria:** Fresh setup → configure env → import DB → placeholder home page loads (HTTPS locally preferred, or HTTP dev with cookie caveats documented).

---

## Phase 1 — Security & Shared Plumbing

**Goals:** Reusable foundation so later phases do not repeat security mistakes.

| Task | Output |
|------|--------|
| PDO access layer—**prepared statements only** | DB helper |
| Session bootstrap (cookie flags: dev vs production) | Central include |
| CSRF token generation and validation | All mutating forms |
| HTML escaping helper for template output | XSS baseline |
| Security headers (baseline §6.1) | Applied globally |
| Failed-login throttling / delay (session/file/IP—pick one approach) | Auth hardening |

**Exit criteria:** Demonstrable CSRF rejection and login throttling on a test endpoint.

---

## Phase 2 — Public UI Shell & Home

**Goals:** First branded, responsive experience (spec §3.4).

| Task | Output |
|------|--------|
| Shared `header` / `footer`, navigation, active states | Common public layout |
| Bootstrap 5 + custom CSS (brand variables, buttons, cards) | Consistent chrome |
| Home: hero, FAQ accordion, optional supporting sections | Scroll/motion with `prefers-reduced-motion` respect |

**Exit criteria:** Home is responsive and on-brand; admin not required yet.

---

## Phase 3 — Contact (Database + Email)

**Goals:** Spec §4.6—persist and notify.

| Task | Output |
|------|--------|
| Contact form UI + server-side validation | Required fields + optional fields |
| Insert each submission into `contact_submissions` | Audit trail |
| Email notification via **SMTP** (preferred); safe failure logging | DB + email both; no secret leakage on errors |
| Spam baseline (honeypot, rate limit—finalize with open spec items) | Reduced abuse |

**Exit criteria:** Test submission appears in DB and notification is received (or mail failure is logged clearly).

---

## Phase 4 — Admin Authentication & Dashboard Shell

**Goals:** Single administrator; protect all CMS routes.

| Task | Output |
|------|--------|
| Login, logout, auth guard for `/admin` | No anonymous CMS |
| Session ID regeneration on successful login | §6.1 |
| Admin layout using same brand tokens as public | §3.4 |
| Dashboard hub linking to Blog, App gallery, Photo gallery, Contact list | Navigation |

**Exit criteria:** Unauthenticated access to admin routes redirects to login; authenticated users see consistent admin UI.

---

## Phase 5 — Blog (Public + CMS)

**Goals:** Primary editorial workflow.

| Task | Output |
|------|--------|
| Public: index + single post by slug; 404 handling | SEO-friendly URLs |
| Admin: create/read/update/delete; draft vs published; unique slug | Full CRUD |
| Featured image upload with §6.1 upload policy | Safe media handling |
| Body format decision (sanitized HTML vs Markdown)—resolve spec open items | Consistent sanitization/escaping |

**Exit criteria:** Publish from admin; view on public site without stored XSS.

---

## Phase 6 — App Gallery (Public + Management)

| Task | Output |
|------|--------|
| Public gallery grid/list (+ optional detail page) | Apps discoverable |
| Admin CRUD: metadata, screenshots/icons, sort order, visibility | Management |
| Uploads follow same rules as Phase 5 | Security parity |

---

## Phase 7 — Photo Gallery (Public + Management)

| Task | Output |
|------|--------|
| Resolve albums vs flat gallery (spec open items)—implement chosen model | Schema/UI |
| Thumbnails, lightbox/modal, lazy loading where appropriate | UX + performance |
| Admin: upload/order/caption/visibility | CMS |

---

## Phase 8 — Pricing, FAQ Placement & Polish

| Task | Output |
|------|--------|
| Pricing page (tiers—CMS vs config file per implementation choice) | §4.5 |
| Motion polish site-wide (meaningful animation, not excessive) | “Big tech” feel |
| IA: FAQ only on Home vs dedicated `/faq` | Final routes |

---

## Phase 9 — Pre-Launch Hardening & Launch

| Task | Output |
|------|--------|
| Production HTTPS, cookie flags, CSP (start report-only if needed) | §6.1 |
| Custom error pages; no stack traces exposed | Safe failures |
| SEO: `<title>`/meta, Open Graph basics, `sitemap.xml` | Discoverability |
| Asset caching, image optimization | Performance |
| Backup procedure: DB + uploads | Operations |

**Exit criteria:** Walk through spec §6.1 checklist on staging; successful end-to-end contact test.

---

## Indicative Timeline

| Horizon | Focus |
|---------|--------|
| Week 1 | Phases 0–2 |
| Week 2 | Phases 3–4 |
| Week 3 | Phase 5 |
| Week 4 | Phases 6–7 |
| Week 5 | Phases 8–9 |

Adjust for team size and weekly capacity.

---

## Critical Path

`Phase 0 → 1 → 4` must exist before reliable CMS work. **Blog** follows **admin shell**. **Contact** can start after Phase 1 in parallel with Home polish if capacity allows.

---

## Early Decisions (Unblock Rework)

1. Blog body: **sanitized HTML** vs **Markdown** (drives editor and XSS strategy).
2. Photo gallery: **albums** vs **single grid**.
3. Production **SMTP** provider and credentials handling.
4. Spam strategy: **honeypot + rate limit** vs **reCAPTCHA** (or both).

---

## Document Control

| Version | Date       | Notes |
|---------|------------|--------|
| 1.0     | 2026-05-03 | Initial roadmap |

---

*End of development roadmap.*
