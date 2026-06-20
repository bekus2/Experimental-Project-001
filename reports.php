<?php
/**
 * Проект: ВайбКод
 * Файл: reports.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-21
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Provides a moderator queue for reported posts.
 * RU: Предоставляет модераторскую очередь жалоб на сообщения.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login_redirect();

$me = current_user();
if (!user_can_moderate($me)) {
    http_response_code(403);
    $pageTitle = 'Нет доступа';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card empty"><div class="ico">!</div><p>Эта страница доступна только модераторам.</p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$status = (string)($_GET['status'] ?? 'open');
if (!in_array($status, ['open', 'reviewed', 'dismissed'], true)) {
    $status = 'open';
}

$stmt = db()->prepare('
    SELECT r.*, p.body, p.topic_id, t.title AS topic_title,
           reporter.username AS reporter_name,
           author.username AS author_name
    FROM post_reports r
    JOIN posts p ON p.id = r.post_id
    JOIN topics t ON t.id = p.topic_id
    JOIN users reporter ON reporter.id = r.reporter_id
    JOIN users author ON author.id = p.user_id
    WHERE r.status = ?
    ORDER BY r.created_at DESC
    LIMIT 80
');
$stmt->execute([$status]);
$reports = $stmt->fetchAll();

$pageTitle = 'Жалобы';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
    <h1>Жалобы</h1>
    <div class="status-tabs">
        <a class="<?= $status === 'open' ? 'active' : '' ?>" href="<?= url('reports.php?status=open') ?>">Открытые</a>
        <a class="<?= $status === 'reviewed' ? 'active' : '' ?>" href="<?= url('reports.php?status=reviewed') ?>">Проверенные</a>
        <a class="<?= $status === 'dismissed' ? 'active' : '' ?>" href="<?= url('reports.php?status=dismissed') ?>">Отклоненные</a>
    </div>
</div>

<?php if (!$reports): ?>
    <div class="card empty"><div class="ico">✓</div><p>Жалоб в этом статусе нет.</p></div>
<?php else: ?>
    <div class="report-list">
        <?php foreach ($reports as $report): ?>
            <article class="report-card" data-report-id="<?= (int)$report['id'] ?>">
                <div>
                    <h2><a href="<?= url('topic.php?id=' . (int)$report['topic_id'] . '#post-' . (int)$report['post_id']) ?>"><?= e($report['topic_title']) ?></a></h2>
                    <p class="report-meta">Жалоба от <?= e($report['reporter_name']) ?> на сообщение <?= e($report['author_name']) ?> · <?= e(time_ago($report['created_at'])) ?></p>
                    <p><strong>Причина:</strong> <?= e($report['reason']) ?></p>
                    <blockquote><?= e(excerpt($report['body'], 220)) ?></blockquote>
                </div>
                <?php if ($status === 'open'): ?>
                    <div class="report-actions">
                        <button type="button" class="btn btn-primary btn-sm moderate-report-btn" data-report-id="<?= (int)$report['id'] ?>" data-status="reviewed">Проверено</button>
                        <button type="button" class="btn btn-ghost btn-sm moderate-report-btn" data-report-id="<?= (int)$report['id'] ?>" data-status="dismissed">Отклонить</button>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
