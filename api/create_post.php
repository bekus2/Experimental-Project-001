<?php
/**
 * AJAX: добавление ответа в тему. Возвращает готовый HTML поста.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/render.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Метод не поддерживается', 405);
}
csrf_guard();

$me = current_user();
if (!$me) {
    json_error('Нужно войти в аккаунт.', 401);
}

$topicId = (int)($_POST['topic_id'] ?? 0);
$body    = trim((string)($_POST['body'] ?? ''));

if (mb_strlen($body) < 2) {
    json_error('Сообщение слишком короткое.');
}
if (mb_strlen($body) > 20000) {
    json_error('Сообщение слишком длинное.');
}

$pdo = db();

$stmt = $pdo->prepare('SELECT id, is_locked FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();

if (!$topic) {
    json_error('Тема не найдена.', 404);
}
if ((int)$topic['is_locked'] === 1) {
    json_error('Тема закрыта для ответов.', 403);
}

try {
    $stmt = $pdo->prepare('INSERT INTO posts (topic_id, user_id, body) VALUES (?, ?, ?)');
    $stmt->execute([$topicId, $me['id'], $body]);
    $postId = (int)$pdo->lastInsertId();

    $pdo->prepare('UPDATE topics SET last_post_at = NOW() WHERE id = ?')->execute([$topicId]);
} catch (PDOException $e) {
    json_error('Не удалось отправить сообщение.', 500);
}

$post = [
    'id'              => $postId,
    'body'            => $body,
    'created_at'      => date('Y-m-d H:i:s'),
    'edited_at'       => null,
    'username'        => $me['username'],
    'avatar_color'    => $me['avatar_color'],
    'role'            => $me['role'],
    'user_created_at' => $me['created_at'],
    'like_count'      => 0,
    'liked_by_me'     => false,
];

json_response([
    'ok'   => true,
    'html' => render_post_card($post, $me),
]);
