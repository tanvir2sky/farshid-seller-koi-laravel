# Developer guide (Laravel experts)

This document describes how **Laravel 11** and **Botble CMS** are wired in this project, and how to find code when you are maintaining or updating it. It assumes you already know Laravel.

---

## What this project is (from `composer.json`)

This is **Laravel 11** with **Botble CMS**: a “platform + plugins + themes” setup, not a minimal `laravel/laravel` skeleton.

- **Framework**: `laravel/framework` ^11.31, PHP ^8.2|^8.3.
- **CMS packages**: Many `botble/*` packages (base, ACL, media, theme, API, ecommerce, payments, etc.). Some use `*@dev` and resolve from **`platform/` path repositories** (see `repositories` in `composer.json`).
- **Composer merge plugin**: `wikimedia/composer-merge-plugin` merges **`platform/plugins/*/composer.json`** and **`platform/themes/*/composer.json`** into the root graph. Plugin/theme-specific PHP dependencies are declared there but installed at the project root.
- **Post-update**: `post-update-cmd` runs Laravel asset publish, AWS SDK trimming, and **`php artisan cms:publish:assets`**. Expect **published files under `public/`** after updates (see `.gitignore`).

**Mental model**: **`platform/`** is the main CMS codebase. **`app/`** is a thin Laravel shell (providers, minimal routes). Admin identity and permissions are driven by Botble’s **ACL** in core, not only `app/Models/User.php`.

---

## Directory map

| Area | Path | Role |
|------|------|------|
| Laravel shell | `app/`, `bootstrap/`, `config/`, `routes/`, `database/` (root) | Standard Laravel. `routes/web.php` is largely empty because the CMS registers its own routes. |
| Botble core | `platform/core/*` | Admin, ACL, media, settings, tables, dashboard, etc. Namespaces like `Botble\Base\`, `Botble\ACL\` (see `platform/core/composer.json` `autoload`). |
| First-party packages | `platform/packages/*` | Plugin management, theme engine, menu, page, SEO, installer, etc. |
| Plugins | `platform/plugins/*` | Feature modules (e.g. `ecommerce`, `payment`, gateways). Each has **`plugin.json`**, `src/`, routes, views, migrations. |
| Themes | `platform/themes/*` | Storefront / theme (e.g. **martfury**): views, assets, widgets. |
| Dependencies | `vendor/` | Includes `botble/*` not mapped as path repos; path repos use `platform/core` and `platform/packages/*`. |

### Path helpers

Defined from packages under `platform/`:

- `platform_path()` → `base_path('platform/...')`
- `core_path()`, `package_path()`, `plugin_path()`, `theme_path()`

Example: the ecommerce plugin lives under `plugin_path('ecommerce')` → `platform/plugins/ecommerce`.

---

## Bootstrapping vs vanilla Laravel

1. **`bootstrap/app.php`** wires **`routes/web.php`**, **`routes/console.php`**, and **`/up`**. API routes may be registered by `botble/api` or individual plugins rather than a root `routes/api.php`.
2. **Botble service providers** are registered from **`platform/core/composer.json`** (`extra.laravel.providers`) and merged packages. They load routes, migrations, views, and config via **`LoadAndPublishDataTrait`**.
3. **Plugins** use **activation state** and a **manifest** (PSR-4 roots + provider classes). `PluginManagementServiceProvider` registers each active plugin’s provider (see `platform/packages/plugin-management/src/Providers/PluginManagementServiceProvider.php`).

When asking “who registered this route?”, search **providers under `platform/`**, not only `routes/web.php`.

---

## How to find things

### Routes

- **`php artisan route:list`** — filter by URI or name (fastest index).
- Implementations usually live in **`platform/**/routes/web.php`** (sometimes other files if the provider loads them).
- Providers call **`loadRoutes()`**, which by default loads `routes/web.php` beside that module.

### Controllers, models, form requests

- Namespaces **`Botble\...`** under `platform/plugins/<plugin>/src`, `platform/core/<module>/src`, or `platform/packages/<pkg>/src`.
- Search: `rg "class YourClassName" platform`

### Config

- Many CMS configs are **merged at runtime** from paths like `platform/.../config/general.php` into keys such as **`config('core.base.general')`** (namespace `core/base` + config file `general`). See `LoadAndPublishDataTrait::loadAndPublishConfigurations`.
- Optional **publish** to `config/<dashed-namespace>/...` via the **`cms-config`** group.
- Laravel framework config remains under root **`config/`**.

### Views / Blade

- Namespaces use dashed segments, e.g. **`core/base::...`**, loaded from that package’s `resources/views`.
- Theme views: **`platform/themes/<theme>/views`** (and related partials).

### Translations

- Per-module `resources/lang` with the same namespace conventions.
- This repo’s `.gitignore` typically keeps **`lang/en`** tracked and ignores other top-level `lang/*` entries when those are generated or vendor overrides — align with your deployment process.

### Migrations

- **Per module**: `database/migrations` inside each core package, plugin, or theme package (loaded by their providers).
- **Root** `database/migrations`: Laravel and app-level migrations. Some plugins (e.g. ecommerce) also run root migrations on activation — read the plugin’s `Plugin` class.

### Assets / frontend

- **`package.json`**: **npm workspaces** over `platform/core/*`, `platform/packages/*`, `platform/plugins/*`, `platform/themes/*`.
- **`webpack.mix.js`**: builds scoped to theme/plugin via env-style variables (`npm_config_theme`, `npm_config_plugin`).
- After PHP/composer updates, run **`php artisan cms:publish:assets`** when the CMS expects refreshed public assets.

### Hooks / extensibility

- Core uses **WordPress-style** hooks in places (e.g. **`do_action(BASE_ACTION_INIT)`** in `BaseServiceProvider`). Search **`do_action`**, **`add_action`**, **`apply_filters`** in `platform/` when you need extension points without forking.

---

## Plugins

Each plugin typically includes:

- **`plugin.json`**: `id`, `namespace`, **`provider`** (main `*ServiceProvider`), version, requirements.
- **`src/Providers/*ServiceProvider.php`**: container bindings, `boot()`, routes, `DashboardMenu` entries, etc.
- **`src/Plugin.php`** (optional): **`activated()`**, **`remove()`**, default settings, cleanup. **Updates can change migrations and lifecycle behavior.**

**Discovery**: `plugin.json` → provider class → `routes/`, `config/`, `resources/`.

---

## Themes

- Themes live under **`platform/themes/<name>`** (e.g. **martfury**): layouts, ecommerce Blade, **widgets** under `widgets/<widget>/` with `registration.php` and templates.
- Theme `composer.json` files are merged by the Composer merge plugin; treat themes like first-class packages for “where is the storefront UI?”

---

## Notable packages (from `composer.json`)

- **`botble/api`**: API surface; routes/controllers may live in that package and in plugins.
- **`predis/predis`**: Redis; wire via `.env` for cache/session/queue as needed.
- **`doctrine/dbal`**: Useful for certain migration/schema operations.
- **Dev**: Pint, PHPUnit 11, Larastan, Rector, Debugbar, Pail — standard Laravel 11 tooling.

---

## Workflows

1. **Composer update**: Watch **`post-update-cmd`**. Re-run **`cms:publish:assets`** if it did not run or failed. Refresh Node deps and rebuild Mix assets when workspace packages change.
2. **Admin issues**: Start in **`platform/core`** and **`platform/packages`**, then the relevant **`platform/plugins`**.
3. **Storefront issues**: Start in **`platform/themes/<active-theme>`**, then **`platform/plugins/ecommerce`** and payment plugins for checkout.
4. **Overrides**: Prefer **`AppServiceProvider`**, published **`cms-config`**, theme overrides, and documented hooks before editing files under `vendor/`.
5. **Production**: Same cache discipline as Laravel (`config:cache`, `route:cache`, etc.). If plugins or the manifest seem stale, clear caches and confirm plugins are active.

---

## References

- Botble documentation: [https://docs.botble.com](https://docs.botble.com)
- Support (from `platform/core/composer.json`): [https://botble.ticksy.com](https://botble.ticksy.com)
