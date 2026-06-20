<?php
/**
 * Проект: ВайбКод
 * Файл: includes/header.php
 * Автор: Beck Sarbassov
 * Версия: 1.2.0
 * Дата выпуска: 2026-06-16
 * Последнее обновление: 2026-06-21
 * Авторские права: © Beck Sarbassov. Все права защищены.
 *
 * EN: Renders the shared page header, navigation, search, and account menu.
 * RU: Рендерит общую шапку страницы, навигацию, поиск и меню аккаунта.
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? SITE_NAME;
$activeNav = $activeNav ?? '';
$me = current_user();
$unreadCount = $me ? unread_notifications_count((int)$me['id']) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(SITE_TAGLINE) ?> — общайтесь, делитесь проектами, ищите решения и развивайте идеи вместе.">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> · <?= e(SITE_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>VK</text></svg>">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body data-logged-in="<?= $me ? '1' : '0' ?>">

<div class="bg-aurora" aria-hidden="true">
    <span class="blob blob-1"></span>
    <span class="blob blob-2"></span>
    <span class="blob blob-3"></span>
</div>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">VK</span>
            <span class="brand-text"><?= e(SITE_NAME) ?><i><?= e(SITE_TAGLINE) ?></i></span>
        </a>

        <nav class="main-nav" aria-label="Основная навигация">
            <a href="<?= url('index.php') ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Форум</a>
            <a href="<?= url('index.php#categories') ?>">Разделы</a>
            <a href="<?= url('members.php') ?>" class="<?= $activeNav === 'members' ? 'active' : '' ?>">Участники</a>
            <a href="<?= url('search.php') ?>" class="<?= $activeNav === 'search' ? 'active' : '' ?>">Поиск</a>
        </nav>

        <form class="quick-search" action="<?= url('search.php') ?>" method="get" role="search">
            <input type="search" name="q" placeholder="Поиск по форуму..." aria-label="Поиск"
                   value="<?= e($_GET['q'] ?? '') ?>">
        </form>

        <div class="auth-area">
            <?php if ($me): ?>
                <a class="btn btn-primary btn-sm" href="<?= url('new-topic.php') ?>">+ Тема</a>
                <a class="icon-link <?= $activeNav === 'notifications' ? 'active' : '' ?>" href="<?= url('notifications.php') ?>" aria-label="Уведомления">
                    <span>Увед.</span>
                    <?php if ($unreadCount > 0): ?><b><?= min(99, $unreadCount) ?></b><?php endif; ?>
                </a>
                <div class="user-chip" tabindex="0">
                    <span class="avatar avatar-sm" style="--clr: <?= e($me['avatar_color']) ?>"><?= e(avatar_initial($me['username'])) ?></span>
                    <span class="user-chip-name"><?= e($me['username']) ?></span>
                    <div class="user-menu">
                        <a href="<?= url('profile.php?u=' . urlencode($me['username'])) ?>">Профиль</a>
                        <a href="<?= url('notifications.php') ?>">Уведомления<?= $unreadCount > 0 ? ' (' . min(99, $unreadCount) . ')' : '' ?></a>
                        <a href="<?= url('bookmarks.php') ?>">Избранное</a>
                        <a href="<?= url('settings.php') ?>">Настройки</a>
                        <a href="<?= url('new-topic.php') ?>">Создать тему</a>
                        <?php if (user_can_moderate($me)): ?>
                            <a href="<?= url('reports.php') ?>">Жалобы</a>
                        <?php endif; ?>
                        <a href="<?= url('api/logout.php') ?>" class="danger">Выйти</a>
                    </div>
                </div>
            <?php else: ?>
                <a class="btn btn-ghost btn-sm" href="<?= url('login.php') ?>">Войти</a>
                <a class="btn btn-primary btn-sm" href="<?= url('register.php') ?>">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container site-main">
