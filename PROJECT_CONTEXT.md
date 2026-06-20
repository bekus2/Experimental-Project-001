# PROJECT_CONTEXT.md

## Purpose
VibeCode Forum is a usable PHP/MySQL community forum for discussions, questions, project showcases, guides, and knowledge sharing.

## Business Logic
The forum is centered around categories, typed topics, tags, posts, likes, bookmarks, subscriptions, notifications, reports, and user roles. Guests read public content. Authenticated users contribute and follow topics. Moderators and administrators keep discussion quality under control through topic moderation and report review.

## Target Users
- Community members who want to ask questions, share projects, and follow useful discussions.
- Topic authors who need to mark a helpful reply as the solution.
- Moderators who need a simple report queue and topic controls.
- Administrators who set up and maintain the forum.

## Core Functions
- Register and log in.
- Create typed/tagged topics and first posts.
- Reply to topics through AJAX.
- Like visible posts through AJAX.
- Bookmark and subscribe to topics.
- Receive and mark in-app notifications.
- Mark a topic as solved with a selected solution post.
- Edit and soft-delete allowed posts.
- Report posts and review reports as moderator/admin.
- Search topics, tags, and visible posts.
- Browse categories, tags, member profiles, and the member directory.
- Edit profile bio/avatar color and change password.
- Pin/unpin and lock/unlock topics as moderator/admin.

## Architecture Overview
The application uses plain PHP pages for views and small JSON API endpoints for AJAX actions. Shared behavior lives in `includes/`. Database access is centralized through PDO in `includes/db.php`. The UI uses one CSS file and one JS file to keep deployment simple.

## Data Flow
1. Browser renders a PHP page.
2. Header emits a CSRF token in a meta tag.
3. JavaScript sends AJAX requests with the CSRF token.
4. API endpoints validate method, session, CSRF, input, and permissions.
5. API endpoints write through PDO prepared statements and return JSON.
6. UI updates the current page or reloads when the state affects multiple areas.

## Security Logic
- `includes/auth.php` starts sessions with `HttpOnly` and `SameSite=Lax`.
- Passwords are stored as `password_hash` values.
- User output is escaped with `e()`.
- State-changing APIs require CSRF validation.
- Moderator/admin actions check `users.role`.
- Soft-deleted posts are blocked from liking, editing, solution marking, and reporting.
- `current_user()` updates `last_seen_at` for online/last-seen status.

## Administrative Logic
There is no separate full admin dashboard yet. Administrative control currently exists through:
- `admin` and `moderator` roles in `users.role`;
- topic controls in `topic.php`;
- report queue in `reports.php`;
- default local admin seed in `install.php`.

## API Logic
API endpoints live in `api/`, return JSON, and terminate via `json_response()` or `json_error()`. Version 1.2.0 adds endpoints for bookmarks/subscriptions, notifications, solved status, post edit/delete, post reports, and report moderation.

## Database Logic
The database schema lives in `sql/schema.sql`. The project uses:
- `users`
- `categories`
- `topics`
- `posts`
- `post_likes`
- `topic_bookmarks`
- `topic_subscriptions`
- `notifications`
- `post_reports`

`install.php` is a fresh-install/reset script and should not be used as a production migration tool.

## Important Technical Decisions
- No heavy framework to keep hosting simple.
- PDO prepared statements for SQL safety.
- jQuery retained because the project already used it for AJAX.
- New forum features were added through focused tables and helpers rather than a full rewrite.
- The UI remains light and readable, with responsive breakpoints for desktop, tablet, mobile, and very narrow screens.

## Constraints
- No automated migrations yet.
- No email sending yet.
- No password reset email yet.
- No production-grade rate limiting yet.
- No full admin dashboard yet.

## Future Direction
Add migrations, full admin dashboard, email verification/reset, optional email notifications, rate limiting, full-text search, automated tests, and GitHub Actions validation.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-21
Copyright: © Beck Sarbassov. All rights reserved.
