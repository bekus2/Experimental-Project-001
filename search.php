<?php
/**
 * Проект: ВайбКод
 * Файл: search.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders the live search page for forum topics, tags, and posts.
 * RU: Рендерит страницу живого поиска по темам, тегам и сообщениям форума.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$q = trim((string)($_GET['q'] ?? ''));

$pageTitle = 'Поиск';
$activeNav = 'search';
require __DIR__ . '/includes/header.php';
?>

<div class="thread-head">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Форум</a> / Поиск</div>
    <h1>🔎 Поиск по форуму</h1>
    <p class="meta">Ищите по заголовкам тем и тексту сообщений — результаты подгружаются на лету.</p>
    <div class="field" style="margin-top:16px;max-width:520px">
        <input type="search" id="live-search" placeholder="Что ищем?" value="<?= e($q) ?>" autofocus>
    </div>
</div>

<div id="search-results">
    <div class="empty"><div class="ico">🔎</div><p>Начните вводить запрос…</p></div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
