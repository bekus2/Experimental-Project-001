<?php
/**
 * Проект: ВайбКод
 * Файл: api/mark_solved.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Marks a topic as solved using a selected post, or clears the solved state.
 * RU: Отмечает тему решенной выбранным сообщением или снимает статус решения.
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

$topicId = (int)($_POST['topic_id'] ?? 0);
$postId = (int)($_POST['post_id'] ?? 0);
$action = (string)($_POST['action'] ?? 'solve');

$pdo = db();
$stmt = $pdo->prepare('SELECT id, user_id, title FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();
if (!$topic) {
    json_error('Тема не найдена.', 404);
}
if (!user_can_moderate($me) && (int)$topic['user_id'] !== (int)$me['id']) {
    json_error('Только автор темы или модератор может менять статус решения.', 403);
}

if ($action === 'clear') {
    $stmt = $pdo->prepare('UPDATE topics SET is_solved = 0, solved_post_id = NULL WHERE id = ?');
    $stmt->execute([$topicId]);
    json_response(['ok' => true, 'message' => 'Статус решения снят.']);
}

$stmt = $pdo->prepare('SELECT id, topic_id, user_id, is_deleted FROM posts WHERE id = ? AND topic_id = ?');
$stmt->execute([$postId, $topicId]);
$post = $stmt->fetch();
if (!$post || (int)$post['is_deleted'] === 1) {
    json_error('Сообщение для решения не найдено.', 404);
}

$stmt = $pdo->prepare('UPDATE topics SET is_solved = 1, solved_post_id = ? WHERE id = ?');
$stmt->execute([$postId, $topicId]);

create_notification(
    (int)$post['user_id'],
    $topicId,
    $postId,
    'solution',
    'Ваш ответ отмечен как решение в теме: ' . $topic['title'],
    (int)$me['id']
);

json_response(['ok' => true, 'message' => 'Ответ отмечен как решение.']);
