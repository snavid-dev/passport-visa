# Implementation Master Prompt — Iran Visa Processing System
## Claude Code Build Instructions (CodeIgniter 3 / PHP 7.4)

> Paste this file as the opening instruction in your Claude Code session.
> Read every section before writing a single line of code.
> Reference `brief.md` for business requirements. This file governs HOW to build.

---

## ENVIRONMENT FACTS (READ FIRST)

- **Live URL:** `https://passport-visa.cyborgtech.co`
- **Working directory:** You already have access to the project root — no `cd` command needed.
- **PHP:** 7.4 is pre-installed on the server. No Docker, no containers, no local server setup required.
- **Web server:** Apache with mod_rewrite — the subdomain is already pointed at the project root.
- **Database:** MySQL on localhost. Credentials are in `.env` (see below). Do not hardcode credentials anywhere.
- **Git identity:** ALL commits must be authored by **Sayed Navid Azimi (snavid-dev)**. Claude must never appear in the git log or contributors list. See `github_workflow.md` for the exact git config commands to run before the first commit.
- **GitHub repo:** `https://github.com/snavid-dev/passport-visa.git`

---

## PART 0 — HARDCODED PERFORMANCE OPTIMIZATION RULES

**These rules are non-negotiable. Apply them at every layer, every file, every decision.**

### 0.1 PHP / CodeIgniter
- **Always use CI3 Query Builder** (Active Record) — never write raw SQL except for complex reports where a raw `$this->db->query()` with bound parameters is cleaner. Never concatenate user input into a query string.
- **Load models and libraries only when needed.** Use `$this->load->model()` inside the method that needs it, or in the constructor only if used by all methods in the controller.
- **Use CI3 caching for expensive report queries.** Wrap report queries with `$this->db->cache_on()` / `$this->db->cache_off()` in production. Clear the cache on writes.
- **Never use `SELECT *`.** Always select only the columns the view needs.
- **Paginate every list query.** Default page size: 25. Use CI3's built-in `Pagination` library.
- **Eager-load with JOINs.** Never loop over a result set and query the DB inside the loop (N+1). Pre-join accounts, services, tasks in a single query.
- **All money math uses PHP `bcmath`** (`bcadd`, `bcsub`, `bcmul`, `bcdiv`) with scale=2. Never use float arithmetic for currency.
- **All DB writes that span multiple tables use transactions:**
  ```php
  $this->db->trans_start();
  // ... inserts/updates ...
  $this->db->trans_complete();
  if ($this->db->trans_status() === FALSE) {
      // rollback happened automatically — handle error
  }
  ```
- **Database indexes:** add indexes on every foreign key and every column used in WHERE or ORDER BY. Define them in `database/visa_system.sql` from day one.

### 0.2 Frontend / Assets
- **Combine and minify CSS in production.** Keep a single `assets/css/app.css` for all custom styles. Load vendor CSS from CDN with `integrity` hashes.
- **Load JS at bottom of `<body>`.** jQuery and plugins load last to not block rendering.
- **Use event delegation for dynamic elements.** Never bind `click` directly to dynamically added passport rows — use `$(document).on('click', '.remove-row', handler)`.
- **Debounce search/filter inputs** at 300 ms before triggering an AJAX call.
- **DataTables with server-side processing** for any table likely to exceed 200 rows. Client-side processing is fine for tables under 200 rows.
- **AJAX calls use `$.ajax()` with explicit `dataType: 'json'`** and always handle both success and error callbacks. Never use `.success()` / `.error()` (deprecated).
- **Compress uploaded images server-side** (PHP GD or Imagick) before saving — max 1600px wide, 80% JPEG quality.

### 0.3 Animation Performance
- **Animate only `transform` and `opacity`** — these are GPU-composited and do not trigger layout. Never animate `width`, `height`, `top`, `left`, `margin`, or `padding`.
- **Use `will-change: transform` on glass panels** that animate on load or scroll.
- **GSAP is the authority for complex sequences.** CSS transitions handle hover/focus. AOS handles scroll reveals. Do not mix all three on the same element.
- **Respect `prefers-reduced-motion`:** add this to `app.css` and check it in JS before initializing GSAP/AOS:
  ```css
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      transition-duration: 0.01ms !important;
    }
  }
  ```
  ```javascript
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reducedMotion) { AOS.init({ ... }); }
  ```
- **AOS.js:** initialize once, globally, in `main.js`. Do not re-initialize per page.
- **GSAP timelines:** create timelines in `DOMContentLoaded`. Kill timelines on AJAX page transitions to prevent memory leaks.

### 0.4 Security (Non-Negotiable)
- **CSRF protection on every POST form.** CI3's CSRF filter must be enabled in `config.php`. Include `<?= $this->security->get_csrf_field() ?>` in every form.
- **Validate and sanitize all input server-side.** Use CI3 Form Validation. Never trust client-side validation alone.
- **Escape all output with `html_escape()`** (CI3 helper). Never echo raw user data.
- **File uploads:** validate MIME type via `finfo_file()` (not just extension), enforce size limit, store outside webroot or in a non-executable directory, serve via a controller that checks auth.
- **Session security:** set `sess_use_database = TRUE` in `config.php`. Regenerate session ID on login.

---

## PART 1 — TECH STACK (LOCKED)

| Layer | Choice | Version |
|---|---|---|
| Backend | PHP | 7.4.x |
| Framework | CodeIgniter | 3.1.x |
| Database | MySQL / MariaDB | 8.0 / 10.6 |
| Frontend | HTML5 + CSS3 + JavaScript (ES6+) | — |
| JS library | jQuery | 3.7.x |
| CSS Framework | Bootstrap | 5.3.x (RTL build) |
| Icons | Font Awesome | 6.x (CDN) |
| Animations (complex) | GSAP | 3.x (CDN) |
| Animations (scroll) | AOS.js | 2.3.x (CDN) |
| Select boxes | Select2 | 4.1.x (Bootstrap 5 theme) |
| Tables | DataTables | 1.13.x + Buttons extension |
| Date picker (Jalali) | jQuery Jalali Datepicker | latest |
| Jalali conversion (PHP) | jDateTimeClass (PHP) | latest |
| Image processing | PHP GD / Imagick | bundled with PHP 7.4 |
| Webserver | Apache 2.4 / Nginx | — |
| PHP process | PHP-FPM | 7.4.x |

**Do not add packages outside this list without documenting the justification in a PR description.**

---

## PART 2 — PROJECT STRUCTURE

```
visa-system/
├── application/
│   ├── config/
│   │   ├── config.php          # App config: base_url, encryption_key, sess_use_database=TRUE
│   │   ├── database.php        # DB credentials
│   │   ├── routes.php          # Custom routes
│   │   ├── autoload.php        # Autoload: database, session, form_validation, url helper
│   │   └── constants.php       # CURRENCIES, ACCOUNT_TYPES, TASK_STATUSES, TIMEZONE
│   │
│   ├── controllers/
│   │   ├── Auth.php            # login, logout
│   │   ├── Dashboard.php
│   │   ├── Tasks.php           # index, create, edit, view, delete, add_payment, remove_payment
│   │   ├── Accounts.php        # index, create, edit, delete
│   │   ├── Services.php
│   │   ├── Receipts.php
│   │   ├── Balance_sheet.php
│   │   ├── Reports.php         # one method per report (10 total)
│   │   ├── Users.php
│   │   ├── Roles.php
│   │   └── Uploads.php         # serves passport scans with auth check
│   │
│   ├── models/
│   │   ├── Task_model.php
│   │   ├── Passport_model.php
│   │   ├── Account_model.php
│   │   ├── Ledger_model.php
│   │   ├── Service_model.php
│   │   ├── Receipt_model.php
│   │   ├── Payment_model.php   # client + vendor payments
│   │   ├── Report_model.php
│   │   ├── User_model.php
│   │   └── Role_model.php
│   │
│   ├── views/
│   │   ├── _layouts/
│   │   │   ├── main.php        # Full page layout: head, sidebar, topbar, footer scripts
│   │   │   └── auth.php        # Minimal layout for login page
│   │   ├── _partials/
│   │   │   ├── sidebar.php     # Permission-aware nav links
│   │   │   ├── topbar.php
│   │   │   ├── alerts.php      # Flash message display
│   │   │   └── pagination.php  # CI3 pagination output
│   │   ├── auth/
│   │   │   └── login.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── tasks/
│   │   │   ├── index.php       # Task list
│   │   │   ├── form.php        # Create / edit (shared form)
│   │   │   └── view.php        # Task detail: passports, payments, status
│   │   ├── accounts/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   ├── services/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   ├── receipts/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   ├── balance_sheet/
│   │   │   └── index.php
│   │   ├── reports/
│   │   │   ├── balance_sheet.php
│   │   │   ├── account_statement.php
│   │   │   ├── cash_position.php
│   │   │   ├── income_expense.php
│   │   │   ├── tasks.php
│   │   │   ├── profit.php
│   │   │   ├── outstanding.php
│   │   │   ├── client.php
│   │   │   ├── vendor.php
│   │   │   └── volume.php
│   │   ├── users/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   └── roles/
│   │       ├── index.php
│   │       └── form.php
│   │
│   ├── libraries/
│   │   └── Permission.php      # hasPermission(), requirePermission()
│   │
│   ├── helpers/
│   │   ├── jalali_helper.php   # to_jalali(), from_jalali(), jalali_date()
│   │   └── money_helper.php    # format_money(), bc_subtract(), etc.
│   │
│   └── core/
│       └── MY_Controller.php   # Base: auth check, permission check, layout loader, flash data
│
├── assets/
│   ├── css/
│   │   ├── app.css             # All custom styles: glass system, layout, components
│   │   └── glass-tokens.css    # CSS custom properties (variables)
│   ├── js/
│   │   ├── main.js             # Global init: AOS, GSAP page transition, CSRF header
│   │   ├── task-form.js        # Dynamic passport rows, Select2 + datepicker re-init
│   │   ├── datatables-init.js  # DataTables global config + per-table init
│   │   └── select2-init.js     # Select2 global config (RTL, Persian placeholder)
│   └── img/
│       └── bg-gradient.jpg     # Background for glass effect (subtle)
│
├── uploads/
│   └── passports/              # {task_id}/{uuid}.jpg — NOT in webroot ideally
│       .htaccess               # Deny direct access: "Deny from all"
│
├── database/
│   └── visa_system.sql         # Full schema + seed data (roles, permissions, cash account)
│
├── system/                     # CI3 core (do not modify)
├── index.php                   # CI3 entry point
├── .htaccess                   # RewriteEngine for clean URLs
└── .env                        # DB credentials (never commit — in .gitignore)
```

---

## PART 3 — DESIGN SYSTEM

### 3.1 Glassmorphism CSS Tokens (`assets/css/glass-tokens.css`)

```css
:root {
  /* Glass surfaces */
  --glass-bg:           rgba(255, 255, 255, 0.70);
  --glass-bg-strong:    rgba(255, 255, 255, 0.85);
  --glass-bg-subtle:    rgba(255, 255, 255, 0.45);
  --glass-border:       rgba(255, 255, 255, 0.60);
  --glass-border-glow:  rgba(255, 255, 255, 0.90);
  --glass-shadow:       0 8px 32px rgba(31, 38, 135, 0.12),
                        0 2px 8px  rgba(31, 38, 135, 0.06);
  --glass-shadow-hover: 0 16px 48px rgba(31, 38, 135, 0.18),
                        0 4px 16px rgba(31, 38, 135, 0.10);
  --glass-blur:         blur(20px);
  --glass-blur-heavy:   blur(40px);

  /* Page background */
  --bg-gradient: linear-gradient(135deg, #f0f4ff 0%, #e8ecf8 50%, #f4f0ff 100%);

  /* Accent (deep indigo) */
  --accent:       #4f46e5;
  --accent-light: #818cf8;
  --accent-dark:  #3730a3;
  --accent-glow:  rgba(79, 70, 229, 0.20);

  /* Status */
  --success:  #10b981;
  --warning:  #f59e0b;
  --error:    #f43f5e;
  --info:     #06b6d4;

  /* Text */
  --text-primary:   #1e1b4b;
  --text-secondary: #6b7280;
  --text-muted:     #9ca3af;

  /* Spacing & radius */
  --radius-sm:  10px;
  --radius-md:  16px;
  --radius-lg:  24px;
  --radius-xl:  32px;
}
```

### 3.2 Glass Panel Class (add to `app.css`)

```css
.glass-panel {
  background:           var(--glass-bg);
  backdrop-filter:      var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border:               1px solid var(--glass-border);
  border-radius:        var(--radius-lg);
  box-shadow:           var(--glass-shadow);
  transition:           box-shadow 0.3s ease, transform 0.3s ease;
}

.glass-panel:hover {
  box-shadow: var(--glass-shadow-hover);
}

.glass-panel--strong {
  background: var(--glass-bg-strong);
}

.glass-panel--subtle {
  background: var(--glass-bg-subtle);
  backdrop-filter: var(--glass-blur-heavy);
  -webkit-backdrop-filter: var(--glass-blur-heavy);
}

/* Card with luminous top border */
.glass-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border-radius: var(--radius-lg);
  box-shadow: var(--glass-shadow);
  border-top: 1px solid var(--glass-border-glow);
  border-left: 1px solid rgba(255,255,255,0.40);
  border-right: 1px solid rgba(255,255,255,0.25);
  border-bottom: 1px solid rgba(255,255,255,0.20);
}
```

### 3.3 GSAP Animation Patterns (`main.js`)

```javascript
// Page entrance — run on every page load
document.addEventListener('DOMContentLoaded', function() {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  // Sidebar entrance
  gsap.from('.app-sidebar', {
    x: 80, opacity: 0, duration: 0.6,
    ease: 'power3.out'
  });

  // Page content stagger
  gsap.from('.glass-card', {
    y: 32, opacity: 0, duration: 0.5,
    stagger: 0.08, ease: 'power3.out', delay: 0.1
  });

  // Topbar drop
  gsap.from('.app-topbar', {
    y: -40, opacity: 0, duration: 0.5, ease: 'power3.out'
  });
});

// AOS scroll reveals
AOS.init({
  duration: 500,
  easing: 'ease-out-cubic',
  once: true,
  offset: 40
});
```

```javascript
// Reusable: animate a new row being added (call from task-form.js)
function animateRowIn(rowElement) {
  gsap.from(rowElement, {
    opacity: 0, y: 16, duration: 0.35, ease: 'power3.out'
  });
}

// Reusable: animate a row being removed
function animateRowOut(rowElement, callback) {
  gsap.to(rowElement, {
    opacity: 0, height: 0, marginBottom: 0, paddingTop: 0, paddingBottom: 0,
    duration: 0.3, ease: 'power2.in',
    onComplete: callback
  });
}
```

### 3.4 Typography & RTL

```html
<!-- In <head> of main layout -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

```css
/* globals in app.css */
html { direction: rtl; }
body {
  font-family: 'Vazirmatn', sans-serif;
  font-feature-settings: "ss01"; /* Western digits in Vazirmatn */
  background: var(--bg-gradient);
  min-height: 100vh;
  color: var(--text-primary);
}
```

---

## PART 4 — MY_Controller (Base Controller)

Every controller extends `MY_Controller`. This is the backbone of auth, RBAC, and layout rendering.

```php
<?php
// application/core/MY_Controller.php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $current_user = null;

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'html', 'jalali', 'money']);
        $this->load->library('session');
        date_default_timezone_set('Asia/Kabul');

        // Auth check — override in Auth controller
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
        }

        // Load current user + role + permissions
        $this->load->model('User_model');
        $this->current_user = $this->User_model->get_with_permissions(
            $this->session->userdata('user_id')
        );
    }

    // Gate a controller action to a permission key
    protected function require_permission($key) {
        if (!$this->has_permission($key)) {
            show_error('دسترسی غیرمجاز', 403);
        }
    }

    protected function has_permission($key) {
        if (!$this->current_user) return false;
        return in_array($key, $this->current_user->permissions ?? []);
    }

    // Render a view inside the main layout
    protected function render($view, $data = []) {
        $data['current_user']    = $this->current_user;
        $data['content_view']    = $view;
        $this->load->view('_layouts/main', $data);
    }

    // Render a view inside the minimal auth layout
    protected function render_auth($view, $data = []) {
        $data['content_view'] = $view;
        $this->load->view('_layouts/auth', $data);
    }
}
```

---

## PART 5 — DATABASE SCHEMA

Save the full schema as `database/visa_system.sql`. Key design rules:
- All dates stored as `DATE` or `DATETIME` in **Gregorian**. Display layer converts to Shamsi.
- All money columns use `DECIMAL(15,2)`. Never `FLOAT`.
- All currency columns use `ENUM('AFN','USD','TOMAN')`.
- Foreign keys defined with `ON DELETE RESTRICT` unless explicitly stated otherwise.

```sql
-- Permission keys (seed data in SQL file)
INSERT INTO permissions (key_name, label) VALUES
('manage_tasks',        'مدیریت وظایف'),
('manage_accounts',     'مدیریت حساب‌ها'),
('manage_services',     'مدیریت خدمات'),
('manage_receipts',     'مدیریت رسیدها'),
('view_balance_sheet',  'مشاهده ترازنامه'),
('manage_users',        'مدیریت کاربران'),
('manage_roles',        'مدیریت نقش‌ها'),
('view_reports',        'مشاهده گزارشات');
```

**Tables to create (full DDL in `visa_system.sql`):**
- `users` — id, name, username, email, phone, password, role_id, active, created_at
- `roles` — id, name, created_at
- `permissions` — id, key_name, label
- `role_permissions` — role_id, permission_id (composite PK)
- `financial_accounts` — id, name, type ENUM('client','vendor','expense','income','individual','cash'), active, created_at
- `ledger_entries` — id, account_id, currency, debit DECIMAL(15,2), credit DECIMAL(15,2), date, source, reference, note, recorded_by, created_at
- `services` — id, name, default_fee DECIMAL(15,2), default_currency, visa_type, active
- `tasks` — id, client_id, vendor_id, service_id, visa_type, destination DEFAULT 'Iran', date, status ENUM('open','completed','cancelled'), fee_amount, fee_currency, vendor_cost_amount, vendor_cost_currency, created_at, updated_at
- `task_passports` — id, task_id, surname, given_name, passport_no, dob, place_of_birth, issue_date, expiry_date, gender, scan_path
- `task_client_payments` — id, task_id, amount, currency, date, note, created_at
- `task_vendor_payments` — id, task_id, amount, currency, date, note, created_at

**Required indexes (add to SQL):**
```sql
ALTER TABLE ledger_entries ADD INDEX idx_account_currency (account_id, currency);
ALTER TABLE ledger_entries ADD INDEX idx_date (date);
ALTER TABLE ledger_entries ADD INDEX idx_source_ref (source, reference);
ALTER TABLE tasks ADD INDEX idx_client (client_id);
ALTER TABLE tasks ADD INDEX idx_vendor (vendor_id);
ALTER TABLE tasks ADD INDEX idx_status (status);
ALTER TABLE tasks ADD INDEX idx_date (date);
ALTER TABLE task_passports ADD INDEX idx_task (task_id);
ALTER TABLE task_client_payments ADD INDEX idx_task (task_id);
ALTER TABLE task_vendor_payments ADD INDEX idx_task (task_id);
```

---

## PART 6 — SELECT2 + DATEPICKER INIT RULES

These are the most common source of bugs with dynamic rows. Follow exactly.

```javascript
// assets/js/task-form.js

// Init Select2 on a specific row
function initRowSelects(row) {
  $(row).find('.select2').select2({
    theme: 'bootstrap-5',
    dir: 'rtl',
    width: '100%',
    language: 'fa'
  });
}

// Init Jalali datepicker on a specific row
function initRowDatepickers(row) {
  $(row).find('.jalali-date').each(function() {
    $(this).persianDatepicker({ // or whichever plugin is used
      format: 'YYYY/MM/DD',
      autoClose: true,
      observer: true
    });
  });
}

// Add passport row
$('#add-passport-row').on('click', function() {
  var rowHtml = $('#passport-row-template').html();
  var newRow  = $(rowHtml);
  var rowIndex = $('.passport-row').length;
  // Update input names: passport_rows[0][surname] → passport_rows[N][surname]
  newRow.find('input, select').each(function() {
    var name = $(this).attr('name');
    if (name) $(this).attr('name', name.replace('[0]', '[' + rowIndex + ']'));
  });
  $('#passport-rows-container').append(newRow);
  initRowSelects(newRow);
  initRowDatepickers(newRow);
  animateRowIn(newRow[0]);
});

// Remove passport row
$(document).on('click', '.remove-passport-row', function() {
  var row = $(this).closest('.passport-row');
  // Destroy Select2 before removing
  row.find('.select2').each(function() {
    if ($(this).hasClass('select2-hidden-accessible')) {
      $(this).select2('destroy');
    }
  });
  animateRowOut(row[0], function() {
    row.remove();
    // Re-index remaining rows
    $('.passport-row').each(function(i) {
      $(this).find('input, select').each(function() {
        var name = $(this).attr('name');
        if (name) {
          $(this).attr('name', name.replace(/\[\d+\]/, '[' + i + ']'));
        }
      });
    });
  });
});
```

---

## PART 7 — LEDGER POSTING PATTERN (SAFE SIDE-EFFECT)

Every task payment must atomically post to: (1) task payment log, (2) cash account, (3) client/vendor account. Use CI3 transactions.

```php
// In Payment_model.php
public function add_client_payment($task_id, $amount, $currency, $date, $note) {
    $this->db->trans_start();

    // 1. Insert payment record
    $this->db->insert('task_client_payments', [
        'task_id'    => $task_id,
        'amount'     => $amount,
        'currency'   => $currency,
        'date'       => $date,
        'note'       => $note,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    // 2. Debit cash (cash goes up — client paid us)
    $this->db->insert('ledger_entries', [
        'account_id'  => $this->_get_cash_account_id(),
        'currency'    => $currency,
        'debit'       => $amount,
        'credit'      => 0,
        'date'        => $date,
        'source'      => 'task_client_payment',
        'reference'   => $task_id,
        'note'        => $note,
        'recorded_by' => $this->session->userdata('user_id'),
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    // 3. Credit client account (receivable goes down)
    $client_id = $this->_get_task_client_id($task_id);
    $this->db->insert('ledger_entries', [
        'account_id'  => $client_id,
        'currency'    => $currency,
        'debit'       => 0,
        'credit'      => $amount,
        'date'        => $date,
        'source'      => 'task_client_payment',
        'reference'   => $task_id,
        'note'        => $note,
        'recorded_by' => $this->session->userdata('user_id'),
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    $this->db->trans_complete();
    return $this->db->trans_status();
}
```

Mirror this for `add_vendor_payment` (credit cash, debit vendor account).
When a payment is **deleted**, reverse all three entries in a single transaction.

---

## PART 8 — BUILD ORDER (FOLLOW STRICTLY)

### Phase 1 — Scaffold & Foundation
1. Verify the project root is accessible: `ls -la` (no `cd` needed — you are already in it)
2. Copy `.env.example` to `.env`, fill in the GitHub token — DB credentials are already set in the example
3. Add `.env` loader to `index.php` (see Part 9 below)
4. Configure `application/config/config.php`:
   - `$config['base_url'] = 'https://passport-visa.cyborgtech.co/';`
   - `$config['sess_use_database'] = TRUE;`
   - `$config['csrf_protection'] = TRUE;`
5. Configure `application/config/database.php` to read from environment variables (see Part 9)
6. Configure `autoload.php` and `routes.php`
7. Create `database/visa_system.sql` — full schema + indexes + seed data
8. Import schema: `mysql -u sql_passport_vis -p'dca957546afa6' sql_passport_vis < database/visa_system.sql`
9. Verify tables: `mysql -u sql_passport_vis -p'dca957546afa6' sql_passport_vis -e "SHOW TABLES;"`
10. Create `MY_Controller.php` with auth + RBAC + `render()` helper
11. Create helpers: `jalali_helper.php`, `money_helper.php`
12. Create `_layouts/main.php` and `_layouts/auth.php`
13. Create `_partials/sidebar.php`, `topbar.php`, `alerts.php`
14. Link all CSS/JS assets via CDN: Bootstrap 5 RTL, Font Awesome, Vazirmatn, GSAP, AOS, Select2, DataTables, jQuery
15. Implement glassmorphism design system: `glass-tokens.css`, `app.css`
16. Implement `main.js`: GSAP page entrance, AOS init, CSRF jQuery header
17. Test the URL loads: `curl -I https://passport-visa.cyborgtech.co/` — expect 200 or redirect to login

### Phase 2 — Auth + Users + Roles
11. `Auth` controller: login form (glass design + GSAP entrance), login POST, logout
12. `Users` controller + `User_model`: list, create, edit, delete (protect self-delete)
13. `Roles` controller + `Role_model`: list, create, edit, delete, assign permissions
14. Permission-aware navigation in `sidebar.php`

### Phase 3 — Core Data
15. `Accounts` controller + `Account_model`: list (DataTables), create/edit modal, delete
16. `Services` controller + `Service_model`: list, create/edit, delete
17. `Receipts` controller + `Receipt_model` + `Ledger_model`: list, create/edit, delete — posts to ledger

### Phase 4 — Tasks (Core Feature)
18. `Tasks` controller: `index` (list with filters, DataTables)
19. Task `create` / `edit` form: header section (client, vendor, service, dates)
20. Dynamic passport rows: add/remove, file upload, Select2 + datepicker re-init, GSAP animation
21. Task financial section: fee, vendor cost (auto-calc from service + passport count)
22. Client payment log in task view: add payment → triggers ledger posting
23. Vendor payment log in task view: add payment → triggers ledger posting
24. Task view page: all passports, payment history, per-currency outstanding status

### Phase 5 — Balance Sheet + Reports
25. `Balance_sheet` controller: per-currency balances from `ledger_entries` GROUP BY
26. Report 1: Balance sheet (accounts × 3 currencies)
27. Report 2: Account statement (ledger for one account, date range)
28. Report 3: Cash position
29. Report 4: Income vs expense
30. Report 5: Tasks report
31. Report 6: Profit report
32. Report 7: Outstanding balances
33. Report 8: Client report
34. Report 9: Vendor report
35. Report 10: Passport / visa-type volume

### Phase 6 — Polish
36. Mobile QA pass: every page at 375px
37. Animation QA: every page entrance, modal, row add/remove
38. Security audit: CSRF on all forms, output escaping, upload directory protection
39. Performance: add report query caching, verify all indexes are used (`EXPLAIN`)
40. `php -l` on all PHP files — zero syntax errors

---

## PART 9 — ENVIRONMENT SETUP (.env LOADER)

CI3 has no native `.env` support. Add this minimal loader to `index.php` **before** the CI3 bootstrap lines:

```php
// index.php — add near the top, before define('BASEPATH', ...)
// ─── Load .env ───────────────────────────────────────────────
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // skip comments
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
// ─────────────────────────────────────────────────────────────
```

Then in `application/config/database.php`:

```php
$db['default'] = [
    'dsn'          => '',
    'hostname'     => getenv('DB_HOST')    ?: 'localhost',
    'username'     => getenv('DB_USER')    ?: '',
    'password'     => getenv('DB_PASS')    ?: '',
    'database'     => getenv('DB_NAME')    ?: '',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8mb4',
    'dbcollat'     => 'utf8mb4_unicode_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => TRUE,
    'failover'     => [],
    'save_queries' => (ENVIRONMENT !== 'production'),
];
```

The `.env` file (never committed) holds:
```
DB_HOST=localhost
DB_NAME=sql_passport_vis
DB_USER=sql_passport_vis
DB_PASS=dca957546afa6
APP_URL=https://passport-visa.cyborgtech.co
GITHUB_TOKEN=your_pat_here
```

---

## PART 10 — CODING CONVENTIONS

- **Controller method naming:** `index`, `create`, `store`, `edit`, `update`, `delete`. Use POST for mutations; use GET only for reads.
- **Model method naming:** `get_all()`, `get_by_id($id)`, `create($data)`, `update($id, $data)`, `delete($id)`. Report queries go in `Report_model`.
- **View naming:** `application/views/[module]/[action].php`. Shared partials in `_partials/`.
- **Money output:** always use `format_money($amount, $currency)` helper — never echo raw decimal.
- **Date output:** always use `jalali_date($gregorian_date)` helper — never display raw DB dates.
- **Flash messages:** use `$this->session->set_flashdata('success'|'error'|'warning', 'message')` and render in `_partials/alerts.php`.
- **AJAX responses:** always return JSON: `$this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'data' => ...]));`
- **No `SELECT *`:** always list columns explicitly in models.
- **No PHP short tags:** use `<?php` and `<?=` only (short echo tag is allowed in CI3).
- **Commit format:** `type(scope): description` — e.g. `feat(tasks): add dynamic passport row upload`.
