<?php
/**
 * Проект: ВайбКод
 * Файл: install.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Creates the database schema and seed users/topics for local installation.
 * RU: Создает схему базы данных и стартовых пользователей/тем для локальной установки.
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
$defaultAdminPassword = '0123456789+Aa';
$demoPassword = 'password123';

$users = [
    ['beck_admin', 'bek0435@gmail.com', '#2563eb', 'Владелец и администратор форума.', 'admin', $defaultAdminPassword],
    ['neon_dev',   'neon@example.com',  '#7c5cff', 'Кодю под synthwave 🎧',           'admin', $demoPassword],
    ['lo_fi_lucy', 'lucy@example.com',  '#22d3ee', 'lo-fi beats & clean code',        'member', $demoPassword],
    ['promptsmith','prompt@example.com','#f472b6', 'Шепчу нейросетям 🤖',             'moderator', $demoPassword],
];

$ins = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash, avatar_color, bio, role)
     VALUES (?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$ids = [];
foreach ($users as $u) {
    $hash = password_hash($u[5], PASSWORD_DEFAULT);
    $ins->execute([$u[0], $u[1], $hash, $u[2], $u[3], $u[4]]);
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$u[0]]);
    $ids[$u[0]] = (int)$stmt->fetchColumn();
}
out('✓ Администратор готов: bek0435@gmail.com / ' . $defaultAdminPassword . '.', $nl);
out('✓ Демо-пользователи готовы (пароль: ' . $demoPassword . ').', $nl);

// --- 3. Стартовые темы (только если форум пуст) ---
$topicCount = (int)$pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn();
if ($topicCount === 0) {
    $catId = fn(string $slug) => (int)$pdo->query("SELECT id FROM categories WHERE slug = '$slug'")->fetchColumn();

    $seedTopics = [
        ['vibe-coding', 'neon_dev', 'Какой ваш идеальный плейлист для глубокого кодинга?', 'discussion', 'музыка,workflow,фокус',
            "Собираю коллективный плейлист для состояния потока.\n\nУ меня обычно играет synthwave и немного lo-fi. А что слушаете вы, когда залипаете в задачу на пару часов?\n\nКидайте ссылки и жанры!"],
        ['ai-tools', 'promptsmith', 'Делимся лучшими промптами для рефакторинга', 'guide', 'ai,промпты,рефакторинг',
            "Заметил, что качество ответа сильно зависит от формулировки.\n\nВот мой шаблон:\n```\nОтрефактори этот код, сохранив поведение.\nОбъясни каждое изменение одной строкой.\n```\n\nА какие приемы используете вы?"],
        ['showcase', 'lo_fi_lucy', 'Запилила минималистичный pomodoro-таймер на ванильном JS', 'showcase', 'javascript,showcase,ux',
            "Без фреймворков, чистый **HTML/CSS/JS**, светлая тема и приятные звуки.\n\nДелала за один вечер под lo-fi. Дайте фидбек по UX."],
        ['ai-tools', 'beck_admin', 'Как правильно защитить AJAX-действия на форуме?', 'question', 'security,csrf,php',
            "Нужен короткий чеклист для действий вроде лайков, жалоб и редактирования постов.\n\nПока думаю про CSRF, проверку сессии, роли и серверную валидацию. Что еще добавить?"],
    ];

    $insT = $pdo->prepare('INSERT INTO topics (category_id, user_id, title, slug, topic_type, tags) VALUES (?, ?, ?, ?, ?, ?)');
    $insP = $pdo->prepare('INSERT INTO posts (topic_id, user_id, body) VALUES (?, ?, ?)');

    require_once __DIR__ . '/includes/functions.php';

    foreach ($seedTopics as $t) {
        $insT->execute([$catId($t[0]), $ids[$t[1]], $t[2], slugify($t[2]), $t[3], normalize_tags($t[4])]);
        $tid = (int)$pdo->lastInsertId();
        $insP->execute([$tid, $ids[$t[1]], $t[5]]);
        $pdo->prepare('INSERT IGNORE INTO topic_subscriptions (topic_id, user_id) VALUES (?, ?)')->execute([$tid, $ids[$t[1]]]);
    }

    // Пара ответов для живости
    $firstTopic = (int)$pdo->query('SELECT id FROM topics ORDER BY id ASC LIMIT 1')->fetchColumn();
    $insP->execute([$firstTopic, $ids['lo_fi_lucy'], 'lo-fi бесконечный стрим — мой выбор. Иногда дождь + клавиши 🌧️']);
    $insP->execute([$firstTopic, $ids['promptsmith'], 'А я под dnb разгоняюсь, когда дедлайн горит 😅']);
    $pdo->prepare('UPDATE topics SET last_post_at = NOW() WHERE id = ?')->execute([$firstTopic]);

    $questionTopic = (int)$pdo->query("SELECT id FROM topics WHERE topic_type = 'question' ORDER BY id ASC LIMIT 1")->fetchColumn();
    if ($questionTopic > 0) {
        $insP->execute([
            $questionTopic,
            $ids['promptsmith'],
            'Минимальный набор: CSRF для каждого POST, проверка роли на сервере, prepared statements, ограничение длины ввода и безопасный HTML-вывод через escaping.'
        ]);
        $solutionPostId = (int)$pdo->lastInsertId();
        $pdo->prepare('UPDATE topics SET is_solved = 1, solved_post_id = ?, last_post_at = NOW() WHERE id = ?')
            ->execute([$solutionPostId, $questionTopic]);
        $pdo->prepare('INSERT IGNORE INTO topic_subscriptions (topic_id, user_id) VALUES (?, ?), (?, ?)')
            ->execute([$questionTopic, $ids['beck_admin'], $questionTopic, $ids['promptsmith']]);
    }

    out('✓ Добавлены стартовые темы.', $nl);
} else {
    out('• Темы уже есть — пропускаю наполнение.', $nl);
}

out('', $nl);
out('=== Готово! Откройте index.php ===', $nl);
out('!!! Удалите install.php после установки в продакшене.', $nl);
