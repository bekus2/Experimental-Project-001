-- ============================================================
--  ВайбКод — схема базы данных
--  MySQL 8.x / совместимо с MariaDB 10.4+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `vibecode_forum`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `vibecode_forum`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `post_likes`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `topics`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
--  Пользователи
-- ------------------------------------------------------------
CREATE TABLE `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(24)  NOT NULL,
    `email`         VARCHAR(190) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `avatar_color`  VARCHAR(7)   NOT NULL DEFAULT '#7c5cff',
    `bio`           VARCHAR(280) NOT NULL DEFAULT '',
    `role`          ENUM('member','moderator','admin') NOT NULL DEFAULT 'member',
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_username` (`username`),
    UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Категории (разделы форума)
-- ------------------------------------------------------------
CREATE TABLE `categories` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`       VARCHAR(80)  NOT NULL,
    `slug`        VARCHAR(90)  NOT NULL,
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `icon`        VARCHAR(8)   NOT NULL DEFAULT '💬',
    `accent`      VARCHAR(7)   NOT NULL DEFAULT '#7c5cff',
    `position`    INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Темы (треды)
-- ------------------------------------------------------------
CREATE TABLE `topics` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL,
    `title`       VARCHAR(160) NOT NULL,
    `slug`        VARCHAR(180) NOT NULL,
    `is_pinned`   TINYINT(1)   NOT NULL DEFAULT 0,
    `is_locked`   TINYINT(1)   NOT NULL DEFAULT 0,
    `views`       INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_post_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_last_post` (`last_post_at`),
    CONSTRAINT `fk_topic_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_topic_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Сообщения (посты внутри темы)
-- ------------------------------------------------------------
CREATE TABLE `posts` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `topic_id`   INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `body`       TEXT         NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `edited_at`  DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_topic` (`topic_id`),
    KEY `idx_user` (`user_id`),
    CONSTRAINT `fk_post_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Лайки сообщений
-- ------------------------------------------------------------
CREATE TABLE `post_likes` (
    `post_id`    INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`post_id`, `user_id`),
    KEY `idx_user` (`user_id`),
    CONSTRAINT `fk_like_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Начальные данные
-- ============================================================

INSERT INTO `categories` (`title`, `slug`, `description`, `icon`, `accent`, `position`) VALUES
('Вайб-кодинг',        'vibe-coding',  'Делитесь потоком, плейлистами и тем, как вы ловите волну в коде.', '🌊', '#7c5cff', 1),
('AI и помощники',     'ai-tools',     'Claude, Copilot, агенты и всё, что кодит вместе с вами.',          '🤖', '#22d3ee', 2),
('Покажи свой проект', 'showcase',     'Демки, пет-проекты, стартапы. Хвастайтесь!',                       '🚀', '#f472b6', 3),
('Помощь по коду',     'help',         'Застряли? Спросите комьюнити — поможем разобраться.',              '🛟', '#34d399', 4),
('Сетап и инструменты','setup',        'Редакторы, темы, шрифты, клавиатуры и идеальное рабочее место.',   '🛠️', '#fbbf24', 5),
('Флудилка',           'offtopic',     'Свободное общение обо всём на свете.',                             '☕', '#fb7185', 6);

-- Демо-пользователи и стартовые темы создаются скриптом install.php
-- (там пароли хэшируются через password_hash с PASSWORD_DEFAULT).
