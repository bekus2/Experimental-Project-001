<?php
/**
 * Проект: ВайбКод
 * Файл: api/change_password.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-19
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Allows an authenticated user to change their password after verifying the current one.
 * RU: Позволяет авторизованному пользователю сменить пароль после проверки текущего пароля.
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

$currentPassword = (string)($_POST['current_password'] ?? '');
$newPassword = (string)($_POST['new_password'] ?? '');
$confirmPassword = (string)($_POST['confirm_password'] ?? '');

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    json_error('Заполните все поля смены пароля.');
}
if (mb_strlen($newPassword) < MIN_PASSWORD_LENGTH) {
    json_error('Новый пароль должен быть не короче ' . MIN_PASSWORD_LENGTH . ' символов.');
}
if ($newPassword !== $confirmPassword) {
    json_error('Подтверждение нового пароля не совпадает.');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
$stmt->execute([(int)$me['id']]);
$hash = (string)$stmt->fetchColumn();

if ($hash === '' || !password_verify($currentPassword, $hash)) {
    json_error('Текущий пароль указан неверно.', 403);
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->execute([$newHash, (int)$me['id']]);

json_response([
    'ok' => true,
    'message' => 'Пароль обновлен.',
]);
