<?php
/**
 * Установщик ВайбКод.
 *
 * Запуск из CLI:   php install.php
 * Или из браузера: открыть /install.php (после установки УДАЛИТЕ файл!)
 *
 * Делает:
 *   1. Создаёт БД и таблицы из sql/schema.sql
 *   2. Заполняет демо-пользователями (с настоящими хэшами паролей)
 *   3. Добавляет пару стартовых тем, чтобы форум не пустовал
 *
 * Демо-логины (пароль у всех: password123):
 *   neon_dev / lo_fi_lucy / promptsmith
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

$isCli = (PHP_SAPI === 'cli');
$nl    = $isCli ? "\n" : '<br>';

function out(string $msg, string $nl): void { echo $msg . $nl; flush(); }

if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

out('=== Установка ' . SITE_NAME . ' ===', $nl);

// --- 1. Подключаемся без выбора БД, создаём схему ---
try {
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    out('✗ Не удалось подключиться к MySQL: ' . $e->getMessage(), $nl);
    exit(1);
}

$schema = file_get_contents(__DIR__ . '/sql/schema.sql');
if ($schema === false) {
    out('✗ Не найден sql/schema.sql', $nl);
    exit(1);
}

try {
    $pdo->exec($schema);
    out('✓ Схема БД создана (база, таблицы, категории).', $nl);
} catch (PDOException $e) {
    out('✗ Ошибка выполнения schema.sql: ' . $e->getMessage(), $nl);
    exit(1);
}

// Переключаемся на нашу БД
$pdo->exec('USE `' . DB_NAME . '`');

// --- 2. Демо-пользователи ---
$demoPassword = 'password123';
$hash = password_hash($demoPassword, PASSWORD_DEFAULT);

$users = [
    ['neon_dev',   'neon@example.com',   '#7c5cff', 'Кодю под synthwave 🎧',     'admin'],
    ['lo_fi_lucy', 'lucy@example.com',   '#22d3ee', 'lo-fi beats & clean code',   'member'],
    ['promptsmith','prompt@example.com', '#f472b6', 'Шепчу нейросетям 🤖',        'moderator'],
];

$ins = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash, avatar_color, bio, role)
     VALUES (?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$ids = [];
foreach ($users as $u) {
    $ins->execute([$u[0], $u[1], $hash, $u[2], $u[3], $u[4]]);
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$u[0]]);
    $ids[$u[0]] = (int)$stmt->fetchColumn();
}
out('✓ Демо-пользователи готовы (пароль: ' . $demoPassword . ').', $nl);

// --- 3. Стартовые темы (только если форум пуст) ---
$topicCount = (int)$pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn();
if ($topicCount === 0) {
    $catId = fn(string $slug) => (int)$pdo->query("SELECT id FROM categories WHERE slug = '$slug'")->fetchColumn();

    $seedTopics = [
        ['vibe-coding', 'neon_dev', 'Какой ваш идеальный плейлист для глубокого кодинга?',
            "Собираю коллективный плейлист для состояния потока 🌊\n\nУ меня обычно играет synthwave и немного lo-fi. А что слушаете вы, когда залипаете в задачу на пару часов?\n\nКидайте ссылки и жанры!"],
        ['ai-tools', 'promptsmith', 'Делимся лучшими промптами для рефакторинга',
            "Заметил, что качество ответа сильно зависит от формулировки.\n\nВот мой шаблон:\n```\nОтрефактори этот код, сохранив поведение.\nОбъясни каждое изменение одной строкой.\n```\n\nА какие приёмы используете вы?"],
        ['showcase', 'lo_fi_lucy', 'Запилила минималистичный pomodoro-таймер на ванильном JS',
            "Без фреймворков, чистый **HTML/CSS/JS**, тёмная тема и приятные звуки.\n\nДелала за один вечер под lo-fi. Дайте фидбек по UX, если не лень 🙌"],
    ];

    $insT = $pdo->prepare('INSERT INTO topics (category_id, user_id, title, slug) VALUES (?, ?, ?, ?)');
    $insP = $pdo->prepare('INSERT INTO posts (topic_id, user_id, body) VALUES (?, ?, ?)');

    require_once __DIR__ . '/includes/functions.php';

    foreach ($seedTopics as $t) {
        $insT->execute([$catId($t[0]), $ids[$t[1]], $t[2], slugify($t[2])]);
        $tid = (int)$pdo->lastInsertId();
        $insP->execute([$tid, $ids[$t[1]], $t[3]]);
    }

    // Пара ответов для живости
    $firstTopic = (int)$pdo->query('SELECT id FROM topics ORDER BY id ASC LIMIT 1')->fetchColumn();
    $insP->execute([$firstTopic, $ids['lo_fi_lucy'], 'lo-fi бесконечный стрим — мой выбор. Иногда дождь + клавиши 🌧️']);
    $insP->execute([$firstTopic, $ids['promptsmith'], 'А я под dnb разгоняюсь, когда дедлайн горит 😅']);
    $pdo->prepare('UPDATE topics SET last_post_at = NOW() WHERE id = ?')->execute([$firstTopic]);

    out('✓ Добавлены стартовые темы.', $nl);
} else {
    out('• Темы уже есть — пропускаю наполнение.', $nl);
}

out('', $nl);
out('=== Готово! Откройте index.php ===', $nl);
out('!!! Удалите install.php после установки в продакшене.', $nl);
