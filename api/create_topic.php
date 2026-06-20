<?php
/**
 * Проект: ВайбКод
 * Файл: api/create_topic.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Creates a topic with type, tags, first post, and initial author subscription.
 * RU: Создает тему с типом, тегами, первым сообщением и стартовой подпиской автора.
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

$categoryId = (int)($_POST['category_id'] ?? 0);
$title      = trim((string)($_POST['title'] ?? ''));
$body       = trim((string)($_POST['body'] ?? ''));
$topicType  = (string)($_POST['topic_type'] ?? 'discussion');
$tags       = normalize_tags((string)($_POST['tags'] ?? ''));

if (!array_key_exists($topicType, topic_type_options())) {
    $topicType = 'discussion';
}

if (mb_strlen($title) < 5 || mb_strlen($title) > 160) {
    json_error('Заголовок должен быть от 5 до 160 символов.');
}
if (mb_strlen($body) < 10) {
    json_error('Сообщение слишком короткое (минимум 10 символов).');
}

$pdo = db();

$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
$stmt->execute([$categoryId]);
if (!$stmt->fetch()) {
    json_error('Выберите существующий раздел.');
}

$slug = slugify($title);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO topics (category_id, user_id, title, slug, topic_type, tags) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$categoryId, $me['id'], $title, $slug, $topicType, $tags]);
    $topicId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO posts (topic_id, user_id, body) VALUES (?, ?, ?)'
    );
    $stmt->execute([$topicId, $me['id'], $body]);

    $stmt = $pdo->prepare('INSERT INTO topic_subscriptions (topic_id, user_id) VALUES (?, ?)');
    $stmt->execute([$topicId, $me['id']]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    json_error('Не удалось создать тему. Попробуйте позже.', 500);
}

json_response([
    'ok'       => true,
    'redirect' => url('topic.php?id=' . $topicId),
]);
