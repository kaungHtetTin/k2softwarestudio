<?php

declare(strict_types=1);

$pageTitle = 'Privacy Policy';
$metaDescription = 'How K2 collects, uses, and protects personal information when you use our website and services.';
$lastUpdated = '3 May 2026';

ob_start();
?>
<div class="container py-5">
    <header class="mb-5">
        <p class="text-uppercase small text-muted mb-2 letter-spacing">Legal</p>
        <h1 class="display-6 fw-bold text-dark mb-2">Privacy Policy</h1>
        <p class="text-muted small mb-0">Last updated: <?= k2_e($lastUpdated) ?></p>
    </header>

    <div class="k2-blog-body col-lg-10 mx-auto legal-prose">
        <p>
            This Privacy Policy describes how <strong>K2</strong> (“we”, “us”, or “our”) collects, uses, stores, and protects personal information
            when you visit our website, use our contact or enquiry forms, or otherwise interact with us online in connection with our
            software development and related professional services (collectively, the “<strong>Services</strong>”).
        </p>
        <p>
            By using our website or submitting information to us, you acknowledge that you have read this Privacy Policy.
            If you do not agree, please do not use our Services.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">1. Who we are</h2>
        <p>
            K2 operates this website and related communications in connection with software engineering, consulting, and delivery services.
            For data protection purposes, we act as the controller of personal information described in this policy when we determine how and why it is processed.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">2. Information we collect</h2>
        <p>We may collect the following categories of information, depending on how you interact with us:</p>
        <ul>
            <li>
                <strong>Information you provide directly</strong> — for example, name, email address, phone number, company name, and the content of messages you send
                through contact forms, email, or similar channels.
            </li>
            <li>
                <strong>Technical and usage data</strong> — such as IP address, browser type, device type, general location derived from IP,
                pages viewed, and timestamps. This is typically collected automatically through server logs, analytics tools (if enabled),
                and similar technologies.
            </li>
            <li>
                <strong>Cookies and similar technologies</strong> — our website may use cookies or local storage that are strictly necessary for security,
                session management, or basic functionality (for example, remembering preferences). Where we use non-essential cookies,
                we will seek your consent where required by law.
            </li>
        </ul>

        <h2 class="h4 fw-bold mt-5 mb-3">3. How we use your information</h2>
        <p>We use personal information for purposes such as:</p>
        <ul>
            <li>responding to enquiries and providing information about our Services;</li>
            <li>operating, securing, and improving our website and infrastructure;</li>
            <li>sending operational communications related to your enquiry or our relationship (where appropriate);</li>
            <li>complying with legal obligations and defending our legal rights;</li>
            <li>detecting, preventing, and addressing fraud, abuse, or security incidents.</li>
        </ul>
        <p>
            We do not sell your personal information. We do not use your information for automated decision-making that produces legal or similarly significant effects.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">4. Legal bases (where applicable)</h2>
        <p>
            If the EU or UK General Data Protection Regulation (GDPR) or similar laws apply, we rely on one or more of the following legal bases:
            our <strong>legitimate interests</strong> in operating and promoting our business (balanced against your rights);
            <strong>performance of a contract</strong> or steps prior to entering a contract;
            <strong>compliance with legal obligations</strong>; and, where required, your <strong>consent</strong> (for example, certain cookies or marketing communications).
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">5. Disclosure of information</h2>
        <p>We may share personal information with:</p>
        <ul>
            <li>
                <strong>Service providers</strong> who assist us with hosting, email delivery, analytics, security, or IT operations,
                subject to appropriate contractual safeguards where required.
            </li>
            <li>
                <strong>Professional advisers</strong> (such as lawyers or accountants) where necessary.
            </li>
            <li>
                <strong>Authorities</strong> when required by law, regulation, court order, or governmental request, or to protect the rights, property, or safety of K2, our clients, or others.
            </li>
        </ul>
        <p>
            If K2 is involved in a merger, acquisition, or asset sale, personal information may be transferred as part of that transaction,
            subject to equivalent protections where required by law.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">6. International transfers</h2>
        <p>
            Your information may be processed in countries other than your country of residence.
            Where we transfer personal data from the UK, EEA, or Switzerland to countries not regarded as providing adequate protection,
            we implement appropriate safeguards (such as standard contractual clauses) where required by applicable law.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">7. Retention</h2>
        <p>
            We retain personal information only for as long as necessary for the purposes described in this Policy,
            unless a longer period is required or permitted by law (for example, tax, accounting, or dispute resolution).
            Retention periods depend on the nature of the data and our relationship with you.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">8. Security</h2>
        <p>
            We implement technical and organisational measures designed to protect personal information against unauthorised access, alteration, disclosure, or destruction.
            No method of transmission over the Internet or electronic storage is completely secure; we cannot guarantee absolute security.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">9. Your rights</h2>
        <p>
            Depending on where you live, you may have rights to access, rectify, erase, restrict, or object to certain processing of your personal information,
            or to request data portability. You may also have the right to lodge a complaint with a supervisory authority.
        </p>
        <p>
            To exercise applicable rights, please contact us using the details on our <a href="<?= k2_e(k2_url('/contact')) ?>">Contact</a> page.
            We may need to verify your identity before responding.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">10. Children</h2>
        <p>
            Our Services are not directed at individuals under 16 (or the minimum age required in your jurisdiction).
            We do not knowingly collect personal information from children. If you believe we have collected such information, please contact us and we will take appropriate steps to delete it.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">11. Third-party websites</h2>
        <p>
            Our website may contain links to third-party sites. We are not responsible for the privacy practices of those sites.
            We encourage you to read their privacy policies before providing any personal information.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">12. Changes to this Policy</h2>
        <p>
            We may update this Privacy Policy from time to time. The “Last updated” date at the top will be revised when changes are made.
            Material changes may be communicated through the website or other reasonable means where appropriate.
            Continued use of our Services after changes constitutes acceptance of the updated Policy, to the extent permitted by law.
        </p>

        <h2 class="h4 fw-bold mt-5 mb-3">13. Contact</h2>
        <p>
            For privacy-related questions or requests, please contact us via our <a href="<?= k2_e(k2_url('/contact')) ?>">Contact</a> page.
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();
require K2_ROOT . '/templates/layout.php';
