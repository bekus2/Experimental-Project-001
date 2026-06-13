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
            <a href="<?= url('search.php') ?>">Поиск</a>
            <a href="<?= url('register.php') ?>">Присоединиться</a>
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
