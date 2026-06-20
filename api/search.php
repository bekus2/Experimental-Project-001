<?php
/**
 * Проект: ВайбКод
 * Файл: api/search.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Searches topics, tags, and visible posts, returning JSON for live search.
 * RU: Ищет по темам, тегам и видимым постам, возвращая JSON для живого поиска.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$q = trim((string)($_GET['q'] ?? ''));

if (mb_strlen($q) < 2) {
    json_response(['ok' => true, 'results' => []]);
}

$like = '%' . $q . '%';

$sql = '
    SELECT
        t.id,
        t.title,
        t.tags,
        c.title AS category,
        (SELECT COUNT(*) FROM posts p2 WHERE p2.topic_id = t.id AND p2.is_deleted = 0) - 1 AS replies,
        (
            SELECT p.body FROM posts p
            WHERE p.topic_id = t.id AND p.is_deleted = 0
            ORDER BY p.id ASC LIMIT 1
        ) AS first_body
    FROM topics t
    JOIN categories c ON c.id = t.category_id
    WHERE t.title LIKE :q1
       OR t.tags LIKE :q2
       OR EXISTS (SELECT 1 FROM posts p3 WHERE p3.topic_id = t.id AND p3.is_deleted = 0 AND p3.body LIKE :q3)
    ORDER BY t.last_post_at DESC
    LIMIT 25
';

$stmt = db()->prepare($sql);
$stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
$rows = $stmt->fetchAll();

$results = array_map(function ($r) {
    $snippet = trim(mb_substr(strip_tags((string)$r['first_body']), 0, 140));
    return [
        'id'       => (int)$r['id'],
        'title'    => e($r['title']),
        'category' => e($r['category']),
        'replies'  => max(0, (int)$r['replies']),
        'snippet'  => e($snippet) . (mb_strlen((string)$r['first_body']) > 140 ? '…' : ''),
    ];
}, $rows);

json_response(['ok' => true, 'results' => $results]);
