# Handling Protocols — Iran Visa Processing System

> This document defines how Claude Code agents handle files, context, failures, multi-tasking, testing, and token efficiency. These protocols apply to every session, every agent, every task.

---

## PROTOCOL 1 — FILE AND FOLDER HANDLING

### 1.1 Naming Conventions

| Type | Convention | Example |
|---|---|---|
| Directories | `snake_case` (CI3 convention) | `task_passports/`, `balance_sheet/` |
| Controller files | `PascalCase.php` | `Tasks.php`, `Balance_sheet.php` |
| Model files | `PascalCase_model.php` | `Task_model.php`, `Ledger_model.php` |
| View files | `snake_case.php` | `task_form.php`, `passport_row.php` |
| Helper files | `snake_case_helper.php` | `jalali_helper.php`, `money_helper.php` |
| Library files | `PascalCase.php` | `Permission.php` |
| JS files | `kebab-case.js` | `task-form.js`, `datatables-init.js` |
| CSS files | `kebab-case.css` | `app.css`, `glass-tokens.css` |
| SQL files | `snake_case.sql` | `visa_system.sql` |
| Environment files | `.env` (never commit) | — |

### 1.2 Folder Creation Rules

- **Never create a folder without putting at least one file in it.** Empty folders are not tracked by git and are meaningless.
- **Follow CI3 conventions strictly**: controllers in `application/controllers/`, models in `application/models/`, views in `application/views/[module]/`. Do not invent new top-level directories inside `application/`.
- **Views are grouped by module**: `application/views/tasks/`, `application/views/accounts/`, etc. Shared partials go in `application/views/_partials/`. Layout templates go in `application/views/_layouts/`.
- **Upload storage**: all passport scans go to `uploads/passports/{task_id}/{uuid}.{ext}` at the project root. The `uploads/` directory is **never committed to git** — add it to `.gitignore`. Protect with `.htaccess` (`Deny from all`).
- **SQL schema**: maintain the single file `database/visa_system.sql`. Every schema change is added to this file as an `ALTER TABLE` statement with a comment and date.

### 1.3 File Size Limits

- If a **Controller** method exceeds **60 lines**, extract logic into the Model.
- If a **Model** file exceeds **300 lines**, split by concern (e.g., `Task_model.php` handles core task CRUD; `Payment_model.php` handles all payment logic separately).
- If a **View** file exceeds **200 lines**, extract repeated HTML blocks into sub-views loaded with `$this->load->view('_partials/passport_row')`.
- If a **JS file** exceeds **200 lines**, split by feature: `task-form.js` handles only the task form; `datatables-init.js` handles only DataTables setup.

### 1.4 What Never Goes Where

- No database calls in Views — data belongs in Models, passed through Controllers.
- No business logic in Controllers — Controllers only: validate input, call Model, pass data to View.
- No raw SQL strings with user input — always use CI3 Query Builder bindings or `$this->db->escape()`.
- No secrets hardcoded anywhere — use `database.php` + `.env` for DB credentials, never commit them.
- No `var_dump()` or `print_r()` in committed code — remove all debug output before committing.
- No direct `$_POST` / `$_GET` access — always use `$this->input->post()` / `$this->input->get()` with XSS cleaning.

---

## PROTOCOL 2 — CONTEXT HANDLING (SESSION LOSS)

> Power outages and network drops are common. Every session must be resumable from zero.

### 2.1 State Preservation Before Every Session

At the start of each working session, Claude Code must:

1. **Read `SESSION_LOG.md`** (in project root) — this is the single source of truth for current state.
2. **Run `git status` and `git log --oneline -10`** to understand what is committed, what is staged, what is dirty.
3. **Read the current phase** from `SESSION_LOG.md` and continue from where it left off.
4. **Never restart from scratch** — always check the log first.

### 2.2 SESSION_LOG.md Format

Maintain this file at the project root. Update it at the end of every working block (every 30 minutes or after completing a task).

```markdown
# Session Log

## Current Phase
Phase 3 — Core Data

## Last Completed Task
✅ Task #15 — Financial Accounts CRUD (list + create/edit modal)
Last commit: feat(accounts): add list page and create/edit modal (abc1234)

## In Progress
🔄 Task #16 — Services CRUD
Files being worked on:
- application/controllers/Services.php (50% done — index + create working, edit not started)
- application/models/Service_model.php (done, committed)
- application/views/services/index.php (done)
- application/views/services/form.php (not started)

## Blocked / Notes
- Check if jalali datepicker re-init fires on newly added passport rows
- Confirm upload directory permissions: chmod 755 uploads/passports/

## Next Up
Task #17 — Receipts CRUD
Task #18 — Users + Roles CRUD

## Environment
- PHP: 7.4 (pre-installed on server, no setup needed)
- Web server: Apache — project root is the subdomain directory (no cd needed)
- DB: MySQL localhost — db: sql_passport_vis
- App URL: https://passport-visa.cyborgtech.co
- Git identity: Sayed Navid Azimi <snavid.dev@gmail.com> (verify before every commit)
- Last schema change: [date] — [description]
```

### 2.3 Context Recovery Sequence

When a session is interrupted (power cut, crash, timeout), follow this sequence exactly on resume:

```
1. git config user.name "Sayed Navid Azimi"                                     → set identity (ALWAYS FIRST)
   git config user.email "snavid.dev@gmail.com"
2. git status                                                                    → see what's uncommitted
3. git stash list                                                                → see if anything is stashed
4. cat SESSION_LOG.md                                                            → read current state
5. find application/ -name "*.php" -exec php -l {} \; | grep -v "No syntax"    → syntax check
6. mysql -u sql_passport_vis -p'dca957546afa6' sql_passport_vis -e "SHOW TABLES;" → verify DB
7. curl -I https://passport-visa.cyborgtech.co/                                 → verify server is up
8. Resume from "In Progress" section of SESSION_LOG.md
```

### 2.4 Mid-Task Checkpointing

For tasks longer than ~20 minutes (e.g. building the full task form), save checkpoints:

- After each logical sub-section is working, **commit it** with a `wip:` prefix: `wip(tasks): task header form functional, starting passport rows`
- WIP commits get squashed before the PR is opened (see GitHub Workflow doc)
- Update `SESSION_LOG.md` after every WIP commit

---

## PROTOCOL 3 — FAILURE AND INTERRUPTION RESUME

### 3.1 Power Outage Protocol

This project is built in an environment with frequent electricity outages. The following rules protect against data loss and broken state.

**Before any long operation, commit what works:**
```bash
git add -A
git commit -m "wip(scope): checkpoint before [operation]"
git push origin dev
```

**Rule: never have more than 30 minutes of uncommitted work.**

If the system shuts off mid-task:
1. On restart, run the context recovery sequence (Protocol 2.3)
2. Check `git status` — anything uncommitted since the last commit is lost. Accept this and rebuild from the last commit.
3. Check Apache + PHP is serving: `curl -I http://localhost/visa-system/`
4. Check if the DB is intact: `mysql -u root -p visa_system -e "SHOW TABLES;"`
5. If a mid-session schema change was interrupted: re-run the specific `ALTER TABLE` statement from `database/visa_system.sql`. MySQL schema changes are not transactional — check each table individually.

### 3.2 Broken App Recovery

If CI3 shows a PHP error on session start:
```bash
# Check syntax on all PHP files in application/
find application/ -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"

# Check Apache error log
tail -50 /var/log/apache2/error.log
# or
tail -50 /var/log/nginx/error.log
```
Fix the **first** error only, test, repeat. Never try to fix multiple PHP errors simultaneously.

### 3.3 Database Recovery

If MySQL is in a bad state after a power cut:
```bash
# Check InnoDB recovery mode
mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G" | grep -A5 "TRANSACTIONS"

# Re-import schema if tables are corrupted (DEV ONLY — destroys data)
mysql -u root -p visa_system < database/visa_system.sql

# Check for incomplete transactions
mysql -u root -p visa_system -e "SELECT * FROM information_schema.INNODB_TRX;"
```

**Production rule:** always use InnoDB tables (not MyISAM) so crash recovery is automatic. Verify all tables are InnoDB:
```sql
SELECT table_name, engine FROM information_schema.tables WHERE table_schema = 'visa_system';
```

### 3.4 Upload File Recovery

If the server restarts and uploaded files are missing from `uploads/`:
- Files are stored on the local filesystem — they survive server restarts unless the disk is wiped
- Check directory permissions: `ls -la uploads/passports/` — should be `755` and owned by the web server user (`www-data`)
- If permissions broke: `chown -R www-data:www-data uploads/ && chmod -R 755 uploads/`
- **Production backup requirement:** set up a daily `rsync` or `cron` job to back up `uploads/` to an external drive or object storage. This is Phase 6 hardening work.

---

## PROTOCOL 4 — MULTI-AGENT AND MULTI-TASK PROTOCOLS

### 4.1 When to Spawn Parallel Agents

Spawn multiple agents **only when tasks have zero shared state**. Good candidates:

| Parallel-safe | NOT parallel-safe |
|---|---|
| Writing 3 different report pages | Writing the task form + its API route (coupled) |
| Building Accounts CRUD + Services CRUD | Two agents touching `schema.prisma` |
| Writing animation variants + CSS tokens | Anything that modifies the DB migration |

### 4.2 Agent Task Assignment Format

When dispatching parallel agents, define each agent's scope explicitly:

```markdown
## Agent A — Scope: Reports Layer
- Files owned: app/(dashboard)/reports/**, app/api/reports/**
- Database: READ ONLY — no schema changes
- Do NOT touch: components/forms/**, prisma/schema.prisma

## Agent B — Scope: UI Components
- Files owned: components/glass/**, components/animations/**, globals.css
- Database: NONE
- Do NOT touch: app/api/**, prisma/**
```

### 4.3 Agent Handoff Protocol

When Agent A completes and hands off to Agent B:

1. Agent A writes a `HANDOFF.md` in the project root summarizing:
   - What was completed
   - What files were changed
   - Any open decisions Agent B needs to make
   - Any TODOs left in code (search for `// TODO`)
2. Agent A commits all changes and pushes to `dev`
3. Agent B reads `HANDOFF.md` before starting
4. Agent B deletes `HANDOFF.md` after reading and committing

### 4.4 Phase Gating

Never start Phase N+1 until Phase N is:
- Fully committed to `dev`
- Build passing (`npm run build` exits 0)
- No TypeScript errors (`npx tsc --noEmit` exits 0)
- PR opened, reviewed, and merged

### 4.5 Conflict Prevention

- **One agent owns one domain.** The owning agent is the only one who edits files in that domain.
- **Schema changes are sequential, never parallel.** Only one agent at a time may modify `prisma/schema.prisma` and run migrations.
- **Shared utilities (`lib/utils`, `lib/date`, `lib/currency`) are written once in Phase 1** and treated as read-only by feature agents.

---

## PROTOCOL 5 — SANDBOX TEST PROTOCOL

Run these checks before opening any PR. All must pass.

### 5.1 PHP Syntax Check
```bash
# Check all PHP files — must produce zero errors
find application/ -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
# Expected output: nothing (silence = all clean)
```

### 5.2 CI3 Error Log Check
```bash
# After any test run, check the CI3 log for PHP errors
tail -50 application/logs/log-$(date +%Y-%m-%d).php
# Expected: no FATAL or ERROR lines
```

Enable CI3 logging during development: in `config.php` set `$config['log_threshold'] = 2;`

### 5.3 Controller Smoke Tests (run manually in browser or curl)

For each new controller, test these cases:

```bash
# Auth guard — must redirect to login (302)
curl -I http://localhost/visa-system/tasks
# Expected: HTTP/1.1 302 Found, Location: .../auth/login

# Permission guard — log in as a role WITHOUT manage_tasks, visit tasks
# Expected: CI3 show_error('دسترسی غیرمجاز', 403)

# Validation failure — submit a form with empty required fields
# Expected: form re-renders with Persian validation errors, no DB write

# Happy path — submit valid form data
# Expected: redirects to list with success flash message, DB row created

# CSRF test — submit a form without the CSRF token
# Expected: CI3 returns 403 "Disallowed Key Characters" or CSRF error
```

Document smoke test results in the PR description.

### 5.4 Database Integrity Checks
```sql
-- After any task payment test, verify all 3 ledger entries were created:
SELECT * FROM ledger_entries WHERE reference = '[task_id]' ORDER BY created_at;
-- Expected: 2 rows (cash + client/vendor account)

-- Verify cash balance moved correctly:
SELECT 
  SUM(debit) - SUM(credit) AS balance, 
  currency 
FROM ledger_entries 
WHERE account_id = [cash_account_id]
GROUP BY currency;

-- Verify no orphaned passport rows:
SELECT p.* FROM task_passports p
LEFT JOIN tasks t ON t.id = p.task_id
WHERE t.id IS NULL;
-- Expected: empty result
```

### 5.5 AJAX Endpoint Test
For any controller method that returns JSON:
```bash
# Without session cookie — must return JSON error, not HTML login page
curl -X POST http://localhost/visa-system/tasks/add_client_payment \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "task_id=1&amount=100&currency=AFN"
# Expected: {"success":false,"error":"unauthorized"} — NOT an HTML redirect
```

### 5.6 Regression Checklist (run after any schema change)

- [ ] Login works and session persists across pages
- [ ] Task create with 1 passport works end to end
- [ ] File upload saves to `uploads/passports/` and is retrievable via `/uploads/[filename]`
- [ ] Client payment posts 2 ledger entries (cash + client account)
- [ ] Vendor payment posts 2 ledger entries (cash + vendor account)
- [ ] Balance sheet shows correct per-currency balances
- [ ] Deleting a payment reverses the ledger entries correctly
- [ ] CSRF token is present in every form

---

## PROTOCOL 6 — VISUAL TEST PROTOCOL

Every UI feature must pass all of the following before the PR is opened.

### 6.1 Viewport Tests

Test each new page or component at these viewports (use browser DevTools device emulation):

| Device | Width | Priority |
|---|---|---|
| iPhone SE | 375px | **Required** |
| iPhone 14 | 390px | **Required** |
| iPad | 768px | Required |
| Desktop | 1280px | Required |
| Wide desktop | 1440px | Nice to have |

Document any layout breaks as GitHub Issues before merging.

### 6.2 RTL Visual Checks

- All text is right-aligned by default
- Icons and decorative elements are mirrored correctly (e.g., arrow pointing right in LTR should point left in RTL)
- Sidebar is on the right side
- Form fields stack right-to-left
- Number inputs show digits left-to-right (numbers are not mirrored in RTL)
- Table columns flow from right to left

### 6.3 Animation Quality Checks

For every animated component, verify:

- [ ] Enter animation plays on first render
- [ ] Exit animation plays when component unmounts (`AnimatePresence` is present)
- [ ] No layout shift (CLS = 0) during animation
- [ ] Animation respects `prefers-reduced-motion` (disable animations and check)
- [ ] Animation feels snappy on low-end device simulation (CPU 4x slowdown in DevTools)

### 6.4 Glass Effect Checks

- [ ] `backdrop-filter: blur()` is visible (requires a non-white background behind the panel)
- [ ] Glass border is visible with a subtle luminous edge
- [ ] Box shadow creates depth
- [ ] Panel is legible — text contrast ratio ≥ 4.5:1 against the glass background

### 6.5 Screenshot Evidence

For each completed phase, take screenshots at 375px and 1280px and attach them to the PR. Name them: `[phase]-[feature]-[viewport].png` — e.g. `phase2-accounts-375.png`.

---

## PROTOCOL 7 — TOKEN OPTIMIZATION PROTOCOL

> Goal: get maximum quality output with minimum token usage. These rules keep sessions lean and context windows efficient.

### 7.1 Context Loading Rules

**Only load what the current task needs.** When starting a task:

- Read only the files directly relevant to the task. Do not read the entire project.
- Use `grep` to find the specific function or component rather than reading whole files.
- Use `git diff HEAD~1` to understand recent changes rather than re-reading files.

**Standard context load for a feature task (load in this order, stop when you have enough):**
1. `SESSION_LOG.md` — current state (always)
2. The specific Controller file being worked on
3. The relevant Model file(s)
4. The relevant View file(s) only if the bug/feature is in the UI
5. Nothing else unless a specific error requires it — do NOT pre-load the entire `application/` tree

### 7.2 Code Generation Efficiency

- **Never regenerate a working file.** Edit files in-place with surgical edits.
- **Reuse GSAP animation patterns from `main.js`** — `animateRowIn()` and `animateRowOut()` are already defined; call them, don't rewrite.
- **Reuse the `.glass-panel` / `.glass-card` CSS classes** — never write raw `backdrop-filter` in a new view file.
- **Reuse `$this->render()` from `MY_Controller`** — never call `$this->load->view()` directly in a controller (use the base method).
- Before writing any helper function, grep for it: `grep -r "format_money" application/helpers/` — it may already exist.

### 7.3 Prompt Efficiency Rules

When asking Claude Code to do something in a session:

- **One task at a time.** Give one clear instruction, wait for it to complete, then give the next.
- **Reference the implementation doc** rather than re-explaining the entire system: "Build Phase 3 Task #16 per implementation.md."
- **Reference the handling protocols** for test requirements: "Run Protocol 5 checks and document results."
- **Use the SESSION_LOG** to resume: "Read SESSION_LOG.md and continue from where we left off."

### 7.4 Output Compression

When reviewing or generating reports during a session:

- Request summaries, not full file dumps: "Summarize the changes in the last 3 commits" not "Show me every file."
- Request diffs, not full files: "Show me only the changed lines in task-form.tsx" not "Show me the whole file."
- Request specific sections: "Show me only the `POST` handler in `app/api/tasks/route.ts`."

### 7.5 Token Budget Per Phase

Aim to complete each phase within the following rough budget. If you are approaching the limit and the phase is not done, **commit what works, update SESSION_LOG.md, and start a new session.**

| Phase | Estimated Token Budget |
|---|---|
| Phase 1 — Scaffold & Foundation | ~60,000 tokens |
| Phase 2 — Auth + Users + Roles | ~70,000 tokens |
| Phase 3 — Core Data (Accounts, Services, Receipts) | ~90,000 tokens |
| Phase 4 — Tasks (core feature) | ~180,000 tokens |
| Phase 5 — Balance Sheet + 10 Reports | ~130,000 tokens |
| Phase 6 — Polish & Hardening | ~60,000 tokens |

A million-token context window is a tool, not a license to be wasteful. Staying lean keeps the model's attention sharp.

### 7.6 The "Load What You Prove You Need" Rule

Before reading any file, ask: "Do I have a specific error or question that requires seeing this file?"

- **Yes** → read it
- **No** → don't read it

This single rule saves more tokens than any other optimization.
