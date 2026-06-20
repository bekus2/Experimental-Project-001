<?php
/**
 * Проект: ВайбКод
 * Файл: bookmarks.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Shows the authenticated user's bookmarked topics.
 * RU: Показывает темы, добавленные пользователем в закладки.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login_redirect();

$me = current_user();
$pdo = db();

$stmt = $pdo->prepare('
    SELECT t.id, t.title, t.topic_type, t.tags, t.is_pinned, t.is_locked, t.is_solved, t.views, t.last_post_at,
           c.title AS cat_title, c.slug AS cat_slug,
           u.username, u.avatar_color,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id AND p.is_deleted = 0) - 1 AS replies
    FROM topic_bookmarks b
    JOIN topics t ON t.id = b.topic_id
    JOIN categories c ON c.id = t.category_id
    JOIN users u ON u.id = t.user_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
');
$stmt->execute([(int)$me['id']]);
$topics = $stmt->fetchAll();

$pageTitle = 'Закладки';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
    <h1>Закладки</h1>
    <a href="<?= url('index.php') ?>">Все обсуждения</a>
</div>

<?php if (!$topics): ?>
    <div class="card empty">
        <div class="ico">★</div>
        <p>Здесь будут темы, которые вы добавили в закладки.</p>
    </div>
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
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
