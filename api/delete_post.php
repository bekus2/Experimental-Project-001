<?php
/**
 * Проект: ВайбКод
 * Файл: api/delete_post.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Soft-deletes a post while preserving the topic structure.
 * RU: Мягко удаляет сообщение, сохраняя структуру темы.
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
$stmt = db()->prepare('SELECT id, user_id, topic_id, is_deleted FROM posts WHERE id = ?');
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    json_error('Сообщение не найдено.', 404);
}
if (!user_can_delete_post($me, $post)) {
    json_error('Недостаточно прав для удаления.', 403);
}

$stmt = db()->prepare(
    'UPDATE posts SET is_deleted = 1, deleted_at = NOW(), deleted_by = ?, body = ? WHERE id = ?'
);
$stmt->execute([(int)$me['id'], '[deleted]', $postId]);

$pdo = db();
$stmt = $pdo->prepare('SELECT solved_post_id FROM topics WHERE id = ?');
$stmt->execute([(int)$post['topic_id']]);
if ((int)$stmt->fetchColumn() === $postId) {
    $stmt = $pdo->prepare('UPDATE topics SET is_solved = 0, solved_post_id = NULL WHERE id = ?');
    $stmt->execute([(int)$post['topic_id']]);
}

json_response(['ok' => true, 'message' => 'Сообщение удалено.']);
