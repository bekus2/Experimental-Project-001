<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . url('index.php'));
    exit;
}

$pageTitle = 'Регистрация';
require __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Влиться в комьюнити 🚀</h1>
    <p class="sub">Пара секунд — и ты вайбкодер.</p>

    <form id="register-form" novalidate>
        <div class="form-msg"></div>
        <div class="field">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" autocomplete="username"
                   minlength="<?= MIN_USERNAME_LENGTH ?>" maxlength="<?= MAX_USERNAME_LENGTH ?>" required>
            <p class="hint">Латиница, цифры, _ - . — от <?= MIN_USERNAME_LENGTH ?> до <?= MAX_USERNAME_LENGTH ?> символов.</p>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="email" required>
        </div>
        <div class="field">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                   minlength="<?= MIN_PASSWORD_LENGTH ?>" required>
            <p class="hint">Минимум <?= MIN_PASSWORD_LENGTH ?> символов.</p>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Создать аккаунт</button>
    </form>

    <p class="form-foot">Уже с нами? <a href="<?= url('login.php') ?>">Войти</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
