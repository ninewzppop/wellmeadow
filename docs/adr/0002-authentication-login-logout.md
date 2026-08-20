# ADR-0002: Session-based login/logout on the existing `users` table

- **Date**: 2026-08-20
- **Status**: Accepted

## Context

The app (Laravel + Sail) had no authentication. All pages (staff, search,
allocations, ward report) were publicly reachable. The standard Laravel
`users` table and `User` model (Authenticatable, `password` hashed) already
existed. Requirement: gate the site behind login while reusing the existing
`users` table — no new tables, no schema/migration changes — and keep the
existing staff UI/theme untouched.

## Decision

- Use the standard Laravel session guard (`web`, eloquent provider → `User`)
  already configured in `config/auth.php`. No new guard or provider.
- Add routes: `GET /login` (`login`), `POST /login` (`login.submit`),
  `POST /logout` (`logout`).
- New `AuthController` with `showLoginForm()` (view `auth.login`) and
  `login()` using `Auth::attempt(['email' => ..., 'password' => ...])`,
  `Auth::logout()` + session regeneration on logout.
- Login view extends the existing `layouts.app` and reuses its Tailwind
  (slate/sky) theme — no new template.
- Successful login → `redirect()->intended(route('staff.index'))`.
- Failed login → back to form with an error message.
- `/login` is guarded by `guest`: already-authenticated users are redirected
  to `staff.index`.
- All protected routes (staff resource, staff.search, allocations, wards.report)
  get the `auth` middleware. `/` (redirect to staff.index) is covered via
  `staff.index`.
- Logout button added to the shared nav in `layouts.app` (POST form + `@csrf`),
  styled with the existing nav classes. Logout → redirect to `route('login')`.
- Seed one test user in the existing `users` table via `php artisan tinker`:
  name `Test User`, email `test@example.com`, password `password123`
  (`Hash::make`).

## Rationale

- Standard `Auth` keeps the change minimal and reviewable, and satisfies the
  "no custom auth logic" constraint.
- Reusing the existing theme/layout honours the "don't redesign the site"
  requirement while still giving every page a consistent look.
- `redirect()->intended()` lets a user return to the page they originally
  requested after login.

## Consequences

- `users` table stays schema-identical; only a new row is inserted.
- Every page in the system requires a logged-in session.
- Shared layout gains one logout item in the nav.