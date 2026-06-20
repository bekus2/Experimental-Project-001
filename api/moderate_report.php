<?php
/**
 * Проект: ВайбКод
 * Файл: api/moderate_report.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Updates moderation report status.
 * RU: Обновляет статус жалобы в модераторской очереди.
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
    json_error('Недостаточно прав для обработки жалоб.', 403);
}

$reportId = (int)($_POST['report_id'] ?? 0);
$status = (string)($_POST['status'] ?? '');
if (!in_array($status, ['reviewed', 'dismissed'], true)) {
    json_error('Некорректный статус жалобы.');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM post_reports WHERE id = ?');
$stmt->execute([$reportId]);
if (!$stmt->fetch()) {
    json_error('Жалоба не найдена.', 404);
}

$stmt = $pdo->prepare('UPDATE post_reports SET status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE id = ?');
$stmt->execute([$status, (int)$me['id'], $reportId]);

json_response(['ok' => true, 'message' => 'Жалоба обновлена.']);
