# HANDOFF.md

## Project
VibeCode Forum is a PHP/MySQL forum with AJAX auth, topics, replies, likes, search, profile settings, password change, and moderator topic controls.

## Technologies
- PHP 8.1+
- MySQL 8.x or MariaDB 10.4+
- PDO
- HTML/CSS/JavaScript
- jQuery AJAX

## Structure
- `index.php` - homepage, dashboard, category cards, filtered topic list.
- `category.php` - category topic list with sorting and pagination.
- `topic.php` - thread page, replies, likes, moderator controls.
- `settings.php` - profile settings and password change.
- `api/` - JSON endpoints for user/content/moderation actions.
- `includes/` - shared authentication, database, rendering, and layout helpers.
- `config/config.php` - environment-driven configuration constants.
- `sql/schema.sql` - schema and starter categories.
- `install.php` - creates schema and seed accounts/topics for local setup.

## Install And Run
1. Configure DB values in `config/config.php` or environment variables.
2. Run `php install.php`.
3. Run `php -S 127.0.0.1:8000`.
4. Open `http://127.0.0.1:8000/`.

## Default Local Access
- Admin email/login: `bek0435@gmail.com`
- Admin username: `beck_admin`
- Admin password: `0123456789+Aa`

Change the admin password immediately from `settings.php`.

## Deployment
- Use Apache/nginx with PHP and MySQL/MariaDB.
- Remove or block `install.php` after installation.
- Deny public access to `config/`, `includes/`, `sql/`, logs, backups, and private files.
- Use environment variables for database credentials.

## Important Files Not To Change Blindly
- `includes/auth.php` - sessions, role checks, CSRF.
- `includes/functions.php` - escaping, URL building, markdown rendering helpers.
- `api/create_post.php` and `api/create_topic.php` - core content creation.
- `sql/schema.sql` and `install.php` - database reset/seed logic.

## Current Limitations
- No migration runner yet; `install.php` is for fresh local setup.
- No email verification or password reset email flow.
- No full admin dashboard for users/categories yet.
- No automated test suite yet.

## Future Improvements
- Add database migrations.
- Add rate limiting for auth and posting.
- Add admin dashboard for categories/users.
- Add CI with PHP lint and integration smoke checks.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-19
Copyright: © Beck Sarbassov. All rights reserved.
