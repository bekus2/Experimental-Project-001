<?php
/**
 * Проект: ВайбКод
 * Файл: api/update_profile.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-19
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Updates the authenticated user's public bio and avatar color.
 * RU: Обновляет публичное описание и цвет аватара авторизованного пользователя.
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

$bio = trim((string)($_POST['bio'] ?? ''));
$avatarColor = trim((string)($_POST['avatar_color'] ?? ''));

if (mb_strlen($bio) > 280) {
    json_error('Описание профиля не должно быть длиннее 280 символов.');
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $avatarColor)) {
    json_error('Выберите корректный HEX-цвет аватара.');
}

$stmt = db()->prepare('UPDATE users SET bio = ?, avatar_color = ? WHERE id = ?');
$stmt->execute([$bio, strtolower($avatarColor), (int)$me['id']]);

json_response([
    'ok' => true,
    'message' => 'Профиль обновлен.',
    'redirect' => url('profile.php?u=' . urlencode($me['username'])),
]);
