<?php
/**
 * Проект: ВайбКод
 * Файл: includes/footer.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders the shared footer, useful navigation links, toast host, and script includes.
 * RU: Рендерит общий футер, полезные ссылки, контейнер уведомлений и подключение скриптов.
 */
?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <span class="brand-mark">🌊</span>
            <div>
                <strong><?= e(SITE_NAME) ?></strong>
                <p><?= e(SITE_TAGLINE) ?>. Сделано с вайбом и кодом.</p>
            </div>
        </div>
        <div class="footer-links">
            <a href="<?= url('index.php') ?>">Форум</a>
            <a href="<?= url('members.php') ?>">Участники</a>
            <a href="<?= url('search.php') ?>">Поиск</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= url('notifications.php') ?>">Уведомления</a>
                <a href="<?= url('bookmarks.php') ?>">Закладки</a>
            <?php else: ?>
                <a href="<?= url('register.php') ?>">Присоединиться</a>
            <?php endif; ?>
        </div>
        <p class="footer-copy">© <?= date('Y') ?> <?= e(SITE_NAME) ?>. Стек: PHP&nbsp;8.3 · MySQL · jQuery · AJAX.</p>
    </div>
</footer>

<div id="toast-host" aria-live="polite"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
