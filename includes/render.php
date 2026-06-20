<?php
/**
 * Проект: ВайбКод
 * Файл: includes/render.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders reusable post HTML for topic pages and AJAX-created replies.
 * RU: Рендерит переиспользуемую HTML-разметку постов для страниц тем и AJAX-ответов.
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
    $isDeleted = !empty($post['is_deleted']);
    $canEdit   = user_can_edit_post($me, $post);
    $canDelete = user_can_delete_post($me, $post);
    $canSolve  = $me
        && !$isDeleted
        && !empty($post['topic_id'])
        && (user_can_moderate($me) || (int)($post['topic_author_id'] ?? 0) === (int)$me['id']);
    $isSolution = !empty($post['solved_post_id']) && (int)$post['solved_post_id'] === (int)$post['id'];

    $roleLabel = match ($role) {
        'admin'     => 'админ',
        'moderator' => 'модератор',
        default     => 'участник',
    };

    ob_start();
    ?>
    <article class="post <?= $isDeleted ? 'post-deleted' : '' ?> <?= $isSolution ? 'post-solution' : '' ?>" id="post-<?= (int)$post['id'] ?>">
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
                <?php if ($isSolution): ?>
                    <span class="solution-label">Решение</span>
                <?php endif; ?>
            </div>
            <?php if ($isDeleted): ?>
                <div class="post-content muted">Сообщение удалено.</div>
            <?php else: ?>
                <div class="post-content"><?= render_body($post['body']) ?></div>
                <?php if ($canEdit): ?>
                    <form class="inline-edit-form" data-post-id="<?= (int)$post['id'] ?>" hidden>
                        <div class="form-msg"></div>
                        <textarea name="body" required><?= e($post['body']) ?></textarea>
                        <div class="post-action-row">
                            <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                            <button type="button" class="btn btn-ghost btn-sm cancel-edit-btn">Отмена</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <div class="post-footer">
                <?php if (!$isDeleted): ?>
                    <button class="like-btn <?= $liked ? 'liked' : '' ?>" data-post="<?= (int)$post['id'] ?>"
                            type="button" aria-pressed="<?= $liked ? 'true' : 'false' ?>">
                        <span class="heart">♥</span>
                        <span class="like-count"><?= $likeCount ?></span>
                    </button>
                    <button type="button" class="post-tool quote-post-btn" data-quote="@<?= e($post['username']) ?>, ">Цитата</button>
                    <?php if ($canSolve): ?>
                        <button type="button"
                                class="post-tool solve-post-btn"
                                data-topic-id="<?= (int)$post['topic_id'] ?>"
                                data-post-id="<?= (int)$post['id'] ?>"
                                data-action="<?= $isSolution ? 'clear' : 'solve' ?>">
                            <?= $isSolution ? 'Снять решение' : 'Это решение' ?>
                        </button>
                    <?php endif; ?>
                    <?php if ($canEdit): ?>
                        <button type="button" class="post-tool edit-post-btn">Изменить</button>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                        <button type="button" class="post-tool danger delete-post-btn" data-post-id="<?= (int)$post['id'] ?>">Удалить</button>
                    <?php endif; ?>
                    <?php if ($me && (int)($post['user_id'] ?? 0) !== (int)$me['id']): ?>
                        <button type="button" class="post-tool report-post-btn" data-post-id="<?= (int)$post['id'] ?>">Жалоба</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
    return (string)ob_get_clean();
}
