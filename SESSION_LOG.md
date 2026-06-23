# Session Log

## Current Phase
Phase 4 — Tasks (core feature) ✅ COMPLETE

## Last Completed Task
✅ Phase 4 — Tasks with passports, uploads, payments + ledger, verified live

## What was built (Phase 4)
- Task_model (filters + server-side pagination, joins, create/update,
  delete with ledger reversal), Passport_model (row sync, scan paths),
  Payment_model (client+vendor payments, transactional ledger posting per
  Part 7, precise per-payment reversal).
- Tasks controller (manage_tasks): index w/ filter bar + CI pagination,
  create/store, edit/update, delete (+file cleanup), view, AJAX
  add/delete client+vendor payments.
- Uploads controller: auth-gated passport-scan serving (served via /scan/...).
- upload_helper.php: content-based validation (getimagesize for images +
  %PDF magic bytes — no fileinfo needed), GD resize to 1600px + JPEG 80%.
- Views: tasks/index (filters), tasks/form (header + financial + dynamic
  passport rows + per-row upload), tasks/view (passports, payment logs,
  per-currency outstanding). task-form.js (dynamic rows, reindex, datepicker
  re-init, GSAP, fee auto-calc), task-payments.js (AJAX payments).

## Key fixes this phase
- Scan serving URL must NOT collide with the physical /uploads path or Apache
  serves it statically (then uploads/.htaccess denies). Route changed to
  /scan/{task_id}/{file} → Uploads::passport (auth-checked).

## Verified (Phase 4)
- Task create (multipart) w/ passport + scan: dates 1404/04/15→2025-07-06,
  dob 1370/05/10→1991-08-01; image resized 1800→1600px, compressed
- Scan serving: 200 w/ session, 302→login without (auth gated)
- Client payment 1000 AFN → 2 ledger rows (cash debit + client credit)
- Vendor payment 800 AFN → 2 ledger rows (cash credit + vendor debit)
- Balances: cash 200, client -1000, vendor 800 (all correct)
- Payment delete → exact 2-row reversal; task delete → passports+payments+
  ledger+scan-dir all removed
- php -l clean; CI log clean (only external bot 404s)

## ⚠️ Outstanding env item
- fileinfo STILL not installed. Uploads work without it (getimagesize +
  magic bytes), but enabling fileinfo is recommended hardening for Phase 6.

---

## Phase 3 — Core Data ✅ COMPLETE (earlier)
✅ Accounts, Services, Receipts CRUD + ledger posting, verified live

## What was built (Phase 3)
- Account_model + Accounts controller (manage_accounts): AJAX modal CRUD,
  cash-account + in-use delete guards. Views: index DataTable + modal.
- Service_model + Services controller (manage_services): AJAX modal CRUD,
  in-use delete guard, format_money display. Views: index + modal.
- Ledger_model: post_entries (transactional batch), delete_by_source (reversal),
  get_account_balance / get_account_balances (bcmath, per-currency).
- Receipt_model: receipts = single ledger entries (source='receipt'),
  debit/credit split, CRUD scoped to source='receipt'.
- Receipts controller (manage_receipts): AJAX modal CRUD, Jalali↔Gregorian
  date conversion, get() shapes row into form fields.
- Reusable assets/js/crud-modal.js — generic AJAX create/edit modal
  (used by accounts, services, receipts).
- Jalali datepicker added to layout (@majidh1/jalalidatepicker, SRI pinned),
  init in main.js (Western digits), inputs use [data-jdp].

## Key fix this phase
- MY_Controller::json_response() rewritten to echo+exit (CI3's
  output->set_output() is dropped when you exit before the end-of-request
  flush, so AJAX bodies were empty). Now all AJAX endpoints return real bodies.

## Verified (Phase 3)
- Accounts: AJAX create 200 {success:true}, edit get JSON, validation 422 (Persian),
  cash delete-protected, in-use guard
- Services: AJAX create persisted (fee 1500 AFN)
- Receipts: create → ledger row (debit 5000 AFN, date 1404/04/02→2025-06-23 ✓),
  balance 5000; update → credit 1200 USD (1404/05/10→2025-08-01 ✓);
  delete → ledger reversed to 0
- php -l clean on all files; CI log clean (no new errors)

## Current seed/data state
- accounts: 1 (صندوق نقدی / cash)
- services: 1 (ویزای توریستی ایران, 1500 AFN) — kept as sample
- ledger: 0 · users: 1 (admin) · roles: 1 (مدیر کل)

---

## Phase 2 — Auth + Users + Roles ✅ COMPLETE (earlier)
✅ Users + Roles CRUD with RBAC, verified live

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
Phase 5 — Balance Sheet + 10 Reports
- Balance_sheet controller (per-currency balances from ledger GROUP BY)
- Report_model + Reports controller (10 reports): balance sheet,
  account statement, cash position, income/expense, tasks, profit,
  outstanding, client, vendor, passport/visa volume
- Report query caching + EXPLAIN index checks

## Environment
- PHP: 7.4.33 (php-fpm as www; CLI separate ini)
- Web server: Apache (docroot = project root), mod_rewrite on
- DB: MySQL 5.7.44 — db sql_passport_vis (localhost works for PHP; CLI use 127.0.0.1)
- App URL: https://passport-visa.cyborgtech.co
- Git identity: Sayed Navid Azimi <snavid.dev@gmail.com>
- Last schema change: 2026-06-22 — initial schema (Phase 1)
