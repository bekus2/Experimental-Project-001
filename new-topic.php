<?php
/**
 * Проект: ВайбКод
 * Файл: new-topic.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders the topic creation form with category, type, tags, and draft support.
 * RU: Рендерит форму создания темы с разделом, типом, тегами и поддержкой черновика.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login_redirect();

$pdo = db();
$categories = $pdo->query('SELECT id, title, icon FROM categories ORDER BY position ASC')->fetchAll();
$preselect  = (int)($_GET['cat'] ?? 0);
$topicTypes = topic_type_options();

$pageTitle = 'Новая тема';
require __DIR__ . '/includes/header.php';
?>

<div class="card form-card wide">
    <h1>Создать тему ✍️</h1>
    <p class="sub">Поделись идеей, вопросом или проектом с комьюнити.</p>

    <form id="new-topic-form" novalidate>
        <div class="form-msg"></div>

        <div class="field-row">
            <div class="field">
                <label for="category_id">Раздел</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $preselect === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= e($c['icon']) ?> <?= e($c['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="topic_type">Тип темы</label>
                <select id="topic_type" name="topic_type" required>
                    <?php foreach ($topicTypes as $value => $label): ?>
                        <option value="<?= e($value) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="field">
            <label for="title">Заголовок</label>
            <input type="text" id="title" name="title" maxlength="160" placeholder="О чём тема?" required>
        </div>

        <div class="field">
            <label for="tags">Теги</label>
            <input type="text" id="tags" name="tags" maxlength="255" placeholder="php, дизайн, вопрос">
            <p class="hint">До 6 тегов через запятую. Они помогут найти тему позже.</p>
        </div>

        <div class="field">
            <label for="body">Сообщение</label>
            <div class="editor-toolbar">
                <button type="button" data-wrap="**" title="Жирный">B</button>
                <button type="button" data-wrap="*" title="Курсив">i</button>
                <button type="button" data-wrap="`" title="Код">&lt;/&gt;</button>
                <button type="button" data-wrap="code" title="Блок кода">{ }</button>
            </div>
            <textarea id="body" name="body" data-draft-key="new-topic-body" placeholder="Расскажи подробнее… Поддерживается **жирный**, *курсив*, `код` и ```блоки кода```" required></textarea>
            <p class="hint draft-status">Черновик сохраняется в этом браузере автоматически.</p>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Опубликовать тему</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
