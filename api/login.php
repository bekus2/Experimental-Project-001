<?php
/**
 * AJAX: вход пользователя.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Метод не поддерживается', 405);
}
csrf_guard();

$login    = trim((string)($_POST['login'] ?? ''));   // username или email
$password = (string)($_POST['password'] ?? '');

if ($login === '' || $password === '') {
    json_error('Заполните все поля.');
}

$stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_error('Неверный логин или пароль.', 401);
}

// При необходимости обновляем хэш до актуального алгоритма
if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $rehash = password_hash($password, PASSWORD_DEFAULT);
    $upd = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([$rehash, $user['id']]);
}

login_user((int)$user['id']);

json_response([
    'ok'       => true,
    'message'  => 'С возвращением! 👋',
    'redirect' => url('index.php'),
]);
