# Session Log

## Current Phase
Phase 2 — Auth + Users + Roles ✅ COMPLETE

## Last Completed Task
✅ Phase 2 — Users + Roles CRUD with RBAC, verified live

## What was built (Phase 2)
- Role_model: get_all (w/ permission_count + user_count subqueries, no N+1),
  get_by_id, get_permission_ids, get_all_permissions, create/update with atomic
  permission sync (trans), delete, is_in_use guard, name_exists
- Roles controller (gated manage_roles): index/create/store/edit/update/delete,
  validation, name-unique + in-use delete guard
- Roles views: index (DataTable + delete guard for in-use roles),
  form (permission checkbox grid + select-all toggle)
- User_model extended: get_all (role join), get_by_id, create (bcrypt hash),
  update (re-hash only if pw provided), delete, username_exists, count_active
- Users controller (gated manage_users): full CRUD, self-delete + self-deactivate
  guards, username-unique guard, password optional on edit
- Users views: index (DataTable, status badges, self-row protected),
  form (role select2, active switch, pw hint on edit)
- Persian validation language override:
  application/language/english/form_validation_lang.php (UI stays Persian-only)

## Verified (Phase 2)
- Auth guard: /users unauthed → 302 login
- Permission guard: operator (no manage_users/roles) → 403 on /users & /roles, 200 dashboard
- Sidebar hides admin section for users lacking the perms
- Role create (happy path) → row + permissions persisted
- User create (happy path) → bcrypt-hashed row persisted
- Validation failure → form re-renders with PERSIAN errors, no DB write
- Self-delete blocked (admin id=1 survives)
- php -l clean on all new files; CI log clean (no new errors)
- Test data cleaned up → back to 1 user (admin) + 1 role (مدیر کل)

---

## Phase 1 — Scaffold & Foundation ✅ COMPLETE (earlier)
✅ full foundation built and verified live on https://passport-visa.cyborgtech.co

## What was built (Phase 1)
- CodeIgniter 3.1.13 installed (system/, application/, index.php)
- `.env` loader in index.php + `env()` helper (putenv() is disabled in php-fpm,
  so config reads from $_ENV/$_SERVER via env())
- config.php: base_url from env, clean URLs, DB sessions, CSRF on, secure cookies,
  encryption key, log_threshold (1 prod / 4 dev)
- database.php: env-driven mysqli, utf8mb4, stricton
- autoload.php: database, session, form_validation + url/form/html/security helpers
- routes.php: default_controller=dashboard, login/logout/uploads routes
- constants.php: CURRENCIES, ACCOUNT_TYPES, TASK_STATUSES, PAYMENT_STATUSES, upload + pagination consts
- database/visa_system.sql: 12 tables (incl. ci_sessions), FKs, indexes, seed data — IMPORTED
- MY_Controller (auth guard + RBAC + render + JSON helpers)
- User_model (get_with_permissions, get_by_username)
- helpers: jalali_helper (Gregorian↔Jalali, verified), money_helper (bcmath, scale 2)
- libraries/Permission.php (has/has_any/require_permission)
- Layouts: _layouts/main.php (sidebar shell), _layouts/auth.php
- Partials: sidebar (permission-aware), topbar, alerts, pagination
- Assets: glass-tokens.css, app.css (glass system + RTL + responsive),
  main.js (GSAP/AOS/CSRF/sidebar/row anims), select2-init.js, datatables-init.js
  — all vendor CDN libs pinned with real SRI hashes
- Auth controller (functional login/logout) + Dashboard controller
- Views: auth/login.php, dashboard/index.php
- .htaccess (clean URLs + deny sensitive files), uploads/.htaccess (deny all), .gitignore

## Verified
- GET / → 302 → /login
- GET /login → 200 (glass login page renders, CSRF present)
- POST /login (admin / admin@1234) → 302 → /dashboard, DB session persisted (ci_sessions row)
- GET /dashboard (authed) → 200, renders shell + KPI placeholders
- GET /dashboard (unauthed) → 302 → /login
- GET /logout → 302 → /login
- php -l on all application/*.php → clean
- CI log clean after fix (all errors are pre-fix, ts 23:50:45)

## Default Login
- username: admin
- password: admin@1234  (CHANGE after first login — Phase 2)

## Blocked / Notes
- ⚠️ `fileinfo` extension (finfo_file) is NOT installed in PHP 7.4 — REQUIRED for
  secure upload MIME validation in Phase 4. Enable via aaPanel PHP extension manager
  before Phase 4 (Tasks / passport scan uploads).
- `putenv` is disabled in php-fpm (security). All env reads go through env().
- MySQL is 5.7.44 (not 8.0). Socket /tmp/mysql.sock; CLI must use -h 127.0.0.1.
- Web user is `www`; project owned navid:www, setgid, group-writable.

## Next Up
Phase 3 — Core Data
- Task #15 Accounts CRUD + Account_model (DataTables list, create/edit, delete)
- Task #16 Services CRUD + Service_model
- Task #17 Receipts CRUD + Receipt_model + Ledger_model (posts to ledger)

## Environment
- PHP: 7.4.33 (php-fpm as www; CLI separate ini)
- Web server: Apache (docroot = project root), mod_rewrite on
- DB: MySQL 5.7.44 — db sql_passport_vis (localhost works for PHP; CLI use 127.0.0.1)
- App URL: https://passport-visa.cyborgtech.co
- Git identity: Sayed Navid Azimi <snavid.dev@gmail.com>
- Last schema change: 2026-06-22 — initial schema (Phase 1)
