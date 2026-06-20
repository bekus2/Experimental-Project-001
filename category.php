<?php
/**
 * Проект: ВайбКод
 * Файл: category.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Displays one forum category with sorted topic lists and pagination.
 * RU: Показывает раздел форума со списком тем, сортировкой и пагинацией.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$pdo  = db();

$sortOptions = [
    'recent' => 'Сначала активные',
    'popular' => 'Популярные',
    'unanswered' => 'Без ответов',
    'solved' => 'Решенные',
];
$sort = (string)($_GET['sort'] ?? 'recent');
if (!array_key_exists($sort, $sortOptions)) {
    $sort = 'recent';
}

$typeOptions = ['all' => 'Все типы'] + topic_type_options();
$selectedType = (string)($_GET['type'] ?? 'all');
if (!array_key_exists($selectedType, $typeOptions)) {
    $selectedType = 'all';
}

$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Раздел не найден';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card empty"><div class="ico">🚧</div><p>Такого раздела нет.</p><a class="btn btn-primary" href="' . url('index.php') . '">На главную</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// EN: Pagination count follows the same filter as the visible topic list.
// RU: Счетчик пагинации использует тот же фильтр, что и видимый список тем.
$extraWhere = [];
$params = [(int)$category['id']];
if ($selectedType !== 'all') {
    $extraWhere[] = 't.topic_type = ?';
    $params[] = $selectedType;
}
if ($sort === 'unanswered') {
    $extraWhere[] = '(SELECT COUNT(*) FROM posts p0 WHERE p0.topic_id = t.id AND p0.is_deleted = 0) = 1';
}
if ($sort === 'solved') {
    $extraWhere[] = 't.is_solved = 1';
}
$extraSql = $extraWhere ? ' AND ' . implode(' AND ', $extraWhere) : '';
$totalStmt = $pdo->prepare('SELECT COUNT(*) FROM topics t WHERE t.category_id = ?' . $extraSql);
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / TOPICS_PER_PAGE));
$page  = max(1, min($pages, (int)($_GET['p'] ?? 1)));
$offset = ($page - 1) * TOPICS_PER_PAGE;

$orderSql = match ($sort) {
    'popular' => 't.views DESC, replies DESC, t.last_post_at DESC',
    'unanswered', 'solved' => 't.last_post_at DESC',
    default => 't.is_pinned DESC, t.last_post_at DESC',
};

$limit = (int)TOPICS_PER_PAGE;
$offset = (int)$offset;

$stmt = $pdo->prepare("
    SELECT t.id, t.title, t.topic_type, t.tags, t.is_solved, t.is_pinned, t.is_locked, t.views, t.created_at, t.last_post_at,
           u.username, u.avatar_color,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id AND p.is_deleted = 0) - 1 AS replies
    FROM topics t
    JOIN users u ON u.id = t.user_id
    WHERE t.category_id = ? {$extraSql}
    ORDER BY {$orderSql}
    LIMIT {$limit} OFFSET {$offset}
");
$stmt->execute($params);
$topics = $stmt->fetchAll();

$pageTitle = $category['title'];
require __DIR__ . '/includes/header.php';
?>

<div class="thread-head" style="border-color: <?= e($category['accent']) ?>55">
    <div class="breadcrumb"><a href="<?= url('index.php') ?>">Форум</a> / <?= e($category['title']) ?></div>
    <h1><?= e($category['icon']) ?> <?= e($category['title']) ?></h1>
    <p class="meta"><?= e($category['description']) ?></p>
    <div style="margin-top:18px">
        <?php if (is_logged_in()): ?>
            <a class="btn btn-primary btn-sm" href="<?= url('new-topic.php?cat=' . (int)$category['id']) ?>">+ Новая тема в разделе</a>
        <?php else: ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('login.php') ?>">Войдите, чтобы создать тему</a>
        <?php endif; ?>
    </div>
</div>

<form class="filter-bar" action="<?= url('category.php') ?>" method="get">
    <input type="hidden" name="slug" value="<?= e($slug) ?>">
    <label>
        <span>Тип темы</span>
        <select name="type">
            <?php foreach ($typeOptions as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $selectedType === $value ? 'selected' : '' ?>><?= e($label) ?></option>
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
    <a class="btn btn-ghost btn-sm" href="<?= url('category.php?slug=' . urlencode($slug)) ?>">Сбросить</a>
</form>

<?php if (!$topics): ?>
    <div class="card empty">
        <div class="ico"><?= e($category['icon']) ?></div>
        <p>По выбранной сортировке в разделе пока нет тем.</p>
        <?php if (is_logged_in()): ?>
            <a class="btn btn-primary" href="<?= url('new-topic.php?cat=' . (int)$category['id']) ?>">Создать первую</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="topic-list">
        <?php foreach ($topics as $t): ?>
            <div class="topic-row">
                <span class="avatar" style="--clr: <?= e($t['avatar_color']) ?>"><?= e(avatar_initial($t['username'])) ?></span>
                <div class="topic-main">
                    <p class="topic-title">
                        <?php if ($t['is_solved']): ?><span class="tag tag-solved">решено</span><?php endif; ?>
                        <?php if ($t['is_pinned']): ?><span class="tag tag-pin">закреплено</span><?php endif; ?>
                        <?php if ($t['is_locked']): ?><span class="tag tag-lock">закрыто</span><?php endif; ?>
                        <span class="topic-type-pill"><?= e(topic_type_label($t['topic_type'])) ?></span>
                        <a href="<?= url('topic.php?id=' . (int)$t['id']) ?>"><?= e($t['title']) ?></a>
                    </p>
                    <p class="topic-sub">
                        <span>от <a href="<?= url('profile.php?u=' . urlencode($t['username'])) ?>"><?= e($t['username']) ?></a></span>
                        <span>создано <?= e(time_ago($t['created_at'])) ?></span>
                        <span>активность <?= e(time_ago($t['last_post_at'])) ?></span>
                        <?php foreach (tags_to_array($t['tags']) as $tag): ?>
                            <a class="tag-link" href="<?= url('tag.php?t=' . urlencode($tag)) ?>">#<?= e($tag) ?></a>
                        <?php endforeach; ?>
                    </p>
                </div>
                <div class="topic-stats">
                    <div><span class="num"><?= max(0, (int)$t['replies']) ?></span><span class="lbl">ответов</span></div>
                    <div><span class="num"><?= (int)$t['views'] ?></span><span class="lbl">просм.</span></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Постранично">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= url('category.php?slug=' . urlencode($slug) . '&type=' . urlencode($selectedType) . '&sort=' . urlencode($sort) . '&p=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
