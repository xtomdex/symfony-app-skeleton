# Admin Panel

AdminPanel is a proto-bundle module that provides a configurable, multi-panel admin UI system built on Symfony UX Twig Components and Webpack Encore.

The module lives in `src/Modules/AdminPanel/` and follows the proto-bundle convention: all code, templates, assets, tests, and documentation are self-contained within the module directory.

This document describes architectural rules and design decisions, not implementation details.

---

# 1. Core Principles

## 1.1 Proto-Bundle Convention

AdminPanel is a UI module, not a domain module.

Unlike domain modules (User, Auth), it depends on Symfony Twig, Routing, and UX TwigComponent. It contains templates, assets, and frontend resources alongside PHP code.

Rule: modules that include UI (templates, assets, Twig components) follow the proto-bundle convention. All resources live inside the module directory under `Resources/`. External coupling is limited to the project's asset entry point (which imports module assets) and the Encore entry in `webpack.config.js`.

This convention does not apply to domain modules. Domain modules remain pure PHP.

## 1.2 Self-Contained Module

Everything related to AdminPanel lives inside `src/Modules/AdminPanel/`:

- PHP code (contracts, DTOs, services, Twig components, listeners)
- Templates (`Resources/views/`)
- Assets (`Resources/assets/`)
- Configuration (`Resources/config/`)
- Tests (`Tests/`)
- Documentation (`Resources/docs/`)

Nothing is placed in project-level `templates/`, `assets/`, `config/packages/`, or `tests/` directories by the skeleton.

## 1.3 Optional Activation

The skeleton contains AdminPanel code but does NOT activate it by default.

The skeleton does NOT require Webpack Encore, npm, or any frontend tooling.

Projects that need AdminPanel activate it explicitly. Projects that don't need it (e.g. API-only) ignore it.

Activation steps are documented in `Resources/docs/INSTALLATION.md`.

## 1.4 Multi-Panel Architecture

One admin panel = one PHP class implementing `AdminPanelInterface`.

A project may define multiple panels (e.g. `/admin` for superadmins, `/manager` for managers, `/partner` for partners). Each panel has its own brand, menu, user context, and footer.

Layout templates are unaware of multi-panel complexity. They read the active panel from the request and render accordingly.

## 1.5 Pure Presentation Components

Twig Components provided by AdminPanel are presentation-only.

They receive prepared data and render HTML. They do not query databases, call use cases, or contain business logic.

Data preparation is the responsibility of the project's controllers and use cases.

---

# 2. Module Structure

    src/Modules/AdminPanel/
    ├── Contract/
    │   └── AdminPanelInterface.php
    ├── DTO/
    │   ├── MenuItem.php
    │   ├── PreparedMenuItem.php
    │   ├── BrandConfig.php
    │   ├── FooterConfig.php
    │   └── AdminUserView.php
    ├── Menu/
    │   └── AdminMenuBuilder.php
    ├── Listener/
    │   └── ResolveAdminPanelListener.php
    ├── Twig/
    │   ├── Component/
    │   │   ├── Page.php
    │   │   ├── Card.php
    │   │   ├── DataTable.php
    │   │   └── NavTabs.php
    │   └── Extension/
    │       └── AdminUiTwigExtension.php
    ├── AdminPanelRegistry.php
    ├── Resources/
    │   ├── config/
    │   │   ├── services.yaml
    │   │   └── twig.yaml
    │   ├── views/
    │   │   ├── layout/
    │   │   │   ├── base.html.twig
    │   │   │   ├── base.test.html.twig
    │   │   │   ├── _sidebar.html.twig
    │   │   │   ├── _sidebar_menu_item.html.twig
    │   │   │   ├── _navbar.html.twig
    │   │   │   ├── _user_dropdown.html.twig
    │   │   │   ├── _footer.html.twig
    │   │   │   └── _brand.html.twig
    │   │   └── components/
    │   │       ├── Page.html.twig
    │   │       ├── Card.html.twig
    │   │       ├── DataTable.html.twig
    │   │       └── NavTabs.html.twig
    │   ├── assets/
    │   │   ├── admin.js              ← JS-only entry (no SCSS import)
    │   │   ├── scss/
    │   │   │   ├── core.scss         ← main SCSS aggregator
    │   │   │   ├── libs.scss
    │   │   │   ├── _bootstrap.scss
    │   │   │   ├── _bootstrap-extended/
    │   │   │   ├── _components/
    │   │   │   ├── _colors.scss
    │   │   │   └── _libs/
    │   │   ├── js/
    │   │   │   ├── helpers.js
    │   │   │   ├── config.js
    │   │   │   ├── main.js
    │   │   │   ├── menu.js
    │   │   │   └── libs/
    │   │   │       ├── bootstrap.js
    │   │   │       └── perfect-scrollbar.js
    │   │   └── icons/
    │   │       └── iconify-icons.css
    │   └── docs/
    │       └── INSTALLATION.md
    └── Tests/
        ├── Unit/
        └── Functional/
            └── Fixtures/

---

# 3. Panel Configuration

## 3.1 AdminPanelInterface

Each panel is a single PHP class implementing:

```php
interface AdminPanelInterface
{
    public static function name(): string;
    public static function routePrefix(): string;

    public function brand(): BrandConfig;

    /** @return list<MenuItem> */
    public function menuItems(): array;

    public function userView(): ?AdminUserView;

    public function footer(): FooterConfig;

    public function homePath(): string;
}
```

`name()` — unique panel identifier (e.g. `'main'`, `'partner'`).

`routePrefix()` — URL prefix for panel resolution (e.g. `'/admin'`, `'/partner'`).

`brand()` — logo and application name for sidebar.

`menuItems()` — sidebar menu structure.

`userView()` — current user data for navbar dropdown. Returns `null` when unauthenticated.

`footer()` — footer content.

`homePath()` — absolute URL path for the panel's home page (e.g. `'/admin'`, `'/partner/dashboard'`). Used as the breadcrumb root link.

## 3.2 Panel Registration

Panels are collected via Symfony tagged services:

```yaml
services:
    _instanceof:
        App\Modules\AdminPanel\Contract\AdminPanelInterface:
            tags: ['admin.panel']

    App\Modules\AdminPanel\AdminPanelRegistry:
        arguments:
            $panels: !tagged_iterator admin.panel
```

`AdminPanelRegistry` indexes panels by name and route prefix.

## 3.3 Panel Resolution

`ResolveAdminPanelListener` runs on `kernel.request` (priority 16).

It matches the request path against registered route prefixes. If matched, the resolved panel is stored as a request attribute `_admin_panel`.

Twig Extension reads `_admin_panel` from the current request. Layout templates call `admin_brand()`, `admin_sidebar_menu()`, `admin_user()`, `admin_footer()` — all resolved from the active panel.

Layout templates do not know how many panels exist or which one is active.

---

# 4. UI Components

## 4.1 Symfony UX Twig Components

All UI components use Symfony UX TwigComponent (`#[AsTwigComponent]`).

Components are namespaced under `Admin:` prefix:

```yaml
twig_component:
    defaults:
        App\Modules\AdminPanel\Twig\Component\:
            template_directory: '@admin_panel/components'
            name_prefix: 'Admin'
```

Usage: `<twig:Admin:Card>`, `<twig:Admin:Page>`, `<twig:Admin:DataTable>`, `<twig:Admin:NavTabs>`.

## 4.2 Component Inventory

Priority components (first implementation phase):

**Page** — page wrapper. Delegates breadcrumb rendering to a nested `<twig:Admin:Breadcrumbs>`. Props: `title`, `breadcrumbs` (list of `Breadcrumb`), `showHome` (bool, default `true`). Slots: `actions`, default content.

**Breadcrumbs** — standalone breadcrumb trail. Props: `items` (list of `Breadcrumb`), `showHome` (bool, default `true`). When `showHome=true`, prepends a home icon breadcrumb using the active panel's `homePath()`. When `items` is empty and `showHome=true`, appends a `...` placeholder after the home icon.

**Card** — content card. Props: `title`, `subtitle`, `icon` (?string, default `null` — optional Iconify icon rendered before the title). Slots: `actions`, default content (body), `footer`.

**DataTable** — pure presentation table. Props: `columns`, `items`, `sort`, `pagination`. No DTO contract — receives raw arrays/objects, renders HTML.

**NavTabs** — navigation sub-tabs. Renders a row of links as second/third-level navigation. Props: `tabs` (list of `{label, url, active?, icon?}`), `style` (string, default `'tabs'`, options: `'tabs'`|`'pills'`). Not Bootstrap JS tabs — each link navigates to a separate route.

### Breadcrumb DTO

`Breadcrumb` is a `final readonly` DTO used by the Page and Breadcrumbs components:

```php
final readonly class Breadcrumb
{
    public function __construct(
        public string $label,
        public ?string $url = null,
        public ?string $icon = null,
    ) {}
}
```

`label` — display text. May be empty when `icon` is used instead (e.g. the home breadcrumb).

`url` — link target. `null` for the last (current) breadcrumb, which renders as plain text.

`icon` — optional Iconify icon identifier rendered in place of or alongside the label.

## 4.3 DataTable Design

DataTable is a pure presentation component.

It does NOT provide DTOs, query builders, data sources, or abstractions for data retrieval. It receives prepared data from the controller and renders a table with optional sorting indicators and pagination controls.

Column definitions, row data, sorting state, and pagination state are passed as component props. The project's controller (or a project-level view builder) is responsible for preparing this data from use case results.

This keeps AdminPanel decoupled from ORM, repositories, and business logic. It also avoids recreating EasyAdmin's CRUD-centric approach.

## 4.4 Component Design Rules

- Components are presentation-only. No database queries, no service injection beyond Twig needs.
- Components use named slots (`<twig:block name="...">`) for extensibility.
- Components render HTML with CSS classes from the admin theme. No inline styles.
- Components are stateless. All data comes through props.

---

# 5. Layout Architecture

## 5.1 Base Layout

`base.html.twig` is a clean shell:

- Includes `_sidebar.html.twig`, `_navbar.html.twig`, `_footer.html.twig` as partials
- Provides `{% block body %}` for page content
- Provides `{% block stylesheets %}` and `{% block javascripts %}` for asset extension
- Calls `encore_entry_link_tags('admin')` and `encore_entry_script_tags('admin')` for core assets

## 5.2 Dynamic Content

All layout data comes through Twig functions, not controller variables:

- `admin_brand()` — returns `BrandConfig` from active panel
- `admin_sidebar_menu()` — returns `list<PreparedMenuItem>` built from active panel's menu items
- `admin_user()` — returns `?AdminUserView` from active panel
- `admin_footer()` — returns `FooterConfig` from active panel

Controllers do not pass layout data. They inherit `base.html.twig` and only populate `{% block body %}`.

## 5.3 Navbar

Navbar contains user dropdown only. User dropdown is configurable via `AdminPanelInterface::userView()`.

Shortcuts section from the original template is removed. Navbar is minimal.

Projects may override `{% block navbar %}` in their templates for custom navbar content.

## 5.4 Sidebar Menu

Menu system from the current implementation is retained:

- `MenuItem` — immutable DTO with fluent API (`linkToRoute()`, `linkToUrl()`, `section()`, `withIcon()`, `withRoutePrefix()`, `withChildren()`, `visibleForRoles()`)
- `AdminMenuBuilder` — resolves URLs, determines active/open state based on current route
- `PreparedMenuItem` — flat DTO with resolved `link`, `active`, `open` flags

Menu is defined in `AdminPanelInterface::menuItems()` and built per request.

---

# 6. Assets

## 6.1 Ownership Split

Module owns the **core** (SCSS with `!default` variables, JS behavior). Project owns the **entry point** and **customization** (variable overrides, custom styles).

This separation exists because SCSS variables use the `!default` flag. When the project defines `$primary: #e91e63` before importing the module core, SCSS skips the `$primary: $purple !default` in the module. Overrides must appear earlier in the same SCSS compilation chain.

If module and project SCSS were imported through separate JS `import` statements, Webpack would compile them as independent SCSS trees and variable overrides would not work. Therefore, a single SCSS entry point in the project controls the full chain.

## 6.2 Module Assets (core)

All core assets live inside the module:

    src/Modules/AdminPanel/Resources/assets/
    ├── admin.js              ← JS-only entry (imports JS, NOT SCSS)
    ├── scss/
    │   ├── core.scss         ← aggregator: bootstrap → colors → components
    │   ├── libs.scss         ← third-party CSS (perfect-scrollbar, etc.)
    │   ├── _bootstrap.scss
    │   ├── _bootstrap-extended/
    │   │   ├── _include.scss ← functions, variables (!default), mixins
    │   │   ├── _variables.scss
    │   │   ├── _variables-dark.scss
    │   │   └── ...           ← component overrides
    │   ├── _components/
    │   │   ├── _include.scss ← component variables (!default), mixins
    │   │   ├── _variables.scss
    │   │   ├── _variables-dark.scss
    │   │   └── ...           ← menu, layout, avatar, etc.
    │   ├── _colors.scss
    │   └── _libs/
    ├── js/
    │   ├── helpers.js
    │   ├── config.js
    │   ├── main.js
    │   ├── menu.js
    │   └── libs/
    │       ├── bootstrap.js
    │       └── perfect-scrollbar.js
    └── icons/
        └── iconify-icons.css

Module SCSS does NOT contain custom variable override files. All variables use `!default` and are designed to be overridden from the project entry point.

Module `admin.js` imports JS only, not SCSS:

```js
// src/Modules/AdminPanel/Resources/assets/admin.js
import './js/helpers';
import './js/config';
import './js/libs/bootstrap';
import './js/libs/perfect-scrollbar';
import './js/menu';
import './js/main';
```

## 6.3 Project Assets (entry point + customization)

The project creates a thin wrapper that imports overrides and module core:

    assets/admin/
    ├── admin.js              ← Webpack entry point (project owns)
    ├── admin.scss            ← SCSS entry point (project owns)
    ├── _custom-variables.scss  ← variable overrides (project owns)
    └── _custom-styles.scss   ← free-form CSS (project owns)

Project SCSS entry:

```scss
// assets/admin/admin.scss

// 1. Project variable overrides — BEFORE core
@import "custom-variables";

// 2. Module core (all !default variables yield to overrides above)
@import "../../src/Modules/AdminPanel/Resources/assets/scss/core";
@import "../../src/Modules/AdminPanel/Resources/assets/scss/libs";
@import "../../src/Modules/AdminPanel/Resources/assets/icons/iconify-icons.css";

// 3. Project custom styles — AFTER core
@import "custom-styles";
```

Project JS entry:

```js
// assets/admin/admin.js

// 1. Styles — single SCSS chain with overrides
import './admin.scss';

// 2. Module JS
import '../../src/Modules/AdminPanel/Resources/assets/admin';

// 3. Project-specific JS plugins (optional)
// import 'chart.js';
```

Project variable overrides:

```scss
// assets/admin/_custom-variables.scss
$primary: #e91e63;
$font-size-root: 15px;
$menu-width: 18rem;
```

## 6.4 Build Tool

Webpack Encore. Chosen because the admin theme requires SCSS compilation and JS plugin bundling. AssetMapper does not support SCSS.

Encore is NOT installed in the skeleton by default. Projects install it as part of AdminPanel activation.

## 6.5 Entry Point Strategy

Single entry point: `admin` (from the project's `assets/admin/admin.js`). All CSS and JS for the admin UI is compiled into one bundle.

This is intentional. Admin panels have limited audience, the bundle is cached after first load, and splitting provides negligible benefit for typical admin page sizes.

When a project needs heavy page-specific libraries (Chart.js, CodeMirror, etc.), there are two options:

- Light plugins needed on all pages: import in the project's `admin.js` after the module import
- Heavy libraries for specific pages: add a separate entry point in `webpack.config.js` and include via `{% block javascripts %}{{ parent() }}{{ encore_entry_script_tags('admin_charts') }}{% endblock %}` in specific templates

Additional entry points are the project's responsibility, not the module's.

## 6.6 External Coupling

The only external coupling for assets is one line in the project's `webpack.config.js`:

```js
Encore.addEntry('admin', './assets/admin/admin.js');
```

The entry point references the project's wrapper file, not the module directly. npm dependencies are documented in `INSTALLATION.md`, not in a skeleton-level `package.json`.

---

# 7. Testing

## 7.1 Test Location

Tests live inside the module: `src/Modules/AdminPanel/Tests/`.

PHPUnit registers them as a dedicated test suite:

```xml
<testsuite name="admin-panel">
    <directory>src/Modules/AdminPanel/Tests</directory>
</testsuite>
```

Run isolated: `php bin/phpunit --testsuite admin-panel`.

## 7.2 Test Layout

Functional tests use `base.test.html.twig` — a minimal layout that does NOT call Encore functions.

This allows functional tests to run without npm, Webpack, or compiled assets. Tests verify Twig rendering, component output, sidebar/navbar content — not CSS/JS.

## 7.3 Unit Tests

Must cover:

- `MenuItem` — factory methods, immutability, fluent API, clone behavior
- `PreparedMenuItem` — section factory, hasChildren
- `BrandConfig`, `FooterConfig`, `AdminUserView` — creation, field access
- `Breadcrumb` — construction, field defaults (`url` and `icon` nullable)
- `AdminMenuBuilder` — build with nesting, active/open resolution, empty children filtering
- `AdminPanelRegistry` — resolveByRequest, get by name, missing panel exception
- `ResolveAdminPanelListener` — attribute set on match, skipped on sub-request, null on no match
- `AdminUiTwigExtension` — function registration, exception when no panel in request
- `Breadcrumbs` component — `getResolvedItems()` prepends home breadcrumb, appends placeholder when items empty, falls back to `/` when no panel in request

## 7.4 Functional Tests

Must cover:

- Sidebar renders menu items from test panel
- Navbar renders user dropdown when user is present
- Navbar handles null user (unauthenticated)
- Each component (Page, Card, DataTable, NavTabs, Breadcrumbs) renders correctly with test data

Functional tests use a `TestAdminPanel` fixture implementing `AdminPanelInterface` and test-only controllers.

## 7.5 Test Isolation

Test-only artifacts (controllers, panels, routes) must not load in dev/prod environments.

Services config excludes tests:

```yaml
App\Modules\AdminPanel\:
    resource: '../../*'
    exclude:
        - '../../Resources/'
        - '../../Tests/'
```

---

# 8. Project Integration

## 8.1 Activation Checklist

Projects activate AdminPanel by:

1. Installing Webpack Encore (`composer require symfony/webpack-encore-bundle`)
2. Installing Symfony UX TwigComponent (`composer require symfony/ux-twig-component`)
3. Running `npm install` with dependencies listed in `INSTALLATION.md`
4. Creating project asset files (`assets/admin/admin.js`, `admin.scss`, overrides)
5. Adding entry point in `webpack.config.js` pointing to project's `assets/admin/admin.js`
6. Importing module service configs in `config/packages/admin_panel.yaml`
7. Adding AdminPanel test namespace to `composer.json` autoload-dev
8. Adding `admin-panel` test suite to `phpunit.xml.dist`
9. Implementing `AdminPanelInterface` (at least one panel)
10. Compiling assets (`npm run build`)

Detailed step-by-step instructions are in `src/Modules/AdminPanel/Resources/docs/INSTALLATION.md`.

## 8.2 Project Responsibilities

The project:

- Implements `AdminPanelInterface` for each panel
- Configures Symfony Security (firewalls, access control) for admin routes
- Creates admin controllers that extend `base.html.twig` and populate `{% block body %}`
- Adds project-specific Encore entry points for heavy libraries if needed
- May override layout blocks (`navbar`, `footer`) in project templates

## 8.3 What AdminPanel Does NOT Do

- Does not provide CRUD generation
- Does not abstract over entities or repositories
- Does not manage routing (projects define their own admin routes)
- Does not manage security (projects configure firewalls)
- Does not provide form themes (projects use standard Symfony forms)
- Does not replace the CommandBus/UseCase pattern with getter/setter CRUD

---

# 9. Bundle Extraction Path

AdminPanel is designed for future extraction into a standalone Symfony Bundle.

When extracting:

1. Move `src/Modules/AdminPanel/` to a separate repository
2. Add `AdminPanelBundle.php` extending `AbstractBundle`
3. Add `DependencyInjection/AdminPanelExtension.php` for config loading
4. Change namespace from `App\Modules\AdminPanel\` to `YourVendor\AdminPanel\`
5. Add `composer.json` with package metadata and dependencies
6. Projects install via `composer require` from private VCS repository

The private repository can be hosted on Bitbucket or GitHub. Projects on either platform can consume it via VCS repository configuration in `composer.json`.

Tests, assets, templates, and documentation migrate as-is. No restructuring required.

---

# 10. File Ownership

| Path | Owner | Rule |
|---|---|---|
| `src/Modules/AdminPanel/` | Skeleton | All module code, updated via skeleton sync |
| `src/Modules/AdminPanel/Resources/assets/` | Skeleton | Core SCSS (`!default` variables) and JS behavior |
| `assets/admin/` | Project | Entry points, variable overrides, custom styles |
| `config/packages/admin_panel.yaml` | Project | Created by project to activate module |
| `webpack.config.js` | Project | Entry point added by project |
| `package.json` | Project | Created by project with Encore install |
| `AdminPanelInterface` implementations | Project | Project defines panels |
| Admin controllers and routes | Project | Project defines admin pages |
| `security.yaml` admin firewall | Project | Project configures access |
