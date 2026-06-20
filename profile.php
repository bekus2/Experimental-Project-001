<?php
/**
 * Проект: ВайбКод
 * Файл: profile.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Displays a public user profile with activity statistics and recent topics.
 * RU: Показывает публичный профиль пользователя со статистикой и последними темами.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$username = trim((string)($_GET['u'] ?? ''));
$pdo = db();

$stmt = $pdo->prepare('SELECT id, username, avatar_color, bio, role, created_at, last_seen_at FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    $pageTitle = 'Профиль не найден';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card empty"><div class="ico">👤</div><p>Такого пользователя нет.</p><a class="btn btn-primary" href="' . url('index.php') . '">На главную</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$me = current_user();
$uid = (int)$user['id'];
$topicCount = (int)$pdo->query("SELECT COUNT(*) FROM topics WHERE user_id = $uid")->fetchColumn();
$postCount  = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE user_id = $uid AND is_deleted = 0")->fetchColumn();
$likeCount  = (int)$pdo->query("SELECT COUNT(*) FROM post_likes pl JOIN posts p ON p.id = pl.post_id WHERE p.user_id = $uid")->fetchColumn();

$topics = $pdo->query("
    SELECT t.id, t.title, t.topic_type, t.tags, t.is_solved, t.last_post_at, c.title AS cat_title, c.slug AS cat_slug,
           (SELECT COUNT(*) FROM posts p WHERE p.topic_id = t.id AND p.is_deleted = 0) - 1 AS replies
    FROM topics t JOIN categories c ON c.id = t.category_id
    WHERE t.user_id = $uid
    ORDER BY t.last_post_at DESC LIMIT 10
")->fetchAll();

$roleLabel = match ($user['role']) {
    'admin' => 'администратор',
    'moderator' => 'модератор',
    default => 'участник',
};

$pageTitle = $user['username'];
require __DIR__ . '/includes/header.php';
?>

<div class="card profile-head">
    <span class="avatar avatar-lg" style="--clr: <?= e($user['avatar_color']) ?>"><?= e(avatar_initial($user['username'])) ?></span>
    <div class="pinfo">
        <h1><?= e($user['username']) ?> <span class="role-badge"><?= e($roleLabel) ?></span></h1>
        <p class="bio"><?= $user['bio'] !== '' ? e($user['bio']) : 'Этот вайбкодер пока без описания.' ?></p>
        <p class="bio" style="font-size:12px;color:var(--text-faint)">С нами с <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
        <p class="bio" style="font-size:12px;color:var(--text-faint)">
            <?= !empty($user['last_seen_at']) && strtotime($user['last_seen_at']) >= time() - 300 ? 'Сейчас онлайн' : 'Последняя активность: ' . e($user['last_seen_at'] ? time_ago($user['last_seen_at']) : 'давно') ?>
        </p>
        <?php if ($me && (int)$me['id'] === $uid): ?>
            <p class="profile-actions"><a class="btn btn-ghost btn-sm" href="<?= url('settings.php') ?>">Редактировать профиль</a></p>
        <?php endif; ?>
    </div>
    <div class="profile-stats">
        <div><b><?= $topicCount ?></b><span>тем</span></div>
        <div><b><?= $postCount ?></b><span>сообщений</span></div>
        <div><b><?= $likeCount ?></b><span>лайков</span></div>
    </div>
</div>

<div class="section-head"><h2>Темы пользователя</h2></div>

<?php if (!$topics): ?>
    <div class="card empty"><div class="ico">🗒️</div><p><?= e($user['username']) ?> ещё не создавал тем.</p></div>
<?php else: ?>
    <div class="topic-list">
        <?php foreach ($topics as $t): ?>
            <div class="topic-row">
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
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
