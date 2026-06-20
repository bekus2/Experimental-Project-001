<?php
/**
 * Проект: ВайбКод
 * Файл: notifications.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Displays the authenticated user's notification center.
 * RU: Показывает центр уведомлений авторизованного пользователя.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login_redirect();

$me = current_user();
$stmt = db()->prepare('
    SELECT n.*, t.title AS topic_title
    FROM notifications n
    LEFT JOIN topics t ON t.id = n.topic_id
    WHERE n.user_id = ?
    ORDER BY n.is_read ASC, n.created_at DESC
    LIMIT 80
');
$stmt->execute([(int)$me['id']]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Уведомления';
$activeNav = 'notifications';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
    <h1>Уведомления</h1>
    <?php if ($notifications): ?>
        <button type="button" class="btn btn-ghost btn-sm mark-notifications-read-btn">Отметить все прочитанными</button>
    <?php endif; ?>
</div>

<?php if (!$notifications): ?>
    <div class="card empty">
        <div class="ico">○</div>
        <p>Уведомлений пока нет.</p>
    </div>
<?php else: ?>
    <div class="notification-list">
        <?php foreach ($notifications as $item): ?>
            <article class="notification-item <?= (int)$item['is_read'] === 0 ? 'unread' : '' ?>">
                <div>
                    <strong><?= e($item['message']) ?></strong>
                    <p><?= e(time_ago($item['created_at'])) ?></p>
                </div>
                <div class="notification-actions">
                    <?php if (!empty($item['topic_id'])): ?>
                        <a class="btn btn-primary btn-sm" href="<?= url('topic.php?id=' . (int)$item['topic_id'] . (!empty($item['post_id']) ? '#post-' . (int)$item['post_id'] : '')) ?>">Открыть</a>
                    <?php endif; ?>
                    <?php if ((int)$item['is_read'] === 0): ?>
                        <button type="button" class="btn btn-ghost btn-sm mark-notification-read-btn" data-notification-id="<?= (int)$item['id'] ?>">Прочитано</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
