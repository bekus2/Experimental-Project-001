<?php
/**
 * AJAX: переключение лайка на сообщении.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Метод не поддерживается', 405);
}
csrf_guard();

$me = current_user();
if (!$me) {
    json_error('Нужно войти в аккаунт.', 401);
}

$postId = (int)($_POST['post_id'] ?? 0);

$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
$stmt->execute([$postId]);
if (!$stmt->fetch()) {
    json_error('Сообщение не найдено.', 404);
}

// Уже лайкнул?
$stmt = $pdo->prepare('SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?');
$stmt->execute([$postId, $me['id']]);
$alreadyLiked = (bool)$stmt->fetchColumn();

if ($alreadyLiked) {
    $pdo->prepare('DELETE FROM post_likes WHERE post_id = ? AND user_id = ?')
        ->execute([$postId, $me['id']]);
    $liked = false;
} else {
    $pdo->prepare('INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)')
        ->execute([$postId, $me['id']]);
    $liked = true;
}

$count = (int)$pdo->query('SELECT COUNT(*) FROM post_likes WHERE post_id = ' . $postId)->fetchColumn();

json_response([
    'ok'    => true,
    'liked' => $liked,
    'count' => $count,
]);
