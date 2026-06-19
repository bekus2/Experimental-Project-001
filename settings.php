<?php
/**
 * Проект: ВайбКод
 * Файл: settings.php
 * Автор: Beck Sarbassov
 * Версия: 1.1.0
 * Дата выпуска: 2026-06-19
 * Последнее обновление: 2026-06-19
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Provides account profile settings for the authenticated forum member.
 * RU: Предоставляет настройки профиля для авторизованного участника форума.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login_redirect();

$me = current_user();
$colorOptions = ['#7c5cff', '#2563eb', '#0ea5e9', '#059669', '#d97706', '#dc2626', '#be185d', '#4b5563'];

$pageTitle = 'Настройки профиля';
require __DIR__ . '/includes/header.php';
?>

<div class="card form-card wide settings-card">
    <div class="settings-preview">
        <span class="avatar avatar-lg profile-avatar-preview" style="--clr: <?= e($me['avatar_color']) ?>"><?= e(avatar_initial($me['username'])) ?></span>
        <div>
            <h1>Настройки профиля</h1>
            <p class="sub">Обновите публичное описание и цвет аватара. Эти данные видны другим участникам форума.</p>
        </div>
    </div>

    <form id="profile-settings-form" novalidate>
        <div class="form-msg"></div>

        <div class="field">
            <label for="bio">О себе</label>
            <textarea id="bio" name="bio" maxlength="280" placeholder="Коротко расскажите, чем занимаетесь и какие темы вам интересны."><?= e($me['bio'] ?? '') ?></textarea>
            <p class="hint"><span id="bio-counter"><?= mb_strlen((string)($me['bio'] ?? ''), 'UTF-8') ?></span>/280 символов</p>
        </div>

        <div class="field">
            <label for="avatar_color">Цвет аватара</label>
            <input type="color" id="avatar_color" name="avatar_color" value="<?= e($me['avatar_color']) ?>" aria-label="Цвет аватара">
            <div class="color-swatches" aria-label="Быстрый выбор цвета">
                <?php foreach ($colorOptions as $color): ?>
                    <button type="button" class="color-swatch" data-color="<?= e($color) ?>" style="--swatch: <?= e($color) ?>" aria-label="Выбрать цвет <?= e($color) ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Сохранить профиль</button>
            <a class="btn btn-ghost btn-lg" href="<?= url('profile.php?u=' . urlencode($me['username'])) ?>">Открыть профиль</a>
        </div>
    </form>

    <div class="settings-divider"></div>

    <form id="password-settings-form" novalidate>
        <h2>Смена пароля</h2>
        <p class="sub">После первой локальной установки обязательно замените стартовый пароль администратора.</p>
        <div class="form-msg"></div>

        <div class="field-row">
            <div class="field">
                <label for="current_password">Текущий пароль</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>
            <div class="field">
                <label for="new_password">Новый пароль</label>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="<?= MIN_PASSWORD_LENGTH ?>" required>
            </div>
            <div class="field">
                <label for="confirm_password">Повтор нового пароля</label>
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="<?= MIN_PASSWORD_LENGTH ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-ghost btn-lg">Обновить пароль</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
