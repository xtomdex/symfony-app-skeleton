# AdminPanel — Installation Guide

Step-by-step instructions for activating the AdminPanel module in a project.

If any step is skipped or misconfigured, the admin panel will not work.

---

# 1. PHP Dependencies

Install required Symfony packages:

```bash
composer require symfony/webpack-encore-bundle
composer require symfony/ux-twig-component
```

`webpack-encore-bundle` provides Twig functions (`encore_entry_link_tags`, `encore_entry_script_tags`) and integrates Webpack with Symfony.

`ux-twig-component` provides the `#[AsTwigComponent]` attribute and `<twig:*>` syntax used by all AdminPanel UI components.

---

# 2. npm Dependencies

After installing `webpack-encore-bundle`, a `package.json` and `webpack.config.js` will be generated in the project root (if they don't exist).

Install required npm packages:

```bash
npm install
npm install bootstrap@^5.3 @popperjs/core@^2.11
npm install -D sass@^1.70 sass-loader@^14.0
npm install perfect-scrollbar@^1.5
```

Breakdown:

| Package | Purpose |
|---|---|
| `bootstrap` | CSS framework (imported via SCSS) |
| `@popperjs/core` | Required by Bootstrap dropdowns, tooltips, popovers |
| `sass` | SCSS compiler |
| `sass-loader` | Webpack integration for SCSS |
| `perfect-scrollbar` | Sidebar scrollbar |

---

# 3. Project Asset Files

Create the project's asset wrapper directory and files:

    assets/admin/
    ├── admin.js
    ├── admin.scss
    ├── _custom-variables.scss
    └── _custom-styles.scss

## 3.1 `assets/admin/admin.js`

Main entry point. Imports SCSS and module JS:

```js
// Styles — single SCSS chain with project overrides
import './admin.scss';

// Module JS
import '../../src/Modules/AdminPanel/Resources/assets/admin';
```

If the project needs additional JS libraries on all admin pages, import them after the module:

```js
import './admin.scss';
import '../../src/Modules/AdminPanel/Resources/assets/admin';

// Project-specific (optional)
// import 'chart.js';
// import './my-custom-plugin';
```

## 3.2 `assets/admin/admin.scss`

SCSS entry point. Controls the override → core → custom styles chain:

```scss
// 1. Project variable overrides — BEFORE core
//    Variables defined here take priority over !default values in module core
@import "custom-variables";

// 2. Module core
@import "../../src/Modules/AdminPanel/Resources/assets/scss/core";
@import "../../src/Modules/AdminPanel/Resources/assets/scss/libs";
@import "../../src/Modules/AdminPanel/Resources/assets/icons/iconify-icons.css";

// 3. Project custom styles — AFTER core
@import "custom-styles";
```

Do not change the import order. Overrides must come before core, custom styles must come after.

## 3.3 `assets/admin/_custom-variables.scss`

Project-specific variable overrides. Empty by default:

```scss
// Override any !default variable from the module core.
// See src/Modules/AdminPanel/Resources/assets/scss/_bootstrap-extended/_variables.scss
// and src/Modules/AdminPanel/Resources/assets/scss/_components/_variables.scss
// for available variables.

// Examples:
// $primary: #e91e63;
// $font-size-root: 15px;
// $menu-width: 18rem;
```

## 3.4 `assets/admin/_custom-styles.scss`

Free-form project CSS. Empty by default:

```scss
// Project-specific styles that don't fit into variable overrides.
```

---

# 4. Webpack Configuration

Edit `webpack.config.js` in the project root. Add the admin entry point:

```js
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // AdminPanel entry point
    .addEntry('admin', './assets/admin/admin.js')

    .enableSassLoader()
    .enableSingleRuntimeChunk()
;

module.exports = Encore.getWebpackConfig();
```

Key points:

- `.enableSassLoader()` is required for SCSS compilation
- Entry name `'admin'` must match what `base.html.twig` calls: `encore_entry_link_tags('admin')` / `encore_entry_script_tags('admin')`
- If the project has other entry points (e.g. `app` for the frontend), they coexist without conflict

---

# 5. Module Service Configuration

Create `config/packages/admin_panel.yaml`:

```yaml
imports:
    - { resource: '../../src/Modules/AdminPanel/Resources/config/services.yaml' }
    - { resource: '../../src/Modules/AdminPanel/Resources/config/twig.yaml' }
```

This registers all AdminPanel services (registry, listener, Twig extension, components) and configures the `@admin_panel` Twig namespace.

Without this file, none of the AdminPanel services are loaded.

---

# 6. Autoload Configuration

Add the AdminPanel test namespace to `composer.json`:

```json
{
    "autoload-dev": {
        "psr-4": {
            "App\\Tests\\": "tests/",
            "App\\Modules\\AdminPanel\\Tests\\": "src/Modules/AdminPanel/Tests/"
        }
    }
}
```

Run `composer dump-autoload` after editing.

---

# 7. PHPUnit Configuration

Add the AdminPanel test suite to `phpunit.xml.dist`:

```xml
<testsuites>
    <!-- Existing suites -->
    <testsuite name="unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="functional">
        <directory>tests/Functional</directory>
    </testsuite>

    <!-- AdminPanel -->
    <testsuite name="admin-panel">
        <directory>src/Modules/AdminPanel/Tests</directory>
    </testsuite>
</testsuites>
```

Run AdminPanel tests isolated: `php bin/phpunit --testsuite admin-panel`.

---

# 8. Implement AdminPanelInterface

Create at least one panel class. Example:

```php
<?php

declare(strict_types=1);

namespace App\Panel;

use App\Infrastructure\Security\UserIdentity;
use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\FooterConfig;
use App\Modules\AdminPanel\DTO\MenuItem;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class MainAdminPanel implements AdminPanelInterface
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public static function name(): string
    {
        return 'main';
    }

    public static function routePrefix(): string
    {
        return '/admin';
    }

    public function brand(): BrandConfig
    {
        return BrandConfig::create('My App')
            ->withLogos('img/logo.svg');
    }

    public function menuItems(): array
    {
        return [
            MenuItem::linkToRoute('Dashboard', 'admin_dashboard')
                ->withIcon('tabler-dashboard'),
            MenuItem::section('Management'),
            MenuItem::linkToRoute('Users', 'admin_users')
                ->withIcon('tabler-users')
                ->withRoutePrefix('admin_user'),
        ];
    }

    public function userView(): ?AdminUserView
    {
        $identity = $this->security->getUser();

        if (!$identity instanceof UserIdentity) {
            return null;
        }

        return new AdminUserView(
            displayName: $identity->getUsername(),
            avatarUrl: null,
            role: 'Admin',
            profileUrl: $this->urlGenerator->generate('admin_profile'),
            logoutUrl: $this->urlGenerator->generate('app_logout'),
        );
    }

    public function footer(): FooterConfig
    {
        return FooterConfig::create('© My Company');
    }
}
```

The panel class is autowired and autoconfigured via the `admin.panel` tag defined in the module's `services.yaml`.

---

# 9. Build Assets

Compile for development:

```bash
npm run dev
```

Compile with file watching:

```bash
npm run watch
```

Compile for production:

```bash
npm run build
```

Compiled assets appear in `public/build/`. The `admin` entry produces `admin.js` and `admin.css` (plus versioned chunks).

---

# 10. Create Admin Routes and Controllers

The module does not provide routes or controllers. The project creates its own:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
}
```

```twig
{# templates/admin/dashboard.html.twig #}
{% extends '@admin_panel/layout/base.html.twig' %}

{% block title %}Dashboard{% endblock %}

{% block body %}
    <twig:Admin:Page title="Dashboard">
        <twig:Admin:Card title="Welcome">
            <p>This is the admin dashboard.</p>
        </twig:Admin:Card>
    </twig:Admin:Page>
{% endblock %}
```

---

# 11. Configure Security

The module does not manage security. The project configures firewalls and access control in `security.yaml`:

```yaml
security:
    providers:
        users_by_username:
            id: App\Infrastructure\Security\Provider\ByUsernameProvider

    firewalls:
        admin:
            pattern: ^/admin
            lazy: true
            provider: users_by_username
            form_login:
                login_path: admin_login
                check_path: admin_login
                default_target_path: /admin
            logout:
                path: admin_logout

    access_control:
        - { path: ^/admin/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

---

# Verification Checklist

After completing all steps, verify:

- [ ] `composer require symfony/webpack-encore-bundle` — installed
- [ ] `composer require symfony/ux-twig-component` — installed
- [ ] `npm install` — all npm dependencies installed
- [ ] `assets/admin/admin.js` — exists, imports `admin.scss` and module JS
- [ ] `assets/admin/admin.scss` — exists, imports overrides → core → custom styles
- [ ] `assets/admin/_custom-variables.scss` — exists (can be empty)
- [ ] `assets/admin/_custom-styles.scss` — exists (can be empty)
- [ ] `webpack.config.js` — has `.addEntry('admin', './assets/admin/admin.js')` and `.enableSassLoader()`
- [ ] `config/packages/admin_panel.yaml` — imports module services and twig config
- [ ] `composer.json` — has AdminPanel test namespace in autoload-dev
- [ ] `phpunit.xml.dist` — has `admin-panel` test suite
- [ ] At least one class implementing `AdminPanelInterface` exists
- [ ] `npm run dev` — compiles without errors
- [ ] `security.yaml` — admin routes protected
- [ ] Browser: admin page renders with sidebar, navbar, and correct branding
