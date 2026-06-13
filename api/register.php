<?php
/**
 * AJAX: регистрация нового пользователя.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Метод не поддерживается', 405);
}
csrf_guard();

$username = trim((string)($_POST['username'] ?? ''));
$email    = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

// --- Валидация ---
if (mb_strlen($username) < MIN_USERNAME_LENGTH || mb_strlen($username) > MAX_USERNAME_LENGTH) {
    json_error('Имя пользователя должно быть от ' . MIN_USERNAME_LENGTH . ' до ' . MAX_USERNAME_LENGTH . ' символов.');
}
if (!preg_match('/^[A-Za-z0-9_\-\.]+$/', $username)) {
    json_error('Имя может содержать только латиницу, цифры, _ - .');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Введите корректный email.');
}
if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
    json_error('Пароль должен быть не короче ' . MIN_PASSWORD_LENGTH . ' символов.');
}

$pdo = db();

// Проверка занятости
$stmt = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
$stmt->execute([$username, $email]);
if ($existing = $stmt->fetch()) {
    if (mb_strtolower($existing['username']) === mb_strtolower($username)) {
        json_error('Это имя уже занято.');
    }
    json_error('Этот email уже зарегистрирован.');
}

// Случайный приятный цвет аватара
$palette = ['#7c5cff', '#22d3ee', '#f472b6', '#34d399', '#fbbf24', '#fb7185', '#a78bfa', '#38bdf8'];
$color   = $palette[random_int(0, count($palette) - 1)];

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, avatar_color) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$username, $email, $hash, $color]);
    $userId = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    json_error('Не удалось создать аккаунт. Попробуйте позже.', 500);
}

login_user($userId);

json_response([
    'ok'       => true,
    'message'  => 'Аккаунт создан! Добро пожаловать на борт 🌊',
    'redirect' => url('index.php'),
]);
