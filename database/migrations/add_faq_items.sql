-- One-time migration for databases created before `faq_items` existed.
-- Run in phpMyAdmin or: mysql -u USER -p DATABASE < database/migrations/add_faq_items.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS faq_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question VARCHAR(512) NOT NULL,
    answer MEDIUMTEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faq_visible_sort (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default FAQs only when the table is empty (matches fresh `database/schema.sql`).
INSERT INTO faq_items (question, answer, sort_order, is_visible)
SELECT q, a, s, v
FROM (
    SELECT 'What kinds of projects do you take on?' AS q,
           '<p>We focus on web applications, marketing sites with structured content (blog, galleries, and landing pages), and integrations with email, payments, and internal tools. If it needs a maintainable PHP + MySQL foundation and a polished Bootstrap UI, we are a strong fit.</p>' AS a,
           0 AS s, 1 AS v
    UNION ALL SELECT 'How do you approach security?',
           '<p>Threat modeling starts at the foundation: prepared statements for SQL, CSRF protection on state-changing requests, secure cookies for admin sessions, outbound escaping for XSS, rate limiting on authentication surfaces, and sensible headers (CSP tuned for our asset pipeline).</p>',
           1, 1
    UNION ALL SELECT 'What is the typical engagement model?',
           '<p>Engagements usually start with a short discovery to align on scope, milestones, and success metrics. We prefer iterative delivery — ship a thin vertical slice early, then expand features and content with measurable checkpoints.</p>',
           2, 1
    UNION ALL SELECT 'Can we manage content without developers?',
           '<p>Yes — that is why this stack includes admin tooling for the blog, app gallery, photo gallery, and FAQs. You edit structured content in the browser while the public site stays fast and consistent with your brand tokens.</p>',
           3, 1
) AS seed
WHERE (SELECT COUNT(*) FROM faq_items) = 0;
