<?php
/**
 * Проект: ВайбКод
 * Файл: api/mark_notifications_read.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Marks notifications as read for the authenticated user.
 * RU: Отмечает уведомления авторизованного пользователя прочитанными.
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

$notificationId = (int)($_POST['notification_id'] ?? 0);

if ($notificationId > 0) {
    $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$notificationId, (int)$me['id']]);
} else {
    $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->execute([(int)$me['id']]);
}

json_response(['ok' => true, 'message' => 'Уведомления отмечены прочитанными.']);
