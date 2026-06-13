<?php
/**
 * Главная страница форума: hero, разделы, свежие темы.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pdo = db();

// Статистика
$stats = [
    'topics' => (int)$pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn(),
    'posts'  => (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'users'  => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

// Категории + счётчики
$categories = $pdo->query('
    SELECT c.*,
        (SELECT COUNT(*) FROM topics t WHERE t.category_id = c.id) AS topic_count,
        (SELECT COUNT(*) FROM posts p JOIN topics t ON t.id = p.topic_id WHERE t.category_id = c.id) AS post_count
    FROM categories c
    ORDER BY c.position ASC
')->fetchAll();

// Последние активные темы
$recent = $pdo->query('
    SELECT t.id, t.title, t.is_pinned, t.is_locked, t.views, t.last_post_at,
           c.title AS cat_title, c.slug AS cat_slug, c.accent AS cat_accent,
           u.username, u.avatar_color,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id) - 1 AS replies
    FROM topics t
    JOIN categories c ON c.id = t.category_id
    JOIN users u ON u.id = t.user_id
    ORDER BY t.is_pinned DESC, t.last_post_at DESC
    LIMIT 10
')->fetchAll();

$pageTitle = 'Форум';
$activeNav = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Лови <span class="grad">вайб</span>,<br>пиши <span class="grad">код</span>.</h1>
    <p>Сообщество вайбкодеров: делись потоком, проектами и плейлистами, спрашивай совета и находи своих. Никакого душного формализма — только код и хорошее настроение.</p>
    <div class="hero-actions">
        <?php if (is_logged_in()): ?>
            <a class="btn btn-primary btn-lg" href="<?= url('new-topic.php') ?>">✍️ Создать тему</a>
            <a class="btn btn-ghost btn-lg" href="#categories">Смотреть разделы</a>
        <?php else: ?>
            <a class="btn btn-primary btn-lg" href="<?= url('register.php') ?>">🚀 Присоединиться</a>
            <a class="btn btn-ghost btn-lg" href="#categories">Смотреть форум</a>
        <?php endif; ?>
    </div>
    <div class="hero-stats">
        <div class="stat"><b><?= number_format($stats['topics'], 0, '.', ' ') ?></b><span>тем</span></div>
        <div class="stat"><b><?= number_format($stats['posts'], 0, '.', ' ') ?></b><span>сообщений</span></div>
        <div class="stat"><b><?= number_format($stats['users'], 0, '.', ' ') ?></b><span>вайбкодеров</span></div>
    </div>
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
    <h2>Свежие обсуждения</h2>
    <a href="#categories">Все разделы →</a>
</div>

<?php if (!$recent): ?>
    <div class="card empty">
        <div class="ico">🌱</div>
        <p>Пока тишина. Будь первым, кто создаст тему!</p>
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
                        <?php if ($t['is_pinned']): ?><span class="tag tag-pin">📌 закреплено</span><?php endif; ?>
                        <?php if ($t['is_locked']): ?><span class="tag tag-lock">🔒</span><?php endif; ?>
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
