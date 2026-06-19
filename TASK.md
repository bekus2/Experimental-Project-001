# TASK.md

## Project Goal
Build and maintain a usable, secure, responsive PHP/MySQL community forum that can run locally, be published to GitHub, and be extended without a full rewrite.

## Current Version
1.1.0

## Required Functionality
- Public forum homepage.
- Category browsing.
- Topic creation.
- Topic replies.
- AJAX likes.
- Live search.
- User registration and login.
- User profile pages.
- Profile settings.
- Password change.
- Topic filters and sorting.
- Moderator/admin topic controls.

## Pages And Modules
- `index.php` - homepage, dashboard, filters, categories, topic list.
- `category.php` - category topic list with pagination and sorting.
- `topic.php` - topic detail, posts, likes, reply form, moderation panel.
- `new-topic.php` - create topic form.
- `profile.php` - public profile.
- `settings.php` - profile settings and password change.
- `login.php` - login page.
- `register.php` - registration page.
- `search.php` - search page.
- `api/` - AJAX backend endpoints.

## User Roles
- Guest: read forum content and search.
- Member: create topics, reply, like, edit profile, change password.
- Moderator: member permissions plus pin/unpin and lock/unlock topics.
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
- Clear topic status tags.
- Clear forms and validation messages.
- No text overflow in buttons or panels.

## Backend Requirements
- PHP 8.1+.
- PDO prepared statements.
- CSRF protection for write APIs.
- Session-based authentication.
- Role checks for moderator actions.

## API Requirements
- JSON responses.
- Consistent success/error format.
- Input validation.
- Permission checks before changes.

## Data Storage Requirements
- MySQL/MariaDB.
- Tables: `users`, `categories`, `topics`, `posts`, `post_likes`.
- Password hashes only; no production plaintext secrets.

## Form Requirements
- Server-side validation required.
- Client-side AJAX feedback for main flows.
- CSRF token required for write operations.

## Email And Notifications
Email sending is not implemented in v1.1.0. Future SMTP credentials must be stored in environment variables or protected server configuration.

## SEO Requirements
- Semantic HTML.
- Page titles per view.
- Meta description in the shared header.
- Clean internal links.

## Performance Requirements
- Keep dependencies minimal.
- Avoid unnecessary framework overhead.
- Limit homepage topic/member queries.

## Security Requirements
- Change default admin password before production.
- Remove or block `install.php` in production.
- Protect service folders from public web access.
- Add rate limiting in future production hardening.

## Deployment Requirements
- PHP-capable hosting.
- MySQL/MariaDB database.
- Protected configuration and service folders.
- Environment variables for credentials where possible.

## Acceptance Criteria
- Project runs locally.
- Installer creates schema and seed accounts.
- Login works with default local admin.
- Homepage filters work.
- Category sorting works.
- Profile settings save.
- Password change works.
- Moderator controls update topic state.
- UI is lighter than the original dark theme.
- Documentation is current.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-19
Copyright: © Beck Sarbassov. All rights reserved.
