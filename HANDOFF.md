# HANDOFF.md

## Project
VibeCode Forum is a PHP/MySQL community forum with AJAX auth, typed/tagged topics, replies, likes, live search, bookmarks, subscriptions, notifications, solved answers, post reports, member directory, profile settings, password change, and moderator tools.

## Technologies
- PHP 8.1+
- MySQL 8.x or MariaDB 10.4+
- PDO
- HTML/CSS/JavaScript
- jQuery AJAX

## Structure
- `index.php` - homepage, dashboard, category cards, type/category/sort filters, topic list.
- `category.php` - category topic list with type/sort filters and pagination.
- `topic.php` - thread page, posts, likes, edit/delete/report/solution controls, bookmarks, subscriptions.
- `new-topic.php` - topic creation form with category, type, tags, and draft autosave.
- `members.php` - member directory with search, sorting, stats, and online/last-seen status.
- `notifications.php` - authenticated user's notification center.
- `bookmarks.php` - authenticated user's saved topics.
- `reports.php` - moderator/admin report queue.
- `tag.php` - tag-based topic listing.
- `settings.php` - profile settings and password change.
- `api/` - JSON endpoints for user, content, notification, report, and moderation actions.
- `includes/` - shared authentication, database, rendering, layout, and helper logic.
- `config/config.php` - environment-driven configuration constants.
- `sql/schema.sql` - schema and starter categories.
- `install.php` - fresh local schema/seed setup.

## Install And Run
1. Configure DB values in `config/config.php` or environment variables.
2. Run `php install.php`.
3. Run `php -S 127.0.0.1:8000`.
4. Open `http://127.0.0.1:8000/`.

## Default Local Access
- Admin email/login: `bek0435@gmail.com`
- Admin username: `beck_admin`
- Admin password: `0123456789+Aa`

Change the admin password immediately from `settings.php`, especially before deployment.

## Deployment
- Use Apache/nginx with PHP and MySQL/MariaDB.
- Remove or block `install.php` after installation.
- Deny public access to `config/`, `includes/`, `sql/`, logs, backups, and private files.
- Use environment variables for database credentials and future SMTP secrets.
- Do not rerun `install.php` on production data; add migrations first.

## Important Files Not To Change Blindly
- `includes/auth.php` - sessions, current user tracking, role checks, CSRF.
- `includes/functions.php` - escaping, URLs, topic metadata, notifications, permissions, rendering helpers.
- `includes/render.php` - reusable post markup and action controls.
- `api/create_post.php` and `api/create_topic.php` - core content creation and subscriptions.
- `api/edit_post.php`, `api/delete_post.php`, `api/report_post.php` - sensitive post actions.
- `sql/schema.sql` and `install.php` - destructive fresh-install/reset logic.

## Current Limitations
- No migration runner yet; `install.php` is for fresh local setup.
- No email verification, email notification, or password reset email flow.
- No full admin dashboard for users/categories/SEO settings yet.
- No production-grade rate limiting yet.
- No automated test suite yet.

## Future Improvements
- Add database migrations.
- Add rate limiting for auth, posting, search, and reports.
- Add admin dashboard for categories, users, reports, and SEO settings.
- Add email verification and password reset.
- Add CI with PHP lint, integration smoke checks, and browser checks.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-21
Copyright: © Beck Sarbassov. All rights reserved.
