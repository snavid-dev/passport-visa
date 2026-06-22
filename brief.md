# Project Brief — Iran Visa Processing & Accounting System

**Version:** 1.0  
**Date:** 2026-06-20  
**Owner:** Sayed Navid Azimi

---

## 1. What We Are Building

A web-based operational system for an Afghan company that processes Iran visa applications. The company collects passports from clients, routes them to partner vendors who obtain the visas, and needs to track every financial transaction in three independent currencies (AFN, USD, Toman) with zero conversion between them.

This is a **production-grade, Persian-only, RTL, mobile-first internal tool** built on CodeIgniter 3 + PHP 7.4. The UI will be premium glassmorphism — clean, classy, and animation-rich in the style of Apple.com.

---

## 2. Business Problem

The company currently manages passport batches, vendor assignments, and multi-currency payments across disconnected records. This creates accounting errors, lost payment history, and no real-time visibility into profitability per task, per client, or per vendor.

---

## 3. Core Modules

| Module | Purpose |
|---|---|
| **Tasks** | A "task" = one passport batch → one vendor → Iran visas. The core feature. |
| **Financial Accounts** | Single table of accounts typed as: client, vendor, expense, income, individual, cash. |
| **Services** | Named visa service types with default per-passport fees and currencies. |
| **Receipts** | Manual credit/debit ledger entries against any account. |
| **Balance Sheet** | Real-time three-column (AFN/USD/Toman) balances per account. |
| **Reports** | 10 read-only reports (financial + operational + party-focused + volume). |
| **Users** | Simple CRUD — name, username, role, active. |
| **Roles & Permissions** | RBAC gating every module and navigation item. |

---

## 4. Out of Scope

- Staff/HR module, salaries, leaves
- Patient or clinic concepts
- Bilingual support — Persian only
- Currency conversion of any kind
- Any legacy CodeIgniter code (reference patterns only, do not port)

---

## 5. UI Direction

**Style:** Glassmorphism — frosted glass panels, soft blurs, luminous borders, depth through layering. Inspired by Apple.com's product pages and macOS UI: clean whitespace, precise typography, premium feel.

**Animations:** Every state transition, modal open/close, page load, data update, and micro-interaction must be animated. Use GSAP for complex sequences, AOS.js for scroll-triggered reveals, and CSS transitions for hover/focus states. No abrupt jumps — everything slides, fades, scales, or springs.

**Typography:** Vazirmatn (Persian web font) — clean, geometric, modern. Western numerals.

**Direction:** RTL everywhere. Persian labels, placeholders, and messages only.

**Mobile:** Primary target. Design mobile-first; desktop is an enhancement.

**Color palette:** Light glassmorphism base — white/ultra-light gray glass panels on soft gradient backgrounds. Accent: deep indigo/blue-violet. Status colors: emerald (success), amber (warning), rose (error).

---

## 6. Multi-Currency Rules (Non-Negotiable)

- Three currencies: **AFN, USD, Toman**
- **No conversion. Ever.** Each currency is a completely independent ledger.
- Every monetary record carries a `currency` field.
- Payment status (paid/partial/unpaid) is tracked **per currency**, not as a single status.
- Balance sheet shows three independent balance columns.
- Profit is only meaningful when client fee and vendor cost share the same currency — otherwise both sides display independently.

---

## 7. Tech Stack

| Layer | Choice |
|---|---|
| Backend | PHP 7.4, CodeIgniter 3, MVC |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, vanilla JS + jQuery 3.x |
| CSS Framework | Bootstrap 5 (RTL build) + custom glassmorphism CSS |
| Icons | Font Awesome 6 |
| Animations | GSAP 3 (page transitions, card effects) + AOS.js (scroll reveals) + CSS transitions |
| Select boxes | Select2 4.x (Bootstrap 5 theme) |
| Tables | DataTables 1.13.x (Bootstrap 5 + Buttons extension) |
| Date (Jalali) | Jalali Datepicker (jQuery plugin) — display Shamsi, store Gregorian |
| File uploads | Native PHP `$_FILES` + CI3 Upload library |
| Auth | CI3 session-based login |
| RBAC | Roles + permissions; `require_permission()` in `MY_Controller` |
| Language | Persian only, RTL, Vazirmatn font, Western digits |
| Timezone | `Asia/Kabul` |
| Deployment | Apache / Nginx + PHP-FPM on Linux |

---

## 8. Key Constraints

1. **Mobile-first** — every screen must be fully usable on a 375px phone
2. **Persian only, RTL** — no English labels in the UI, no bilingual fallback
3. **No currency conversion** — three independent balances, always
4. **Transactional payments** — task payment posts atomically to: task log + cash account + counterparty account
5. **Shamsi display / Gregorian storage** — never store Jalali strings in date columns
6. **RBAC** — every API route and UI element gated by permission
7. **Glassmorphism + animations** — non-negotiable UI quality bar

---

## 9. Success Criteria

- Operator can create a task, add passport rows, upload scans, and record payments in under 2 minutes on a phone
- Balance sheet reflects every transaction in real time
- All 10 reports load filtered data correctly per currency
- System survives page refresh and power outage (persistent server-side state, no lost data)
- UI is visually indistinguishable in quality from a commercial SaaS product
