<?php
/**
 * Проект: ВайбКод
 * Файл: api/like.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Toggles a like on a visible post for the authenticated user.
 * RU: Переключает лайк на видимом сообщении для авторизованного пользователя.
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
$stmt = $pdo->prepare('SELECT id, is_deleted FROM posts WHERE id = ?');
$stmt->execute([$postId]);
$post = $stmt->fetch();
if (!$post || (int)$post['is_deleted'] === 1) {
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
