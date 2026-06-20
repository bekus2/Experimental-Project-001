# TASK.md

## Project Goal
Build and maintain a usable, secure, responsive PHP/MySQL community forum that can run locally, be published to GitHub, and be extended without a full rewrite.

## Current Version
1.2.0

## Required Functionality
- Public forum homepage.
- Category and tag browsing.
- Topic creation with category, type, and tags.
- Topic replies.
- AJAX likes.
- Live search across topics, tags, and visible posts.
- User registration and login.
- User profile pages and member directory.
- Profile settings and password change.
- Bookmarks and topic subscriptions.
- In-app notifications.
- Solved-topic workflow.
- Post editing and soft deletion.
- Post reports and moderator report queue.
- Topic filters and sorting.
- Moderator/admin topic controls.

## Pages And Modules
- `index.php` - homepage, dashboard, filters, categories, topic list.
- `category.php` - category topic list with pagination and sorting.
- `topic.php` - topic detail, posts, likes, reply form, solution, bookmarks, subscriptions, post actions.
- `new-topic.php` - create topic form with draft autosave.
- `profile.php` - public profile and activity.
- `members.php` - member directory.
- `bookmarks.php` - saved topics.
- `notifications.php` - notification center.
- `reports.php` - moderator report queue.
- `tag.php` - tag topic list.
- `settings.php` - profile settings and password change.
- `login.php` - login page.
- `register.php` - registration page.
- `search.php` - search page.
- `api/` - AJAX backend endpoints.

## User Roles
- Guest: read forum content, tags, members, profiles, and search.
- Member: create topics, reply, like, bookmark, subscribe, report, edit own posts, edit profile, change password.
- Topic author: member permissions plus mark/clear solution for own topic.
- Moderator: member permissions plus topic controls, report review, edit/delete posts.
- Admin: moderator permissions and initial owner access.

## Admin Requirements
- Default local admin email/login: `bek0435@gmail.com`.
- Default local admin username: `beck_admin`.
- Default local admin password: `0123456789+Aa`.
- The default password must be changed immediately after first login.
- Credentials must not be displayed on public pages.

## UI Requirements
- Light responsive design.
- Usable on mobile, tablet, laptop, and desktop.
- Clear topic type, tag, solved, pinned, and locked states.
- Clear forms and validation messages.
- Mobile-safe action rows for post tools, topic tools, reports, and notifications.
- No text overflow in buttons or panels.

## Backend Requirements
- PHP 8.1+.
- PDO prepared statements.
- CSRF protection for write APIs.
- Session-based authentication.
- Role checks for moderator actions.
- Server-side validation for all state-changing actions.

## API Requirements
- JSON responses.
- Consistent success/error format.
- Input validation.
- Permission checks before changes.
- No state-changing action without CSRF.

## Data Storage Requirements
- MySQL/MariaDB.
- Tables: `users`, `categories`, `topics`, `posts`, `post_likes`, `topic_bookmarks`, `topic_subscriptions`, `notifications`, `post_reports`.
- Password hashes only; no production plaintext secrets.

## Form Requirements
- Server-side validation required.
- Client-side AJAX feedback for main flows.
- CSRF token required for write operations.
- Draft autosave for topic/reply textareas where enabled.

## Email And Notifications
In-app notifications are implemented in v1.2.0. Email sending is not implemented yet. Future SMTP credentials must be stored in environment variables or protected server configuration.

## SEO Requirements
- Semantic HTML.
- Page titles per view.
- Meta description in the shared header.
- Clean internal links for topics, categories, tags, and profiles.

## Performance Requirements
- Keep dependencies minimal.
- Avoid unnecessary framework overhead.
- Limit homepage topic/member queries.
- Add full-text search later for larger data sets.

## Security Requirements
- Change default admin password before production.
- Remove or block `install.php` in production.
- Protect service folders from public web access.
- Use server-side role checks for all moderation/report actions.
- Add rate limiting in future production hardening.

## Deployment Requirements
- PHP-capable hosting.
- MySQL/MariaDB database.
- Protected configuration and service folders.
- Environment variables for credentials where possible.
- Migration system before production upgrades on real data.

## Acceptance Criteria
- Project runs locally.
- Installer creates schema and seed accounts/topics.
- Login works with default local admin.
- Homepage/category filters work.
- Topic creation supports type and tags.
- Replies create notifications for subscribers.
- Bookmarks and subscriptions toggle.
- Solved status can be set/cleared by topic author or moderator.
- Post edit, soft delete, report, and report moderation work.
- Member directory, notification center, bookmarks, and tag pages load.
- UI is lighter and responsive on desktop/tablet/mobile/narrow screens.
- Documentation is current.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-21
Copyright: © Beck Sarbassov. All rights reserved.
