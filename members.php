<?php
/**
 * Проект: ВайбКод
 * Файл: members.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders the member directory with search, activity stats, and online status.
 * RU: Рендерит каталог участников с поиском, статистикой активности и онлайн-статусом.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$q = trim((string)($_GET['q'] ?? ''));
$sort = (string)($_GET['sort'] ?? 'active');
$allowedSorts = ['active', 'topics', 'likes', 'newest'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'active';
}

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE u.username LIKE ? OR u.bio LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}

$orderSql = match ($sort) {
    'topics' => 'topic_count DESC, post_count DESC',
    'likes' => 'like_count DESC, post_count DESC',
    'newest' => 'u.created_at DESC',
    default => 'post_count DESC, topic_count DESC, u.last_seen_at DESC',
};

$stmt = db()->prepare("
    SELECT u.id, u.username, u.avatar_color, u.bio, u.role, u.created_at, u.last_seen_at,
           (SELECT COUNT(*) FROM topics t WHERE t.user_id = u.id) AS topic_count,
           (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.is_deleted = 0) AS post_count,
           (SELECT COUNT(*) FROM post_likes pl JOIN posts p2 ON p2.id = pl.post_id WHERE p2.user_id = u.id) AS like_count
    FROM users u
    {$where}
    ORDER BY {$orderSql}
    LIMIT 80
");
$stmt->execute($params);
$members = $stmt->fetchAll();

$pageTitle = 'Участники';
$activeNav = 'members';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
    <h1>Участники</h1>
</div>

<form class="filter-bar" action="<?= url('members.php') ?>" method="get">
    <label>
        <span>Поиск</span>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Имя или описание">
    </label>
    <label>
        <span>Сортировка</span>
        <select name="sort">
            <option value="active" <?= $sort === 'active' ? 'selected' : '' ?>>По активности</option>
            <option value="topics" <?= $sort === 'topics' ? 'selected' : '' ?>>По темам</option>
            <option value="likes" <?= $sort === 'likes' ? 'selected' : '' ?>>По лайкам</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Новые</option>
        </select>
    </label>
    <button class="btn btn-primary btn-sm" type="submit">Показать</button>
</form>

<div class="member-grid">
    <?php foreach ($members as $member): ?>
        <?php $online = !empty($member['last_seen_at']) && strtotime($member['last_seen_at']) >= time() - 300; ?>
        <a class="member-card" href="<?= url('profile.php?u=' . urlencode($member['username'])) ?>">
            <span class="avatar avatar-lg" style="--clr: <?= e($member['avatar_color']) ?>"><?= e(avatar_initial($member['username'])) ?></span>
            <span class="member-card-name"><?= e($member['username']) ?></span>
            <span class="member-card-role"><?= e($member['role']) ?> · <?= $online ? 'онлайн' : 'был ' . e($member['last_seen_at'] ? time_ago($member['last_seen_at']) : 'давно') ?></span>
            <span class="member-card-bio"><?= e($member['bio'] !== '' ? excerpt($member['bio'], 96) : 'Описание пока не заполнено.') ?></span>
            <span class="member-card-stats">
                <b><?= (int)$member['topic_count'] ?></b> тем
                <b><?= (int)$member['post_count'] ?></b> постов
                <b><?= (int)$member['like_count'] ?></b> лайков
            </span>
        </a>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
