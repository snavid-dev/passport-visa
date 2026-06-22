# GitHub Workflow — Iran Visa Processing System

> Every line of code goes through this workflow. No exceptions. No direct pushes to `prod`.

---

## 0. NON-NEGOTIABLE GIT IDENTITY RULE

**Claude must NEVER appear as a git author or committer. Every commit must be authored by Sayed Navid Azimi (snavid-dev). This is not optional.**

Run these commands once at the very start of every Claude Code session, before any git operation:

```bash
git config user.name  "Sayed Navid Azimi"
git config user.email "snavid.dev@gmail.com"
```

Verify identity is set correctly before any commit:
```bash
git config user.name   # must print: Sayed Navid Azimi
git config user.email  # must print: snavid.dev@gmail.com
```

If `git log` ever shows a commit by "Claude" or any Anthropic-related identity, it must be amended immediately:
```bash
git commit --amend --author="Sayed Navid Azimi <snavid.dev@gmail.com>" --no-edit
git push --force-with-lease origin [branch-name]
```

**How Claude pushes without appearing as a contributor:**
Pushes are authenticated via the `GITHUB_TOKEN` in `.env`. The token belongs to the `snavid-dev` GitHub account, so the push is recorded as an action by `snavid-dev`. The git commit author (set above) is also `snavid-dev`. Claude is invisible in all GitHub records.

---

## 1. REPOSITORY SETUP

### 1.1 Repo Details

The private GitHub repository already exists:
- **URL:** `https://github.com/snavid-dev/passport-visa.git`
- **Owner:** snavid-dev (Sayed Navid Azimi)
- **Visibility:** Private

### 1.2 Initial Local Setup

```bash
# Set git identity FIRST — always
git config user.name  "Sayed Navid Azimi"
git config user.email "snavid.dev@gmail.com"

# Configure authenticated remote using token from .env
# Read token from .env
GITHUB_TOKEN=$(grep GITHUB_TOKEN .env | cut -d '=' -f2)
git remote add origin https://snavid-dev:${GITHUB_TOKEN}@github.com/snavid-dev/passport-visa.git

# Initial commit
git init
git add -A
git commit -m "chore: initial project scaffold"
git branch -M main
git push -u origin main
```

### 1.3 Create the Two Permanent Branches

```bash
# Create dev branch (all work happens here)
git checkout -b dev
git push -u origin dev

# Create prod branch (only receives promotions from dev)
git checkout -b prod
git push -u origin prod

# Return to dev to start working
git checkout dev
```

### 1.4 Branch Protection Rules

Configure these in GitHub → Settings → Branches:

**For `prod`:**
- Require a pull request before merging
- Require at least 1 approval
- Require status checks to pass before merging (add: `php-syntax`, `regression-checklist`)
- Do not allow bypassing the above settings
- Restrict who can push: **nobody** — only merges via PR

**For `dev`:**
- Require a pull request before merging
- Require status checks to pass: `php-syntax`
- Allow bypassing for: repository admin only (for emergency fixes)

---

## 2. BRANCH NAMING CONVENTION

All work happens in feature branches cut from `dev`.

```
[type]/[short-description]

Types:
  feat/     — new feature
  fix/      — bug fix
  chore/    — tooling, config, dependencies
  refactor/ — code restructure, no behavior change
  style/    — CSS/animation changes only
  perf/     — performance improvement
  test/     — adding or fixing tests
  docs/     — documentation only

Examples:
  feat/task-form-passport-rows
  fix/ledger-double-posting-bug
  feat/phase-1-foundation
  chore/add-bundle-analyzer
  style/glass-panel-mobile-spacing
  fix/jalali-date-off-by-one
```

---

## 3. DEVELOPMENT WORKFLOW (STEP BY STEP)

### Step 1 — Cut a Feature Branch

```bash
git checkout dev
git pull origin dev          # always pull latest before cutting a branch
git checkout -b feat/[description]
```

### Step 2 — Work and Checkpoint-Commit

Work in small logical increments. Commit often. Use `wip:` prefix for intermediate saves:

```bash
git add -A
git commit -m "wip(tasks): task header form renders correctly"
# ... continue working ...
git commit -m "wip(tasks): dynamic passport rows add/remove working"
# ... continue working ...
git commit -m "wip(tasks): file upload validated and saving"
git push origin feat/[description]   # push after every WIP commit (power outage safety)
```

### Step 3 — Squash WIP Commits Before PR

Once the feature is complete, clean up WIP commits into meaningful ones:

```bash
# Count how many commits to squash (e.g., last 5)
git log --oneline -10

# Interactive rebase to squash
git rebase -i HEAD~5
# In the editor: keep the first as 'pick', change others to 'squash' (s)
# Write a clean commit message for the squashed result

# Force push the cleaned branch
git push --force-with-lease origin feat/[description]
```

**Final commit message format:**
```
feat(tasks): add dynamic passport rows with file upload

- Dynamic add/remove passport rows
- File upload with sharp compression (max 1600px, 80% quality)
- Re-init Jalali datepicker on each new row
- Validates file type (JPEG/PNG/PDF) and size (max 5MB)
- Stores scan path in task_passports table

Closes #42
```

### Step 4 — Open a Pull Request

Go to GitHub → Pull Requests → New Pull Request:

- **Base branch:** `dev`
- **Compare branch:** `feat/[your-branch]`
- **Title:** same as your final commit message (without the body)
- **Description:** fill in the PR template (see Section 5)
- **Assignee:** yourself
- **Labels:** add relevant labels (`feature`, `bug`, `phase-1`, etc.)
- **Linked issues:** link any issues this PR closes ("Closes #42")

### Step 5 — Review the PR on GitHub

Open the PR on GitHub and do a self-review:

1. Read every changed file in the **Files changed** tab
2. Check: does the code match the implementation.md rules?
3. Check: are the handling protocols followed?
4. Check: is anything missing (loading state, error state, mobile view)?
5. Add **review comments** on any line that needs fixing
6. If issues are found: do NOT approve — go to Step 6

### Step 6 — Fix Issues Found in Review

If you find problems during review:

```bash
# On your feature branch:
git checkout feat/[description]

# Fix the issue
# ... edit files ...

git add -A
git commit -m "fix(tasks): correct passport row removal cleanup"
git push origin feat/[description]
```

Then on GitHub:
1. Reply to your own review comment explaining what was fixed
2. Mark the review comment as **Resolved**
3. Open a GitHub Issue for any problem that is **not fixed in this PR** (technical debt, discovered bugs): see Section 6

### Step 7 — Approve and Merge to Dev

Once all review comments are resolved:

1. On the PR page, click **"Review changes"** → **"Approve"** → **"Submit review"**
2. Click **"Squash and merge"** (not "Create a merge commit" or "Rebase and merge")
3. Confirm the merge commit message is clean
4. Click **"Confirm squash and merge"**
5. Delete the feature branch (GitHub shows a button — click it)

```bash
# Clean up locally
git checkout dev
git pull origin dev
git branch -d feat/[description]
```

---

## 4. PROMOTING DEV TO PROD

Promotions happen **after each phase is complete and verified**, not after individual features.

### 4.1 Pre-Promotion Checklist

Before opening a `dev → prod` PR, verify:

- [ ] PHP syntax check passes on all files in `application/`
- [ ] CI3 error log is clean — no FATAL or ERROR entries
- [ ] Full regression checklist from `handling_protocols.md` Protocol 5.6 has passed
- [ ] All open issues labeled `blocking` are closed
- [ ] `SESSION_LOG.md` is up to date
- [ ] No debug output (`var_dump`, `print_r`, `die()`) left in any PHP file
- [ ] No hardcoded localhost URLs or dev credentials in any file
- [ ] Screenshots attached showing mobile + desktop views

### 4.2 Open the Promotion PR

```bash
# Make sure dev is up to date
git checkout dev
git pull origin dev
```

On GitHub → Pull Requests → New Pull Request:
- **Base:** `prod`
- **Compare:** `dev`
- **Title:** `release: Phase [N] — [Phase Name]`
- **Description:** paste the Phase completion summary (what was built, what was tested)

### 4.3 Review the Promotion PR

Do a full review of the `dev → prod` diff:

- Review every file changed since the last promotion
- Pay special attention to: API routes, Prisma schema, auth logic, file upload handling
- Check for any hardcoded dev values (localhost URLs, debug flags, test credentials)

### 4.4 Merge to Prod

After approval:
1. Use **"Create a merge commit"** (not squash) to preserve the full history from dev
2. Merge message: `release: Phase N — [Phase Name] (YYYY-MM-DD)`

### 4.5 Tag the Release

```bash
git checkout prod
git pull origin prod
git tag -a v[N].0 -m "Phase [N] release: [Phase Name]"
git push origin v[N].0
```

---

## 5. PULL REQUEST TEMPLATE

Create this file at `.github/pull_request_template.md`:

```markdown
## What this PR does
[1-2 sentence summary]

## Phase / Task
Phase [N] — Task #[N]: [Task Name]

## Changes
- [change 1]
- [change 2]

## Testing done
- [ ] PHP syntax check passes (`find application/ -name "*.php" -exec php -l {} \;`)
- [ ] CI3 error log is clean (no FATAL/ERROR lines)
- [ ] Controller smoke tests run (per Protocol 5.3)
- [ ] DB integrity checks verified (per Protocol 5.4)
- [ ] Regression checklist completed (per Protocol 5.6)
- [ ] Visual tests at 375px and 1280px (per Protocol 6.1)
- [ ] RTL visual checks passed (per Protocol 6.2)
- [ ] Animation quality checks passed (per Protocol 6.3)

## Screenshots
[Attach 375px and 1280px screenshots]

## Issues closed
Closes #[issue number]

## Notes / open questions
[Anything the reviewer should know]
```

---

## 6. ISSUE TRACKING

### 6.1 When to Open an Issue

Open a GitHub Issue when:
- A bug is found during review that will NOT be fixed in the current PR
- A feature is discovered to be missing from the implementation plan
- A performance problem is identified that needs a dedicated fix
- A design question needs resolution before implementation can proceed
- A PR review comment cannot be addressed immediately

### 6.2 Issue Format

**Title:** `[type]: [short description]`  
Examples:
- `bug: ledger double-posts when payment is saved twice quickly`
- `feat: add PDF export to tasks report`
- `perf: balance sheet query is slow on accounts with 10k+ entries`
- `design: confirm mobile layout for task financial section`

**Body:**
```markdown
## Description
[What is the problem or request?]

## Steps to reproduce (for bugs)
1. 
2. 
3. 

## Expected behavior
[What should happen]

## Actual behavior
[What actually happens]

## Environment
- Branch: dev
- Commit: [hash]
- Viewport: 375px / 1280px

## Proposed solution (optional)
[Your idea for how to fix it]

## Labels
[bug / enhancement / performance / design / blocking]
```

### 6.3 Issue Lifecycle

```
Open Issue
    ↓
Label it (bug/enhancement/blocking/etc.)
    ↓
Assign to a developer (or yourself)
    ↓
Create a branch: fix/[issue-description] or feat/[issue-description]
    ↓
Fix it → open PR linking "Closes #[issue-number]"
    ↓
PR is reviewed and merged to dev
    ↓
GitHub auto-closes the issue on merge
    ↓
Verify it's closed + add a comment: "Fixed in [commit hash], merged in PR #[N]"
```

### 6.4 Labels to Create on GitHub

Go to GitHub → Issues → Labels and create:

| Label | Color | Purpose |
|---|---|---|
| `bug` | #d73a4a (red) | Something is broken |
| `enhancement` | #a2eeef (teal) | New feature or improvement |
| `performance` | #e4e669 (yellow) | Speed or efficiency issue |
| `design` | #bfd4f2 (blue) | Visual or UX issue |
| `blocking` | #b60205 (dark red) | Blocks a phase from completing |
| `phase-1` through `phase-5` | various | Which phase this belongs to |
| `debt` | #cfd3d7 (gray) | Technical debt — not urgent |
| `question` | #d876e3 (purple) | Needs clarification |

---

## 7. COMMIT MESSAGE REFERENCE

```
feat(scope):     New feature
fix(scope):      Bug fix
chore(scope):    Build, config, dependency change
refactor(scope): Code change with no behavior change
style(scope):    CSS/animation only
perf(scope):     Performance improvement
test(scope):     Tests added or fixed
docs(scope):     Documentation only
wip(scope):      Work in progress checkpoint (squash before PR)
release:         Phase promotion to prod

Scopes: tasks, accounts, services, receipts, reports, auth, db, ui, animations, config
```

---

## 8. QUICK REFERENCE — DAILY WORKFLOW

```bash
# ── Start of every session (run these FIRST, no exceptions) ──
git config user.name  "Sayed Navid Azimi"
git config user.email "snavid.dev@gmail.com"
git config user.name && git config user.email   # verify

# ── Start of day ─────────────────────────────────────────────
git checkout dev && git pull origin dev

# ── Start a new feature ──────────────────────────────────────
git checkout -b feat/[name]

# ── Work → checkpoint commit → push (every 30 min) ───────────
# PHP syntax check first:
find application/ -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
# Then commit:
git add -A && git commit -m "wip(scope): [what's done]" && git push origin feat/[name]

# ── Feature complete → squash → clean commit ─────────────────
git rebase -i HEAD~N
git push --force-with-lease origin feat/[name]

# ── Open PR on GitHub → review → fix → approve → squash merge

# ── After merge — clean up ───────────────────────────────────
git checkout dev && git pull origin dev && git branch -d feat/[name]

# ── End of day — update session log ──────────────────────────
# Edit SESSION_LOG.md with current state
git add SESSION_LOG.md && git commit -m "chore: update session log" && git push origin dev

# ── Sanity check: verify Claude never slipped into git log ───
git log --format="%an <%ae>" | sort -u
# Every line must show: Sayed Navid Azimi <snavid.dev@gmail.com>
```
