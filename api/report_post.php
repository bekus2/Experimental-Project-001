<?php
/**
 * Проект: ВайбКод
 * Файл: api/report_post.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Creates a moderation report for a post.
 * RU: Создает жалобу на сообщение для модерации.
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
$reason = trim((string)($_POST['reason'] ?? ''));

if (mb_strlen($reason) < 5 || mb_strlen($reason) > 255) {
    json_error('Укажите причину жалобы от 5 до 255 символов.');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT p.id, p.user_id, p.is_deleted, t.id AS topic_id, t.title FROM posts p JOIN topics t ON t.id = p.topic_id WHERE p.id = ?');
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post || (int)$post['is_deleted'] === 1) {
    json_error('Сообщение не найдено.', 404);
}
if ((int)$post['user_id'] === (int)$me['id']) {
    json_error('Нельзя пожаловаться на собственное сообщение.');
}

try {
    $stmt = $pdo->prepare('INSERT INTO post_reports (post_id, reporter_id, reason) VALUES (?, ?, ?)');
    $stmt->execute([$postId, (int)$me['id'], $reason]);
} catch (PDOException $e) {
    json_error('Вы уже отправили жалобу на это сообщение.');
}

$mods = $pdo->query("SELECT id FROM users WHERE role IN ('admin','moderator')")->fetchAll();
foreach ($mods as $mod) {
    create_notification(
        (int)$mod['id'],
        (int)$post['topic_id'],
        $postId,
        'report',
        'Новая жалоба в теме: ' . $post['title'],
        (int)$me['id']
    );
}

json_response(['ok' => true, 'message' => 'Жалоба отправлена модераторам.']);
