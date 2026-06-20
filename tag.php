<?php
/**
 * Проект: ВайбКод
 * Файл: tag.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Lists topics associated with a selected tag.
 * RU: Показывает темы, связанные с выбранным тегом.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$tag = normalize_tags((string)($_GET['t'] ?? ''));
$tag = tags_to_array($tag)[0] ?? '';

if ($tag === '') {
    http_response_code(404);
    $pageTitle = 'Тег не найден';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card empty"><div class="ico">#</div><p>Тег не найден.</p><a class="btn btn-primary" href="' . url('index.php') . '">На главную</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = db()->prepare("
    SELECT t.id, t.title, t.topic_type, t.tags, t.is_solved, t.views, t.last_post_at,
           c.title AS cat_title, c.slug AS cat_slug,
           u.username, u.avatar_color,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id AND p.is_deleted = 0) - 1 AS replies
    FROM topics t
    JOIN categories c ON c.id = t.category_id
    JOIN users u ON u.id = t.user_id
    WHERE CONCAT(',', t.tags, ',') LIKE ?
    ORDER BY t.last_post_at DESC
    LIMIT 80
");
$stmt->execute(['%,' . $tag . ',%']);
$topics = $stmt->fetchAll();

$pageTitle = '#' . $tag;
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
    <h1>#<?= e($tag) ?></h1>
    <a href="<?= url('index.php') ?>">Все обсуждения</a>
</div>

<?php if (!$topics): ?>
    <div class="card empty"><div class="ico">#</div><p>Тем с этим тегом пока нет.</p></div>
<?php else: ?>
    <div class="topic-list">
        <?php foreach ($topics as $t): ?>
            <div class="topic-row">
                <span class="avatar" style="--clr: <?= e($t['avatar_color']) ?>"><?= e(avatar_initial($t['username'])) ?></span>
                <div class="topic-main">
                    <p class="topic-title">
                        <?php if ($t['is_solved']): ?><span class="tag tag-solved">решено</span><?php endif; ?>
                        <span class="topic-type-pill"><?= e(topic_type_label((string)$t['topic_type'])) ?></span>
                        <a href="<?= url('topic.php?id=' . (int)$t['id']) ?>"><?= e($t['title']) ?></a>
                    </p>
                    <p class="topic-sub">
                        <a href="<?= url('category.php?slug=' . urlencode($t['cat_slug'])) ?>" class="cat-pill"><?= e($t['cat_title']) ?></a>
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
