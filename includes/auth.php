<?php
/**
 * Сессии, аутентификация, CSRF.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Текущий авторизованный пользователь (или null).
 */
function current_user(): ?array
{
    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }
    $cached = true;

    if (empty($_SESSION['user_id'])) {
        return $user = null;
    }

    $stmt = db()->prepare('SELECT id, username, email, avatar_color, bio, role, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();

    return $user = ($row ?: null);
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login_redirect(): void
{
    if (!is_logged_in()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// --- CSRF ---------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function csrf_verify(?string $token): bool
{
    return !empty($_SESSION[CSRF_TOKEN_KEY])
        && is_string($token)
        && hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
}

/**
 * Проверка CSRF для API. Завершает выполнение при ошибке.
 */
function csrf_guard(): void
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!csrf_verify($token)) {
        json_error('Неверный CSRF-токен. Обновите страницу.', 419);
    }
}
