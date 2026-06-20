<?php
/**
 * Проект: ВайбКод
 * Файл: api/toggle_topic_preference.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Toggles per-user topic bookmarks and subscriptions.
 * RU: Переключает пользовательские закладки и подписки на темы.
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
$type = (string)($_POST['type'] ?? '');
$map = [
    'bookmark' => 'topic_bookmarks',
    'subscribe' => 'topic_subscriptions',
];

if ($topicId <= 0 || !isset($map[$type])) {
    json_error('Некорректный запрос.');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id, title FROM topics WHERE id = ?');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();
if (!$topic) {
    json_error('Тема не найдена.', 404);
}

$table = $map[$type];
$stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE topic_id = ? AND user_id = ?");
$stmt->execute([$topicId, (int)$me['id']]);
$exists = (bool)$stmt->fetchColumn();

if ($exists) {
    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE topic_id = ? AND user_id = ?");
    $stmt->execute([$topicId, (int)$me['id']]);
    $active = false;
} else {
    $stmt = $pdo->prepare("INSERT INTO {$table} (topic_id, user_id) VALUES (?, ?)");
    $stmt->execute([$topicId, (int)$me['id']]);
    $active = true;
}

$message = match ($type) {
    'bookmark' => $active ? 'Тема добавлена в закладки.' : 'Тема удалена из закладок.',
    'subscribe' => $active ? 'Вы подписались на тему.' : 'Подписка на тему отключена.',
};

json_response([
    'ok' => true,
    'active' => $active,
    'message' => $message,
]);
