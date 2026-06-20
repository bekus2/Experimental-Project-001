<?php
/**
 * Проект: ВайбКод
 * Файл: api/edit_post.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Edits an existing post when the current user has permission.
 * RU: Редактирует существующее сообщение при наличии прав.
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
$body = trim((string)($_POST['body'] ?? ''));

if (mb_strlen($body) < 2) {
    json_error('Сообщение слишком короткое.');
}
if (mb_strlen($body) > 20000) {
    json_error('Сообщение слишком длинное.');
}

$stmt = db()->prepare('SELECT id, user_id, body, is_deleted FROM posts WHERE id = ?');
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    json_error('Сообщение не найдено.', 404);
}
if (!user_can_edit_post($me, $post)) {
    json_error('Недостаточно прав для редактирования.', 403);
}

$stmt = db()->prepare('UPDATE posts SET body = ?, edited_at = NOW() WHERE id = ?');
$stmt->execute([$body, $postId]);

json_response([
    'ok' => true,
    'message' => 'Сообщение обновлено.',
    'html' => render_body($body),
]);
