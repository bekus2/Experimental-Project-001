<?php
/**
 * Проект: ВайбКод
 * Файл: index.php
 * Автор: Beck Sarbassov
 * Версия: 1.1.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-19
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders the forum homepage with categories, filters, insights, and topic listings.
 * RU: Рендерит главную страницу форума с разделами, фильтрами, аналитикой и списком тем.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pdo = db();

$sortOptions = [
    'recent' => 'Сначала активные',
    'popular' => 'Популярные',
    'unanswered' => 'Без ответов',
];
$sort = (string)($_GET['sort'] ?? 'recent');
if (!array_key_exists($sort, $sortOptions)) {
    $sort = 'recent';
}

$selectedCategory = max(0, (int)($_GET['cat'] ?? 0));

// EN: High-level counters for the forum dashboard.
// RU: Основные счетчики для панели активности форума.
$stats = [
    'topics' => (int)$pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn(),
    'posts'  => (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'users'  => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

$insights = [
    'active_today' => (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetchColumn(),
    'unanswered' => (int)$pdo->query('SELECT COUNT(*) FROM topics t WHERE (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) = 1')->fetchColumn(),
    'locked' => (int)$pdo->query('SELECT COUNT(*) FROM topics WHERE is_locked = 1')->fetchColumn(),
];

$categories = $pdo->query('
    SELECT c.*,
        (SELECT COUNT(*) FROM topics t WHERE t.category_id = c.id) AS topic_count,
        (SELECT COUNT(*) FROM posts p JOIN topics t ON t.id = p.topic_id WHERE t.category_id = c.id) AS post_count
    FROM categories c
    ORDER BY c.position ASC
')->fetchAll();

$categoryIds = array_map(static fn(array $category): int => (int)$category['id'], $categories);
if ($selectedCategory > 0 && !in_array($selectedCategory, $categoryIds, true)) {
    $selectedCategory = 0;
}

$where = [];
$params = [];
if ($selectedCategory > 0) {
    $where[] = 't.category_id = ?';
    $params[] = $selectedCategory;
}
if ($sort === 'unanswered') {
    $where[] = '(SELECT COUNT(*) FROM posts p0 WHERE p0.topic_id = t.id) = 1';
}

$orderSql = match ($sort) {
    'popular' => 't.views DESC, replies DESC, t.last_post_at DESC',
    'unanswered' => 't.last_post_at DESC',
    default => 't.is_pinned DESC, t.last_post_at DESC',
};

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $pdo->prepare("
    SELECT t.id, t.title, t.is_pinned, t.is_locked, t.views, t.last_post_at,
           c.title AS cat_title, c.slug AS cat_slug, c.accent AS cat_accent,
           u.username, u.avatar_color,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) - 1 AS replies
    FROM topics t
    JOIN categories c ON c.id = t.category_id
    JOIN users u ON u.id = t.user_id
    {$whereSql}
    ORDER BY {$orderSql}
    LIMIT 12
");
$stmt->execute($params);
$recent = $stmt->fetchAll();

$topMembers = $pdo->query('
    SELECT u.username, u.avatar_color, u.role,
           (SELECT COUNT(*) FROM topics t WHERE t.user_id = u.id) AS topic_count,
           (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS post_count,
           (SELECT COUNT(*) FROM post_likes pl JOIN posts p2 ON p2.id = pl.post_id WHERE p2.user_id = u.id) AS like_count
    FROM users u
    ORDER BY post_count DESC, topic_count DESC, like_count DESC
    LIMIT 5
')->fetchAll();

$pageTitle = 'Форум';
$activeNav = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Общий форум для идей, вопросов и проектов.</h1>
    <p>Создавайте темы, отвечайте без перезагрузки страницы, ищите обсуждения, редактируйте профиль и модерируйте важные ветки через понятные инструменты.</p>
    <div class="hero-actions">
        <?php if (is_logged_in()): ?>
            <a class="btn btn-primary btn-lg" href="<?= url('new-topic.php') ?>">Создать тему</a>
            <a class="btn btn-ghost btn-lg" href="<?= url('settings.php') ?>">Настроить профиль</a>
        <?php else: ?>
            <a class="btn btn-primary btn-lg" href="<?= url('register.php') ?>">Присоединиться</a>
            <a class="btn btn-ghost btn-lg" href="<?= url('login.php') ?>">Войти</a>
        <?php endif; ?>
    </div>
    <div class="hero-stats">
        <div class="stat"><b><?= number_format($stats['topics'], 0, '.', ' ') ?></b><span>тем</span></div>
        <div class="stat"><b><?= number_format($stats['posts'], 0, '.', ' ') ?></b><span>сообщений</span></div>
        <div class="stat"><b><?= number_format($stats['users'], 0, '.', ' ') ?></b><span>участников</span></div>
    </div>
</section>

<section class="forum-dashboard" aria-label="Активность форума">
    <div class="insight-grid">
        <article class="insight-card">
            <span class="insight-label">Сегодня</span>
            <strong><?= (int)$insights['active_today'] ?></strong>
            <p><?= plural((int)$insights['active_today'], 'новое сообщение', 'новых сообщения', 'новых сообщений') ?></p>
        </article>
        <article class="insight-card">
            <span class="insight-label">Нужны ответы</span>
            <strong><?= (int)$insights['unanswered'] ?></strong>
            <p><?= plural((int)$insights['unanswered'], 'тема без ответа', 'темы без ответа', 'тем без ответа') ?></p>
        </article>
        <article class="insight-card">
            <span class="insight-label">Модерация</span>
            <strong><?= (int)$insights['locked'] ?></strong>
            <p><?= plural((int)$insights['locked'], 'закрытая тема', 'закрытые темы', 'закрытых тем') ?></p>
        </article>
    </div>

    <aside class="member-panel">
        <div class="panel-head">
            <h2>Активные участники</h2>
            <a href="<?= url('search.php') ?>">Поиск</a>
        </div>
        <div class="member-list">
            <?php foreach ($topMembers as $member): ?>
                <a class="member-row" href="<?= url('profile.php?u=' . urlencode($member['username'])) ?>">
                    <span class="avatar avatar-sm" style="--clr: <?= e($member['avatar_color']) ?>"><?= e(avatar_initial($member['username'])) ?></span>
                    <span class="member-name"><?= e($member['username']) ?><small><?= e($member['role']) ?></small></span>
                    <span class="member-score"><?= (int)$member['post_count'] ?> пост.</span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>
</section>

<div class="section-head" id="categories">
    <h2>Разделы</h2>
</div>

<div class="cat-grid">
    <?php foreach ($categories as $cat): ?>
        <a class="cat-card" style="--accent: <?= e($cat['accent']) ?>"
           href="<?= url('category.php?slug=' . urlencode($cat['slug'])) ?>">
            <div class="cat-icon"><?= e($cat['icon']) ?></div>
            <div class="cat-body">
                <h3><?= e($cat['title']) ?></h3>
                <p><?= e($cat['description']) ?></p>
                <div class="cat-meta">
                    <span><b><?= (int)$cat['topic_count'] ?></b> <?= plural((int)$cat['topic_count'], 'тема', 'темы', 'тем') ?></span>
                    <span><b><?= (int)$cat['post_count'] ?></b> <?= plural((int)$cat['post_count'], 'сообщение', 'сообщения', 'сообщений') ?></span>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="section-head">
    <h2>Обсуждения</h2>
    <a href="#categories">Все разделы</a>
</div>

<form class="filter-bar" action="<?= url('index.php') ?>" method="get">
    <label>
        <span>Раздел</span>
        <select name="cat">
            <option value="0">Все разделы</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= $selectedCategory === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span>Порядок</span>
        <select name="sort">
            <?php foreach ($sortOptions as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit" class="btn btn-primary btn-sm">Показать</button>
    <a class="btn btn-ghost btn-sm" href="<?= url('index.php') ?>">Сбросить</a>
</form>

<?php if (!$recent): ?>
    <div class="card empty">
        <div class="ico">⌕</div>
        <p>По выбранным фильтрам тем пока нет.</p>
        <?php if (is_logged_in()): ?>
            <a class="btn btn-primary" href="<?= url('new-topic.php') ?>">Создать тему</a>
        <?php else: ?>
            <a class="btn btn-primary" href="<?= url('register.php') ?>">Присоединиться</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="topic-list">
        <?php foreach ($recent as $t): ?>
            <div class="topic-row">
                <span class="avatar" style="--clr: <?= e($t['avatar_color']) ?>"><?= e(avatar_initial($t['username'])) ?></span>
                <div class="topic-main">
                    <p class="topic-title">
                        <?php if ($t['is_pinned']): ?><span class="tag tag-pin">закреплено</span><?php endif; ?>
                        <?php if ($t['is_locked']): ?><span class="tag tag-lock">закрыто</span><?php endif; ?>
                        <a href="<?= url('topic.php?id=' . (int)$t['id']) ?>"><?= e($t['title']) ?></a>
                    </p>
                    <p class="topic-sub">
                        <a href="<?= url('category.php?slug=' . urlencode($t['cat_slug'])) ?>" class="cat-pill"><?= e($t['cat_title']) ?></a>
                        <span>от <a href="<?= url('profile.php?u=' . urlencode($t['username'])) ?>"><?= e($t['username']) ?></a></span>
                        <span><?= e(time_ago($t['last_post_at'])) ?></span>
                    </p>
                </div>
                <div class="topic-stats">
                    <div><span class="num"><?= max(0, (int)$t['replies']) ?></span><span class="lbl">ответов</span></div>
                    <div><span class="num"><?= (int)$t['views'] ?></span><span class="lbl">просм.</span></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
