<?php
/**
 * AJAX: поиск по темам и сообщениям. Возвращает JSON.
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
        c.title AS category,
        (SELECT COUNT(*) FROM posts p2 WHERE p2.topic_id = t.id) - 1 AS replies,
        (
            SELECT p.body FROM posts p
            WHERE p.topic_id = t.id
            ORDER BY p.id ASC LIMIT 1
        ) AS first_body
    FROM topics t
    JOIN categories c ON c.id = t.category_id
    WHERE t.title LIKE :q1
       OR EXISTS (SELECT 1 FROM posts p3 WHERE p3.topic_id = t.id AND p3.body LIKE :q2)
    ORDER BY t.last_post_at DESC
    LIMIT 25
';

$stmt = db()->prepare($sql);
$stmt->execute([':q1' => $like, ':q2' => $like]);
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
