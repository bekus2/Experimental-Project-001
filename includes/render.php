<?php
/**
 * Рендер HTML отдельного сообщения (поста).
 * Используется и на странице темы, и в AJAX-ответе create_post.php,
 * чтобы разметка была единой.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * @param array      $post  Поля: id, body, created_at, edited_at, username,
 *                          avatar_color, role, user_created_at, like_count, liked_by_me
 * @param array|null $me    Текущий пользователь
 */
function render_post_card(array $post, ?array $me): string
{
    $likeCount = (int)($post['like_count'] ?? 0);
    $liked     = !empty($post['liked_by_me']);
    $role      = $post['role'] ?? 'member';

    $roleLabel = match ($role) {
        'admin'     => 'админ',
        'moderator' => 'модератор',
        default     => 'участник',
    };

    ob_start();
    ?>
    <article class="post" id="post-<?= (int)$post['id'] ?>">
        <div class="post-aside">
            <span class="avatar" style="--clr: <?= e($post['avatar_color'] ?? '#7c5cff') ?>"><?= e(avatar_initial($post['username'])) ?></span>
            <div>
                <a class="pa-name" href="<?= url('profile.php?u=' . urlencode($post['username'])) ?>"><?= e($post['username']) ?></a>
                <div><span class="pa-role <?= e($role) ?>"><?= e($roleLabel) ?></span></div>
                <?php if (!empty($post['user_created_at'])): ?>
                    <div class="pa-joined">с нами с <?= date('m.Y', strtotime($post['user_created_at'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="post-body">
            <div class="post-meta">
                <span><?= e(time_ago($post['created_at'])) ?></span>
                <?php if (!empty($post['edited_at'])): ?>
                    <span>· изменено</span>
                <?php endif; ?>
            </div>
            <div class="post-content"><?= render_body($post['body']) ?></div>
            <div class="post-footer">
                <button class="like-btn <?= $liked ? 'liked' : '' ?>" data-post="<?= (int)$post['id'] ?>"
                        type="button" aria-pressed="<?= $liked ? 'true' : 'false' ?>">
                    <span class="heart">♥</span>
                    <span class="like-count"><?= $likeCount ?></span>
                </button>
            </div>
        </div>
    </article>
    <?php
    return (string)ob_get_clean();
}
