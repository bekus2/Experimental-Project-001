<?php
/**
 * Страница раздела: список тем с пагинацией.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$pdo  = db();

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

// Пагинация
$total = (int)$pdo->query('SELECT COUNT(*) FROM topics WHERE category_id = ' . (int)$category['id'])->fetchColumn();
$pages = max(1, (int)ceil($total / TOPICS_PER_PAGE));
$page  = max(1, min($pages, (int)($_GET['p'] ?? 1)));
$offset = ($page - 1) * TOPICS_PER_PAGE;

$stmt = $pdo->prepare('
    SELECT t.id, t.title, t.is_pinned, t.is_locked, t.views, t.created_at, t.last_post_at,
           u.username, u.avatar_color,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) - 1 AS replies
    FROM topics t
    JOIN users u ON u.id = t.user_id
    WHERE t.category_id = ?
    ORDER BY t.is_pinned DESC, t.last_post_at DESC
    LIMIT ' . (int)TOPICS_PER_PAGE . ' OFFSET ' . (int)$offset . '
');
$stmt->execute([$category['id']]);
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

<?php if (!$topics): ?>
    <div class="card empty">
        <div class="ico"><?= e($category['icon']) ?></div>
        <p>В этом разделе пока нет тем.</p>
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
                        <?php if ($t['is_pinned']): ?><span class="tag tag-pin">📌</span><?php endif; ?>
                        <?php if ($t['is_locked']): ?><span class="tag tag-lock">🔒</span><?php endif; ?>
                        <a href="<?= url('topic.php?id=' . (int)$t['id']) ?>"><?= e($t['title']) ?></a>
                    </p>
                    <p class="topic-sub">
                        <span>от <a href="<?= url('profile.php?u=' . urlencode($t['username'])) ?>"><?= e($t['username']) ?></a></span>
                        <span>создано <?= e(time_ago($t['created_at'])) ?></span>
                        <span>активность <?= e(time_ago($t['last_post_at'])) ?></span>
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
                    <a href="<?= url('category.php?slug=' . urlencode($slug) . '&p=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
