# PROJECT_CONTEXT.md

## Purpose
VibeCode Forum is a small but usable community forum for discussions, questions, project showcases, and knowledge sharing.

## Business Logic
The forum is centered around categories, topics, posts, likes, and user roles. Guests can read public content. Authenticated users can contribute. Moderators and administrators can manage topic visibility and discussion flow by pinning or locking topics.

## Target Users
- Community members who want to ask questions or share projects.
- Moderators who need light topic control.
- Administrators who set up and maintain the forum.

## Core Functions
- Register and log in.
- Create topics and first posts.
- Reply to topics through AJAX.
- Like posts through AJAX.
- Search topics/posts.
- Browse categories.
- Filter discussions by category and sort mode.
- Edit profile bio and avatar color.
- Change password.
- Pin/unpin and lock/unlock topics as moderator/admin.

## Architecture Overview
The application uses plain PHP pages for views and small JSON API endpoints for AJAX actions. Shared behavior lives in `includes/`. Database access is centralized through PDO in `includes/db.php`.

## Data Flow
1. Browser renders PHP page.
2. Header emits a CSRF token in a meta tag.
3. JavaScript sends AJAX requests with the CSRF token.
4. API endpoints validate method, session, CSRF, input, and permissions.
5. API endpoints write through PDO prepared statements and return JSON.

## Security Logic
- `includes/auth.php` starts secure sessions and validates CSRF tokens.
- Passwords are stored as `password_hash` values.
- User output is escaped with `e()`.
- Topic moderation checks `admin` or `moderator` roles.
- Direct write APIs require authenticated users.

## Administrative Logic
There is no separate full admin dashboard yet. Administrative control currently exists through:
- admin/moderator roles in `users.role`;
- topic moderation controls in `topic.php`;
- default local admin seed in `install.php`.

## API Logic
API endpoints live in `api/`. They return JSON and terminate via `json_response()` or `json_error()`.

## Database Logic
The database schema lives in `sql/schema.sql`. The project uses:
- `users`
- `categories`
- `topics`
- `posts`
- `post_likes`

`install.php` is a fresh-install/reset script and should not be used as a production migration tool.

## Important Technical Decisions
- No heavy framework to keep hosting simple.
- PDO prepared statements for SQL safety.
- jQuery retained because the project already used it for AJAX.
- Existing schema fields were reused for new features to avoid a breaking migration.
- Lighter UI was implemented through CSS variables and reusable panels.

## Constraints
- No automated migrations yet.
- No email sending yet.
- No password reset email yet.
- No production-grade rate limiting yet.

## Future Direction
Add a migration system, admin dashboard, notification/email subsystem, rate limiting, automated tests, and GitHub Actions validation.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-19
Copyright: © Beck Sarbassov. All rights reserved.
