<?php

declare(strict_types=1);

/**
 * Phase 1 dev-only routes for CSRF + login throttle (DEVELOPMENT_ROADMAP exit criteria).
 */
function k2_dev_security_tests_route(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'csrf_ok') {
            if (!k2_csrf_verify()) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'CSRF validation failed';
                return;
            }
            k2_security_tests_redirect('csrf=ok');
            return;
        }

        if ($action === 'csrf_bad') {
            unset($_POST[k2_csrf_field_name()]);
            if (!k2_csrf_verify()) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'CSRF rejected (expected for this test).';
                return;
            }

            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Unexpected: CSRF should have failed.';
            return;
        }

        if ($action === 'throttle_fail') {
            $identity = (string) ($_POST['email'] ?? 'demo@example.com');
            if (!k2_login_allowed($identity)) {
                k2_security_tests_redirect('throttle=locked');
                return;
            }
            k2_login_register_failure($identity);
            k2_security_tests_redirect('throttle=hit');
            return;
        }

        if ($action === 'throttle_reset') {
            $identity = (string) ($_POST['email'] ?? 'demo@example.com');
            k2_login_clear_failures($identity);
            k2_security_tests_redirect('throttle=reset');
            return;
        }

        if ($action === 'db_ping') {
            if (!k2_csrf_verify()) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'CSRF validation failed';
                return;
            }
            try {
                $pdo = k2_db();
                $pdo->query('SELECT 1');
                k2_security_tests_redirect('db=ok');
            } catch (Throwable) {
                k2_security_tests_redirect('db=fail');
            }

            return;
        }
    }

    require K2_ROOT . '/templates/security-tests.php';
}

function k2_security_tests_redirect(string $query): void
{
    $target = k2_url('/security-tests') . '?' . $query;
    header('Location: ' . $target, true, 302);
}
