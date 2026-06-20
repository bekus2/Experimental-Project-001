# AI_RULES.md

## Required Reading Before Changes
Before editing code, read:
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

If a file is missing, create it or document why it cannot be created.

## Architecture Rules
- Keep the project plain PHP unless there is a clear approved reason to add a framework.
- Keep shared logic in `includes/`.
- Keep AJAX endpoints in `api/`.
- Keep database schema in `sql/schema.sql`.
- Keep public pages in the project root.
- Reuse existing helpers before adding new abstractions.
- Do not rewrite the whole project without explicit approval.

## Coding Style Rules
- Use strict PHP types.
- Use PDO prepared statements for database writes and reads with user input.
- Escape output with `e()` or equivalent safe helpers.
- Keep comments concise and useful.
- New source files must include the Beck Sarbassov project header.
- Keep JS behavior in `assets/js/app.js` unless a split becomes clearly necessary.
- Keep CSS responsive and avoid text overflow in action controls.

## Security Rules
- Never expose production secrets, API keys, tokens, or real passwords.
- Do not place real credentials in public frontend code.
- Default local credentials are allowed only as documented development credentials.
- Use CSRF validation for write APIs.
- Check authentication before user-only actions.
- Check `admin` or `moderator` role before moderation/report review actions.
- Check topic-author permission before solution changes by non-moderators.
- Hash passwords with `password_hash`.
- Verify current password before password changes.
- Do not allow deleted posts to be liked, edited, solved, or reported.
- Remove or block `install.php` in production.

## Documentation Rules
Update relevant docs after meaningful changes:
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

Do not rewrite all documentation unnecessarily; update the sections affected by the work. When version behavior changes, keep version numbers and dates in sync.

## Forbidden Actions
- Do not delete working features without explanation.
- Do not introduce unnecessary dependencies.
- Do not bypass CSRF/auth/role checks.
- Do not break existing login, registration, topic, reply, like, search, profile, or moderation flows.
- Do not commit unrelated user changes.
- Do not publish default credentials anywhere except documentation for local initial setup.

## Git And GitHub Workflow
- Understand current branch and changed files before staging.
- Prefer feature/fix/docs branch names for team work, but direct `main` publication is allowed when the user explicitly asks for it.
- Keep commit messages concise and descriptive.
- Push only intended changes.
- Update documentation in the same commit as related code changes.
- Verify remote `main` after push when publishing directly.

## Testing Rules
Before publishing, run:
- PHP lint on changed PHP files or all PHP files.
- Local installer/schema smoke test when schema changes.
- Local HTTP smoke test.
- Authentication smoke test.
- Feature smoke tests for changed AJAX endpoints where practical.
- Browser check for visible UI changes on desktop and mobile widths.
- `git diff --check` before staging/commit.

## Final Report Format
After an update, report:
1. What changed.
2. Which files changed.
3. How to run/test.
4. Security measures added or preserved.
5. Documentation updated.
6. Remaining improvements.
7. Risks or limitations.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-21
Copyright: © Beck Sarbassov. All rights reserved.
