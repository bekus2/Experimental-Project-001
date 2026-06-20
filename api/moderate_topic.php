<?php
/**
 * Проект: ВайбКод
 * Файл: api/moderate_topic.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-19
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Handles moderator actions for pinning and locking forum topics.
 * RU: Обрабатывает модераторские действия для закрепления и закрытия тем форума.
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
if (!user_can_moderate($me)) {
    json_error('Недостаточно прав для модерации темы.', 403);
}

$topicId = (int)($_POST['topic_id'] ?? 0);
$action = trim((string)($_POST['action'] ?? ''));

$allowedActions = ['pin', 'unpin', 'lock', 'unlock'];
if ($topicId <= 0 || !in_array($action, $allowedActions, true)) {
    json_error('Некорректное действие модерации.');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id, is_pinned, is_locked FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();

if (!$topic) {
    json_error('Тема не найдена.', 404);
}

$field = in_array($action, ['pin', 'unpin'], true) ? 'is_pinned' : 'is_locked';
$value = in_array($action, ['pin', 'lock'], true) ? 1 : 0;

$stmt = $pdo->prepare("UPDATE topics SET {$field} = ? WHERE id = ?");
$stmt->execute([$value, $topicId]);

$stmt = $pdo->prepare('SELECT is_pinned, is_locked FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
$updated = $stmt->fetch();

$message = match ($action) {
    'pin' => 'Тема закреплена.',
    'unpin' => 'Тема откреплена.',
    'lock' => 'Тема закрыта для новых ответов.',
    'unlock' => 'Тема снова открыта для ответов.',
};

json_response([
    'ok' => true,
    'message' => $message,
    'is_pinned' => (int)$updated['is_pinned'] === 1,
    'is_locked' => (int)$updated['is_locked'] === 1,
]);
