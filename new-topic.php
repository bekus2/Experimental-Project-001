<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login_redirect();

$pdo = db();
$categories = $pdo->query('SELECT id, title, icon FROM categories ORDER BY position ASC')->fetchAll();
$preselect  = (int)($_GET['cat'] ?? 0);

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
        </div>

        <div class="field">
            <label for="title">Заголовок</label>
            <input type="text" id="title" name="title" maxlength="160" placeholder="О чём тема?" required>
        </div>

        <div class="field">
            <label for="body">Сообщение</label>
            <div class="editor-toolbar">
                <button type="button" data-wrap="**" title="Жирный">B</button>
                <button type="button" data-wrap="*" title="Курсив">i</button>
                <button type="button" data-wrap="`" title="Код">&lt;/&gt;</button>
                <button type="button" data-wrap="code" title="Блок кода">{ }</button>
            </div>
            <textarea id="body" name="body" placeholder="Расскажи подробнее… Поддерживается **жирный**, *курсив*, `код` и ```блоки кода```" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Опубликовать тему</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
