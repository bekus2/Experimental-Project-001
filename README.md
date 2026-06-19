# VibeCode Forum

VibeCode Forum is a lightweight PHP/MySQL community forum with AJAX login, registration, replies, likes, live search, profile settings, topic filters, and moderator controls.

## English

### Project Overview
- Project name: VibeCode Forum
- Current version: 1.1.0
- Created: 2026-06-16
- Last updated: 2026-06-19
- Author: Beck Sarbassov

### Features
- AJAX registration and login with `password_hash` / `password_verify`.
- Topic creation, replies, likes, live search, category pages, and user profiles.
- Homepage filters by category and sort mode: active, popular, unanswered.
- Forum activity dashboard with daily posts, unanswered topics, locked topics, and active members.
- Moderator/admin topic actions: pin/unpin and lock/unlock.
- Profile settings: public bio, avatar color, and password change.
- CSRF protection for state-changing API requests.
- Responsive lighter UI for desktop, tablet, and mobile.

### System Requirements
- PHP 8.1 or newer with `pdo_mysql` and `mbstring`.
- MySQL 8.x or MariaDB 10.4+.
- A local web server or PHP built-in server.

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

Security note: these are development credentials only. Change the administrator password immediately after the first login using `settings.php`, especially before deployment. Do not show these credentials on public pages.

### Usage
- Guests can browse categories, topics, profiles, and search.
- Registered users can create topics, reply, like posts, and edit their profile.
- Moderators and admins can pin/unpin and lock/unlock topics from the topic page.
- Users can change their password from the profile settings page.

### Folder Structure
```text
api/                  AJAX endpoints for auth, content, moderation, profile settings
assets/css/style.css  Responsive visual design
assets/js/app.js      Client-side AJAX and UI behavior
config/config.php     Database, site, session, and security constants
includes/             Shared DB, auth, helper, render, header, and footer code
sql/schema.sql        Database schema and starter categories
index.php             Forum homepage with dashboard, filters, categories, topics
category.php          Category topic listing with sorting and pagination
topic.php             Topic thread, replies, likes, moderation controls
settings.php          Profile and password settings
install.php           Local installer and seed data
```

### Backend
The backend is plain PHP with PDO prepared statements. Sessions use `HttpOnly` and `SameSite=Lax`. All state-changing API endpoints require CSRF validation.

### Frontend
The frontend uses semantic HTML, CSS, JavaScript, and jQuery. The UI is intentionally lighter than the original dark neon theme, with responsive layouts and accessible form controls.

### API
- `api/register.php` - create account.
- `api/login.php` - authenticate account.
- `api/logout.php` - end session.
- `api/create_topic.php` - create topic and first post.
- `api/create_post.php` - create reply.
- `api/like.php` - toggle post like.
- `api/search.php` - live search.
- `api/moderate_topic.php` - pin/unpin and lock/unlock topics.
- `api/update_profile.php` - update bio and avatar color.
- `api/change_password.php` - change password.

### Database
The project uses MySQL/MariaDB tables: `users`, `categories`, `topics`, `posts`, and `post_likes`. Passwords are stored only as hashes.

### Forms and Email
The project currently has account, topic, reply, profile, and password forms. Email notifications are not implemented yet. If email is added later, use environment variables for SMTP secrets.

### Security Notes
- Use prepared statements for SQL.
- Escape output with `htmlspecialchars`.
- Protect write requests with CSRF tokens.
- Hash passwords with `password_hash`.
- Change default credentials before production.
- Remove or block `install.php` in production.
- Block public access to `config/`, `includes/`, `sql/`, logs, backups, and private configuration files.

### Deployment Notes
Point the web server document root to this project directory or configure routes for the PHP files. For Apache/nginx production hosting, deny direct access to service directories and keep secrets outside public files.

### Scaling Recommendations
- Add migrations instead of rerunning `install.php` for production upgrades.
- Add an admin dashboard for category/user management.
- Add email verification and password reset.
- Add rate limiting for login, registration, search, and post creation.
- Add automated tests and CI checks.

## Русский

### Описание проекта
- Название проекта: ВайбКод Форум
- Текущая версия: 1.1.0
- Дата создания: 2026-06-16
- Последнее обновление: 2026-06-19
- Автор: Beck Sarbassov

### Возможности
- AJAX-регистрация и вход с `password_hash` / `password_verify`.
- Создание тем, ответы, лайки, живой поиск, страницы разделов и профили пользователей.
- Фильтры на главной странице по разделу и сортировке: активные, популярные, без ответов.
- Панель активности форума: сообщения за день, темы без ответов, закрытые темы, активные участники.
- Действия модератора/администратора: закрепить/открепить и закрыть/открыть тему.
- Настройки профиля: описание, цвет аватара и смена пароля.
- CSRF-защита для API-запросов, которые изменяют данные.
- Адаптивный светлый интерфейс для компьютеров, планшетов и телефонов.

### Системные требования
- PHP 8.1 или новее с расширениями `pdo_mysql` и `mbstring`.
- MySQL 8.x или MariaDB 10.4+.
- Локальный веб-сервер или встроенный сервер PHP.

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

### Учетные данные по умолчанию
Начальный локальный администратор:
- Логин/email: `bek0435@gmail.com`
- Имя пользователя: `beck_admin`
- Пароль: `0123456789+Aa`

Демо-аккаунты:
- `neon_dev` / `password123` / администратор
- `promptsmith` / `password123` / модератор
- `lo_fi_lucy` / `password123` / участник

Важное замечание по безопасности: эти учетные данные предназначены только для разработки и первичной установки. Сразу после первого входа измените пароль администратора через `settings.php`, особенно перед публикацией проекта.

### Использование
- Гости могут просматривать разделы, темы, профили и поиск.
- Пользователи могут создавать темы, отвечать, ставить лайки и редактировать профиль.
- Модераторы и администраторы могут закреплять, откреплять, закрывать и открывать темы.
- Пользователи могут менять пароль в настройках профиля.

### Документация по backend
Backend написан на PHP без тяжелого фреймворка. Для базы используется PDO и подготовленные SQL-запросы. Сессии защищены параметрами `HttpOnly` и `SameSite=Lax`.

### Документация по frontend
Frontend использует HTML, CSS, JavaScript и jQuery. Интерфейс стал светлее, но сохранил аккуратные акценты, карточки, адаптивность и AJAX-взаимодействия.

### Документация по API
Основные API-файлы находятся в `api/`: авторизация, регистрация, создание тем/ответов, лайки, поиск, модерация, обновление профиля и смена пароля.

### База данных
Используются таблицы `users`, `categories`, `topics`, `posts`, `post_likes`. Пароли хранятся только в виде хешей.

### Формы и email
Есть формы входа, регистрации, создания темы, ответа, настроек профиля и смены пароля. Email-уведомления пока не реализованы.

### Безопасность
- SQL-запросы выполняются через PDO prepared statements.
- Вывод экранируется через `htmlspecialchars`.
- Изменяющие API-запросы защищены CSRF.
- Пароли хешируются.
- Начальные пароли нужно менять перед production.
- `install.php` нужно удалить или закрыть после установки на production.

### Развертывание
Для production настройте Apache или nginx, закройте доступ к `config/`, `includes/`, `sql/`, логам и резервным копиям. Секреты храните в переменных окружения или защищенных серверных конфигурациях.

### Рекомендации по масштабированию
- Добавить миграции БД.
- Добавить полноценную административную панель.
- Добавить email-подтверждение и восстановление пароля.
- Добавить rate limiting.
- Добавить автоматические тесты и CI.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-19
Авторские права: © Beck Sarbassov. Все права защищены.
