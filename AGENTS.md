# Agent instructions — Seller Koi (Botble + Martfury)

Use this file (and the matching `CLAUDE.md`) so new sessions do not need manual context about stack or folder layout.

## What this repository is

- **Laravel 11** application with **Botble CMS** (`botble/platform`, `botble/api`, plugins, theme system, etc.).
- **PHP 8.2 or 8.3** (see root `composer.json`).
- The active public/admin experience is driven by the **Martfury** theme and Botble plugins, not only the small root `app/` namespace.

## Where the code lives

**Primary codebase: `platform/` at the repository root.**

| Path | Purpose |
|------|---------|
| `platform/core/` | Botble core modules (ACL, base, dashboard, media, etc.). |
| `platform/packages/` | Path-repo packages merged by Composer (pages, SEO, sitemap, revision, etc.). |
| `platform/plugins/` | Feature plugins (for example `ecommerce`, `language`, analytics). Each plugin is its own package with routes, views, src, config. |
| `platform/themes/martfury/` | **Martfury** theme: Blade layouts/views, `partials/`, `views/`, `widgets/`, `assets/`, compiled `public/` assets, theme `routes/web.php`. |

**Root Laravel tree** (`app/`, `bootstrap/`, `config/`, `routes/`, `database/`, `tests/`): standard Laravel shell; `app/` may stay small relative to `platform/`. Prefer extending Botble via plugins or the Martfury theme when the change is CMS- or storefront-specific.

Composer **merge-plugin** pulls in `platform/plugins/*/composer.json` and `platform/themes/*/composer.json` — new plugins/themes are usually added under those globs.

## Conventions for assistants

1. **Search and edit in `platform/` first** for CMS features, admin UI behavior, e-commerce flows, and Martfury presentation.
2. **Theme work** → `platform/themes/martfury/` (match existing Blade, SCSS, Mix/Vite usage in that tree).
3. **Plugin work** → dedicated folder under `platform/plugins/<name>/`; follow existing plugin structure (`src/`, `config/`, `routes/`, `resources/`).
4. **Respect Botble APIs** — hooks, facades, shortcodes, `Theme::`, `Ecommerce` helpers, etc.; avoid bypassing the CMS with unrelated ad-hoc patterns when an extension point exists.
5. **Artisan** commands are run from the **repository root** (same directory as `artisan`).

## Cursor-specific note

Persistent Cursor rules for every chat live in `.cursor/rules/` (for example `botble-platform.mdc`). Keep this `AGENTS.md` and `CLAUDE.md` aligned when you change high-level project facts.
