<?php

declare(strict_types=1);

/**
 * Visible pricing rows with nested feature lines for the public /pricing page.
 *
 * @return list<array<string, mixed>>
 */
function k2_pricing_plans_visible(): array
{
    try {
        $pdo = k2_db();
        $stmt = $pdo->query(
            'SELECT id, project_type, title, summary, price_display, price_note, demo_image_path, external_url, link_label, sort_order
             FROM pricing_plans
             WHERE is_visible = 1
             ORDER BY sort_order ASC, id ASC'
        );
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($plans) || $plans === []) {
            return [];
        }

        $ids = [];
        foreach ($plans as $p) {
            $ids[] = (int) ($p['id'] ?? 0);
        }
        $ids = array_values(array_filter($ids, static fn (int $x): bool => $x > 0));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $fStmt = $pdo->prepare(
            "SELECT plan_id, feature_text FROM pricing_plan_features WHERE plan_id IN ($placeholders) ORDER BY plan_id ASC, sort_order ASC, id ASC"
        );
        $fStmt->execute($ids);
        $featureRows = $fStmt->fetchAll(PDO::FETCH_ASSOC);
        $byPlan = [];
        if (is_array($featureRows)) {
            foreach ($featureRows as $fr) {
                $pid = (int) ($fr['plan_id'] ?? 0);
                if ($pid <= 0) {
                    continue;
                }
                if (!isset($byPlan[$pid])) {
                    $byPlan[$pid] = [];
                }
                $byPlan[$pid][] = (string) ($fr['feature_text'] ?? '');
            }
        }

        foreach ($plans as &$plan) {
            $pid = (int) ($plan['id'] ?? 0);
            $plan['features'] = $byPlan[$pid] ?? [];
        }
        unset($plan);

        return $plans;
    } catch (Throwable $e) {
        error_log('K2 pricing list: ' . $e->getMessage());

        return [];
    }
}

/**
 * @return list<string>
 */
function k2_pricing_parse_features_text(string $raw): array
{
    $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $out = [];
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') {
            continue;
        }
        $out[] = mb_substr($t, 0, 512);
        if (count($out) >= 40) {
            break;
        }
    }

    return $out;
}
