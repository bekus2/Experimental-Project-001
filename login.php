<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . url('index.php'));
    exit;
}

$pageTitle = 'Вход';
require __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>С возвращением 👋</h1>
    <p class="sub">Войдите, чтобы продолжить вайбить.</p>

    <form id="login-form" novalidate>
        <div class="form-msg"></div>
        <div class="field">
            <label for="login">Имя пользователя или email</label>
            <input type="text" id="login" name="login" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Войти</button>
    </form>

    <p class="form-foot">Нет аккаунта? <a href="<?= url('register.php') ?>">Зарегистрироваться</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
