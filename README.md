# VibeCode Forum / ВайбКод Форум

VibeCode Forum is a responsive PHP/MySQL community forum with AJAX auth, topic discussions, replies, likes, search, profiles, bookmarks, subscriptions, notifications, solved answers, reports, and moderator tools.

## English

### Project Overview
- Project name: VibeCode Forum
- Current version: 1.2.0
- Created: 2026-06-16
- Last updated: 2026-06-21
- Author: Beck Sarbassov

### Added In Version 1.2.0
1. Topic types: Discussion, Question, Project, and Guide.
2. Topic tags with clickable tag pages.
3. Solved-topic status with a selected solution post.
4. User bookmarks for saving important topics.
5. Topic subscriptions for following replies.
6. In-app notification center for replies, reports, and solution events.
7. Inline post editing for authors, moderators, and admins.
8. Soft post deletion that preserves thread structure.
9. User reports for problematic posts.
10. Moderator report queue with reviewed/dismissed workflow.
11. Member directory with search, sorting, stats, and online/last-seen status.
12. Browser draft autosave for new topics and replies.
13. Expanded homepage/category filters by topic type and solved status.
14. Lighter responsive CSS updated for desktop, tablet, mobile, and narrow screens.

### System Requirements
- PHP 8.1 or newer with `pdo_mysql` and `mbstring`.
- MySQL 8.x or MariaDB 10.4+.
- Local PHP server, Apache, nginx, or OSPanel/OpenServer-style local stack.

### Installation
1. Configure database access in `config/config.php` or environment variables:

```bash
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=vibecode_forum
DB_USER=root
DB_PASS=
```

2. Run the installer:

```bash
php install.php
```

3. Start the local development server:

```bash
php -S 127.0.0.1:8000
```

4. Open `http://127.0.0.1:8000/`.

### Default Local Accounts
Initial local administrator:
- Login/email: `bek0435@gmail.com`
- Username: `beck_admin`
- Password: `0123456789+Aa`

Demo accounts:
- `neon_dev` / `password123` / admin
- `promptsmith` / `password123` / moderator
- `lo_fi_lucy` / `password123` / member

Security note: these are development/initial-install credentials only. Change the administrator password immediately after the first login, especially before deployment. Do not show admin credentials on public pages.

### Usage
- Guests can browse categories, topics, tags, member profiles, and search results.
- Members can create typed/tagged topics, reply, like, bookmark, subscribe, report posts, and manage profile/password settings.
- Topic authors and moderators can mark a reply as the accepted solution.
- Authors, moderators, and admins can edit posts; moderators/admins can soft-delete posts.
- Moderators/admins can pin, unpin, lock, unlock topics, and review post reports.

### Folder Structure
```text
api/                  JSON endpoints for auth, content, notifications, reports, moderation
assets/css/style.css  Responsive light UI and mobile/tablet/desktop layout rules
assets/js/app.js      AJAX actions, editor helpers, drafts, moderation UI behavior
config/config.php     Database, site, session, and security constants
includes/             Shared DB, auth, helper, render, header, and footer code
sql/schema.sql        Database schema and starter categories
index.php             Homepage dashboard, filters, categories, topic list
category.php          Category topic listing with type/sort filters and pagination
topic.php             Thread, replies, solution controls, bookmarks, subscriptions
members.php           Member directory with stats and online status
notifications.php     Authenticated user's notification center
bookmarks.php         Authenticated user's saved topics
reports.php           Moderator report queue
tag.php               Topic list for a selected tag
settings.php          Profile and password settings
install.php           Local installer and seed data
```

### Backend
The backend is plain PHP with PDO prepared statements. Sessions use `HttpOnly` and `SameSite=Lax`. State-changing API endpoints require CSRF validation and server-side permission checks.

### Frontend
The frontend uses semantic HTML, CSS, JavaScript, and jQuery. Version 1.2.0 keeps the site light and readable, adds richer forum controls, and improves responsive behavior across desktop, tablet, mobile, and very narrow screens.

### API
- `api/register.php` - create account.
- `api/login.php` - authenticate account.
- `api/logout.php` - end session.
- `api/create_topic.php` - create topic with type, tags, first post, and author subscription.
- `api/create_post.php` - create reply, auto-subscribe author, notify subscribers.
- `api/like.php` - toggle post like.
- `api/search.php` - live search across topics, tags, and visible posts.
- `api/toggle_topic_preference.php` - toggle bookmarks and subscriptions.
- `api/mark_solved.php` - mark or clear a topic solution.
- `api/edit_post.php` - edit an allowed post.
- `api/delete_post.php` - soft-delete an allowed post.
- `api/report_post.php` - report a post.
- `api/moderate_report.php` - review or dismiss a report.
- `api/mark_notifications_read.php` - mark one or all notifications as read.
- `api/moderate_topic.php` - pin/unpin and lock/unlock topics.
- `api/update_profile.php` - update bio and avatar color.
- `api/change_password.php` - change password.

### Database
The project uses MySQL/MariaDB tables: `users`, `categories`, `topics`, `posts`, `post_likes`, `topic_bookmarks`, `topic_subscriptions`, `notifications`, and `post_reports`. Passwords are stored only as hashes.

### Forms And Email
The project includes account, topic, reply, post edit, report, notification, profile, and password forms. Email notifications are not implemented yet. If email is added later, use environment variables or protected server configuration for SMTP secrets.

### Security Notes
- Use prepared statements for SQL.
- Escape output with `htmlspecialchars` through `e()`.
- Protect write requests with CSRF tokens.
- Hash passwords with `password_hash`.
- Check authentication and role permissions on the server.
- Soft-deleted posts cannot be liked, edited, solved, or reported through the UI/API.
- Change default credentials before production.
- Remove or block `install.php` in production.
- Block public access to `config/`, `includes/`, `sql/`, logs, backups, and private configuration files.

### Deployment Notes
Point the web server document root to this project directory or configure routes for the PHP files. For Apache/nginx production hosting, deny direct access to service directories and keep secrets outside public files. Use migrations instead of rerunning `install.php` on production data.

### Scaling Recommendations
- Add a database migration runner.
- Add a full admin dashboard for users, categories, SEO content, and settings.
- Add email verification and password reset.
- Add rate limiting for login, registration, search, reports, and posting.
- Add automated tests and CI checks.
- Add full-text indexes or an external search service for larger forums.

## Русский

### Описание Проекта
- Название проекта: ВайбКод Форум
- Текущая версия: 1.2.0
- Дата создания: 2026-06-16
- Последнее обновление: 2026-06-21
- Автор: Beck Sarbassov

### Добавлено В Версии 1.2.0
1. Типы тем: обсуждение, вопрос, проект и гайд.
2. Теги тем с отдельными страницами по тегу.
3. Статус решенной темы и выбор сообщения-решения.
4. Закладки пользователя для сохранения важных тем.
5. Подписки на темы для отслеживания новых ответов.
6. Центр уведомлений внутри сайта для ответов, жалоб и решений.
7. Редактирование сообщений прямо в теме.
8. Мягкое удаление сообщений без разрушения ветки обсуждения.
9. Жалобы пользователей на проблемные сообщения.
10. Очередь жалоб для модераторов со статусами проверено/отклонено.
11. Каталог участников с поиском, сортировкой, статистикой и онлайн-статусом.
12. Автосохранение черновиков новых тем и ответов в браузере.
13. Новые фильтры главной и разделов по типу темы и решенным темам.
14. Улучшенная светлая адаптивная верстка для компьютера, планшета, телефона и узких экранов.

### Системные Требования
- PHP 8.1 или новее с расширениями `pdo_mysql` и `mbstring`.
- MySQL 8.x или MariaDB 10.4+.
- Локальный PHP-сервер, Apache, nginx или OSPanel/OpenServer-подобный стек.

### Установка
1. Настройте подключение к базе данных в `config/config.php` или через переменные окружения.
2. Запустите установщик:

```bash
php install.php
```

3. Запустите локальный сервер:

```bash
php -S 127.0.0.1:8000
```

4. Откройте `http://127.0.0.1:8000/`.

### Учетные Данные По Умолчанию
Начальный локальный администратор:
- Логин/email: `bek0435@gmail.com`
- Имя пользователя: `beck_admin`
- Пароль: `0123456789+Aa`

Демо-аккаунты:
- `neon_dev` / `password123` / администратор
- `promptsmith` / `password123` / модератор
- `lo_fi_lucy` / `password123` / участник

Важное замечание по безопасности: эти учетные данные предназначены только для разработки и первичной локальной установки. Сразу после первого входа измените пароль администратора, особенно перед публикацией. Не отображайте учетные данные администратора на общедоступных страницах.

### Использование
- Гости могут читать разделы, темы, теги, профили участников и пользоваться поиском.
- Участники могут создавать темы с типами и тегами, отвечать, лайкать, добавлять темы в закладки, подписываться, отправлять жалобы и менять профиль/пароль.
- Автор темы и модераторы могут отмечать ответ как решение.
- Авторы, модераторы и администраторы могут редактировать сообщения; модераторы и администраторы могут мягко удалять сообщения.
- Модераторы и администраторы могут закреплять, откреплять, закрывать, открывать темы и обрабатывать жалобы.

### Backend
Backend написан на чистом PHP с PDO prepared statements. Сессии используют `HttpOnly` и `SameSite=Lax`. Все API, которые изменяют данные, проверяют CSRF, сессию, входные данные и права доступа.

### Frontend
Frontend использует HTML, CSS, JavaScript и jQuery. Интерфейс стал светлее, плотнее и функциональнее; адаптивные правила обновлены для desktop, tablet, mobile и очень узких экранов.

### API
Основные API-файлы находятся в `api/`: регистрация, вход, выход, создание тем/ответов, лайки, поиск, закладки, подписки, уведомления, решения, редактирование/удаление постов, жалобы, очередь модерации, профиль, пароль и модерация тем.

### База Данных
Используются таблицы `users`, `categories`, `topics`, `posts`, `post_likes`, `topic_bookmarks`, `topic_subscriptions`, `notifications`, `post_reports`. Пароли хранятся только в виде хешей.

### Формы И Email
Есть формы входа, регистрации, создания темы, ответа, редактирования поста, жалобы, уведомлений, настроек профиля и смены пароля. Email-уведомления пока не реализованы; SMTP-секреты в будущем нужно хранить только в переменных окружения или защищенной серверной конфигурации.

### Безопасность
- SQL-запросы выполняются через PDO prepared statements.
- Пользовательский вывод экранируется.
- Изменяющие API-запросы защищены CSRF.
- Пароли хешируются через `password_hash`.
- Права проверяются на сервере.
- Мягко удаленные сообщения нельзя лайкать, редактировать, отмечать решением или жаловаться на них через UI/API.
- Пароль администратора по умолчанию нужно заменить перед production.
- `install.php` нужно удалить или закрыть после установки на production.

### Развертывание
Для production настройте Apache/nginx, закройте доступ к `config/`, `includes/`, `sql/`, логам, резервным копиям и приватным файлам. Секреты храните вне публичных файлов. Для обновлений production-данных используйте миграции, а не повторный запуск `install.php`.

### Рекомендации По Масштабированию
- Добавить систему миграций базы данных.
- Добавить полноценную административную панель.
- Добавить email-подтверждение и восстановление пароля.
- Добавить rate limiting для входа, регистрации, поиска, жалоб и публикаций.
- Добавить автоматические тесты и CI.
- Добавить полнотекстовый поиск для больших форумов.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-21
Авторские права: © Beck Sarbassov. Все права защищены.
