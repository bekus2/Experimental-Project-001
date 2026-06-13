<?php
/**
 * Глобальная конфигурация приложения.
 *
 * Значения можно переопределить через переменные окружения,
 * что удобно для деплоя и контейнеров.
 */

declare(strict_types=1);

// --- База данных ---------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'vibecode_forum');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// --- Сайт ---------------------------------------------------------------
define('SITE_NAME', 'ВайбКод');
define('SITE_TAGLINE', 'Форум для вайбкодеров');

// Базовый URL без завершающего слэша. Пусто = относительные пути.
define('BASE_URL', getenv('BASE_URL') ?: '');

// --- Безопасность -------------------------------------------------------
define('SESSION_NAME', 'vibecode_sid');
define('CSRF_TOKEN_KEY', 'csrf_token');

// Параметры регистрации
define('MIN_PASSWORD_LENGTH', 6);
define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 24);

// Пагинация
define('TOPICS_PER_PAGE', 15);
define('POSTS_PER_PAGE', 20);

// Режим отладки (показывать ошибки PHP)
define('DEBUG', (getenv('APP_DEBUG') === '1'));

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

date_default_timezone_set('UTC');
