# PROJECT — Digital Menu SaaS Platform

A multi-tenant SaaS platform where restaurants and cafes manage **display-only**
digital menus with their own branding, QR codes, and theme templates.

> Status: **Phase 2 (cleanup & foundation) complete.** Business features are not
> implemented yet. This document describes the target architecture and the
> planned implementation phases.

---

## 1. Stack

| Layer | Technology |
| --- | --- |
| Framework | Laravel 13 (PHP 8.3) |
| Frontend | Vue 3 + Inertia.js 2 |
| Styling | Tailwind CSS 3 (PostCSS) |
| Build | Vite 8 |
| Auth scaffolding | Laravel Breeze (Inertia + Vue) |
| API auth | Laravel Sanctum |
| Routing bridge | Ziggy |
| Database (current) | SQLite (`database/database.sqlite`) |
| Database (target) | MySQL |

No framework versions were upgraded in Phase 2.

---

## 2. Product Features

1. **Super Admin Dashboard** — manage restaurants, plans and platform settings.
2. **Restaurant Dashboard** — owners manage their menus, categories and branding.
3. **Public Digital Menu** — branded, mobile-first, read-only menu per restaurant.
4. **Smart Branding** — automatic palette/theme derived from the restaurant logo.
5. **QR Code Studio** — generate and customize downloadable menu QR codes.
6. **Menu Theme Templates** — selectable themes/templates for the public menu.
7. **Product & Category Management** — create/reorder categories and items.
8. **Subscription Management** — assign plans and manage tenant subscriptions.
9. **Analytics** — view counts and item interactions for published menus.

## 3. Strict Exclusions

This platform is **display-only**. The following are explicitly **out of scope**:

- Shopping cart
- Customer ordering
- Delivery systems
- Checkout / customer payments
- Table ordering
- Kitchen management

---

## 4. Architecture (target)

- **Tenancy model**: single shared MySQL database, shared schema, with a
  `restaurant_id` tenant key on tenant-owned tables, enforced through tenant
  middleware and global query scopes. Public menus are resolved by unique
  restaurant `slug`.
- **Roles**: `super_admin`, `restaurant_owner`, `restaurant_staff`
  (mechanism — `spatie/laravel-permission` vs. a lightweight role enum — to be
  decided before implementing authorization).
- **Route areas**:
  - Public: `/` and `/m/{restaurant:slug}` (final URL strategy pending).
  - Restaurant dashboard: authenticated area for owners/staff.
  - Super admin: dedicated admin area.
- **Inertia shared props**: `auth.user` today; later extended with current
  `tenant`, `branding`, and `plan`.
- **Media/branding**: logos and menu images stored on the `public` disk
  (`php artisan storage:link` still required before use).
- **Analytics**: aggregate, privacy-conscious events; no customer PII stored.

### Directory structure (prepared in Phase 2)

```
app/Http/Controllers/
  Admin/         # Super admin controllers (empty placeholder)
  Restaurant/    # Restaurant dashboard controllers (empty placeholder)
  Public/        # Public menu controllers (empty placeholder)

resources/js/
  Pages/
    Admin/         # Super admin pages
    Restaurant/    # Restaurant dashboard pages
    Public/        # Public-facing pages (Home.vue placeholder)
  Layouts/
    Admin/         # Super admin layouts
    Restaurant/    # Restaurant dashboard layouts
  Components/
    Shared/        # Cross-cutting components (AppLogo.vue)
    Menu/          # Menu display components
    Branding/      # Smart-branding components
    QRCode/        # QR studio components
```

Preserved from the starter: authentication, registration, password reset,
email verification, user profile, Inertia integration, Tailwind config, and all
existing UI components (`Modal`, `Dropdown`, buttons, inputs, nav links).

---

## 5. Database

- Current: **SQLite** at `database/database.sqlite` (9 tables, default Breeze
  schema). At the time of the audit it contained **0 users** and no business
  data.
- A backup copy was created at `database/backups/` (git-ignored).
- The `database/.gitignore` excludes all `*.sqlite*` files from version control.
- **Target is MySQL.** `config/database.php` already ships a `mysql` connection
  and the `pdo_mysql` PHP extension is available. No code changes are required
  to switch; only `.env` values need updating once connection details are
  provided. `.env` was **not** modified in this phase.

---

## 6. Planned Implementation Phases

- **Phase 1 — Audit** ✅ read-only review of the existing project.
- **Phase 2 — Cleanup & Foundation** ✅ (this phase) removed starter clutter,
  initialized Git, prepared directory namespaces, documented the project.
- **Phase 3 — Database & Tenancy**: MySQL switch, tenant/restaurant schema,
  role system, tenancy middleware and scopes.
- **Phase 4 — Restaurant Dashboard**: restaurant CRUD, categories, products.
- **Phase 5 — Public Menu**: public menu rendering, themes/templates, SEO.
- **Phase 6 — Branding & QR Studio**: logo palette extraction, theme tokens,
  QR generation and download.
- **Phase 7 — Super Admin & Subscriptions**: platform administration, plans,
  subscription assignment.
- **Phase 8 — Analytics**: menu view/engagement tracking and reporting.

Each phase is gated on explicit approval before implementation.

---

## 7. Local Development

```sh
# Backend + frontend dev servers
composer dev

# One-off build
npm run build

# Tests and formatting
php artisan test
php vendor/bin/pint --test
```

Requirements: PHP 8.3, Composer, Node.js, and (for the target database) MySQL.
