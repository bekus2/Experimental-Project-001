<?php
/**
 * Страница темы: все сообщения + форма ответа (AJAX).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/render.php';

$topicId = (int)($_GET['id'] ?? 0);
$pdo     = db();
$me      = current_user();

$stmt = $pdo->prepare('
    SELECT t.*, c.title AS cat_title, c.slug AS cat_slug, c.icon AS cat_icon, c.accent AS cat_accent,
           u.username AS author
    FROM topics t
    JOIN categories c ON c.id = t.category_id
    JOIN users u ON u.id = t.user_id
    WHERE t.id = ?
');
$stmt->execute([$topicId]);
$topic = $stmt->fetch();

if (!$topic) {
    http_response_code(404);
    $pageTitle = 'Тема не найдена';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card empty"><div class="ico">🔍</div><p>Тема не найдена или удалена.</p><a class="btn btn-primary" href="' . url('index.php') . '">На главную</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// +1 просмотр (без точной защиты от накрутки — достаточно для демо)
$pdo->prepare('UPDATE topics SET views = views + 1 WHERE id = ?')->execute([$topicId]);

// Все посты темы с лайками
$likeMeJoin = $me
    ? 'EXISTS(SELECT 1 FROM post_likes pl2 WHERE pl2.post_id = p.id AND pl2.user_id = ' . (int)$me['id'] . ')'
    : '0';

$stmt = $pdo->prepare("
    SELECT p.id, p.body, p.created_at, p.edited_at,
           u.username, u.avatar_color, u.role, u.created_at AS user_created_at,
           (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,
           $likeMeJoin AS liked_by_me
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.topic_id = ?
    ORDER BY p.id ASC
");
$stmt->execute([$topicId]);
$posts = $stmt->fetchAll();

$pageTitle = $topic['title'];
require __DIR__ . '/includes/header.php';
?>

<div class="thread-head" style="border-color: <?= e($topic['cat_accent']) ?>55">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Форум</a> /
        <a href="<?= url('category.php?slug=' . urlencode($topic['cat_slug'])) ?>"><?= e($topic['cat_icon']) ?> <?= e($topic['cat_title']) ?></a>
    </div>
    <h1>
        <?php if ($topic['is_pinned']): ?><span class="tag tag-pin">📌 закреплено</span> <?php endif; ?>
        <?php if ($topic['is_locked']): ?><span class="tag tag-lock">🔒 закрыто</span> <?php endif; ?>
        <?= e($topic['title']) ?>
    </h1>
    <div class="meta">
        <span>автор <strong><?= e($topic['author']) ?></strong></span>
        <span><?= e(time_ago($topic['created_at'])) ?></span>
        <span><?= (int)$topic['views'] ?> <?= plural((int)$topic['views'], 'просмотр', 'просмотра', 'просмотров') ?></span>
        <span><?= count($posts) ?> <?= plural(count($posts), 'сообщение', 'сообщения', 'сообщений') ?></span>
    </div>
</div>

<div id="post-list">
    <?php foreach ($posts as $post): ?>
        <?= render_post_card($post, $me) ?>
    <?php endforeach; ?>
</div>

<?php if ($topic['is_locked']): ?>
    <div class="notice">🔒 Тема закрыта. Новые ответы добавить нельзя.</div>
<?php elseif ($me): ?>
    <div class="card reply-card">
        <h3>Ваш ответ</h3>
        <form id="reply-form">
            <input type="hidden" name="topic_id" value="<?= (int)$topic['id'] ?>">
            <div class="form-msg"></div>
            <div class="field">
                <div class="editor-toolbar">
                    <button type="button" data-wrap="**" title="Жирный">B</button>
                    <button type="button" data-wrap="*" title="Курсив">i</button>
                    <button type="button" data-wrap="`" title="Код">&lt;/&gt;</button>
                    <button type="button" data-wrap="code" title="Блок кода">{ }</button>
                </div>
                <textarea name="body" placeholder="Поделись мыслями… Поддерживается **жирный**, *курсив*, `код` и ```блоки```" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Отправить ответ</button>
        </form>
    </div>
<?php else: ?>
    <div class="card reply-card empty">
        <p>Чтобы ответить, нужно войти в аккаунт.</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <a class="btn btn-primary" href="<?= url('login.php') ?>">Войти</a>
            <a class="btn btn-ghost" href="<?= url('register.php') ?>">Регистрация</a>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
