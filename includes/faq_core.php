<?php

declare(strict_types=1);

/**
 * FAQ entries shown on the home page (#faq).
 *
 * @return list<array<string, mixed>>
 */
function k2_faq_list_visible(): array
{
    try {
        $pdo = k2_db();
        $stmt = $pdo->query(
            'SELECT id, question, answer, sort_order
             FROM faq_items
             WHERE is_visible = 1
             ORDER BY sort_order ASC, id ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('K2 FAQ list: ' . $e->getMessage());

        return [];
    }
}
