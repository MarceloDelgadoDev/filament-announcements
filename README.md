<p align="center">
    <img src="https://raw.githubusercontent.com/MarceloDelgadoDev/filament-announcements/main/art/banner.png" alt="Filament Announcements Banner" style="width: 100%; max-width: 800px;" />
</p>

<p align="center">
    <a href="https://packagist.org/packages/marcelodelgado/filament-announcements">
        <img src="https://img.shields.io/packagist/v/marcelodelgado/filament-announcements?style=flat-square" alt="Latest Version" />
    </a>
    <a href="https://packagist.org/packages/marcelodelgado/filament-announcements">
        <img src="https://img.shields.io/packagist/dt/marcelodelgado/filament-announcements?style=flat-square" alt="Total Downloads" />
    </a>
    <a href="https://packagist.org/packages/marcelodelgado/filament-announcements">
        <img src="https://img.shields.io/packagist/php-v/marcelodelgado/filament-announcements?style=flat-square" alt="PHP Version" />
    </a>
    <a href="https://github.com/MarceloDelgadoDev/filament-announcements/blob/main/LICENSE">
        <img src="https://img.shields.io/github/license/MarceloDelgadoDev/filament-announcements?style=flat-square" alt="License" />
    </a>
</p>

---

# Filament Announcements

A [Filament v5](https://filamentphp.com/) plugin to display institutional announcements and alerts on your panel dashboard — with full CRUD, per-user dismiss, and automatic expiration.

---

## Screenshots

### Dashboard Widget
![Widget](https://raw.githubusercontent.com/MarceloDelgadoDev/filament-announcements/main/art/widget.png)

### Announcements List
![List](https://raw.githubusercontent.com/MarceloDelgadoDev/filament-announcements/main/art/resource-list.png)

### Create / Edit Form
![Form](https://raw.githubusercontent.com/MarceloDelgadoDev/filament-announcements/main/art/resource-form.png)

---

## Features

- 📢 **Dashboard widget** — displays active announcements ordered by severity
- ✅ **Full CRUD** via `AnnouncementResource` with type badges, bulk actions and inline toggles
- ⏱ **Automatic expiration** — uses `starts_at` / `expires_at` date windows, no jobs required
- 👤 **Per-user dismiss** — dismissible announcements are remembered per user via a pivot table
- 🔒 **Flexible permissions** — works out-of-the-box, with optional Gate or Spatie integration
- 🌍 **Translatable** — all strings go through `__()` with publishable language files
- 🎨 **4 severity levels** — `info`, `warning`, `danger`, `success` with Filament semantic colors

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | **8.2+** |
| Laravel | **11+** |
| Filament | **5.x** |
| spatie/laravel-package-tools | **^1.16** |

---

## Installation

Install via Composer:

```bash
composer require marcelodelgado/filament-announcements
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="announcements-migrations"
php artisan migrate
```

Optionally publish config and views to customize them:

```bash
php artisan vendor:publish --tag="announcements-config"
php artisan vendor:publish --tag="announcements-views"
php artisan vendor:publish --tag="announcements-translations"
```

---

## Setup

### 1. Register the plugin in your PanelProvider

```php
use Marcelodelgado\Announcements\AnnouncementsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            AnnouncementsPlugin::make()
                ->pollingInterval('120s') // optional, default: '60s'
        );
}
```

### 2. Add the trait to your User model

```php
use Marcelodelgado\Announcements\Traits\HasAnnouncements;

class User extends Authenticatable
{
    use HasAnnouncements;
}
```

That's it — the widget will appear on your dashboard and the **Announcements** menu item will show up in the sidebar.

---

## Configuration

After publishing, edit `config/announcements.php`:

| Key | Type | Default | Description |
|---|---|---|---|
| `permission_check` | `null`, `string` or `Closure` | `null` | Access control for the Resource. `null` = no extra check. `string` = Laravel Gate name. `Closure` = receives the authenticated user, must return `bool`. |
| `polling_interval` | `string` | `'60s'` | Fallback `wire:poll` interval for the widget when the panel plugin interval cannot be resolved. |

### Permissions example

```php
// config/announcements.php

// Using a Laravel Gate:
'permission_check' => 'manage-announcements',

// Using a closure:
'permission_check' => fn ($user) => $user->hasRole('admin'),

// Using Spatie Permission:
'permission_check' => fn ($user) => $user->can('manage-announcements'),
```

---

## How it works

**Widget** — only shows announcements where `is_active = true` and the current time falls within the `starts_at` / `expires_at` window. Results are ordered with `danger` first, then by `created_at` descending.

**Dismiss** — when a user closes a dismissible announcement, the action is recorded in the `announcement_user` pivot table (`dismissed_at`). That announcement will never show again for that user. Non-dismissible announcements hide the close button entirely.

**Expiration** — handled purely by the query. No scheduled commands required.

---

## Translations

The plugin ships with English strings. To add your own language, publish the translation files:

```bash
php artisan vendor:publish --tag="announcements-translations"
```

This will create `lang/vendor/announcements/{locale}/announcements.php` where you can override any string.

---

## Roadmap

- [ ] Audience targeting by role / group
- [ ] Custom icon picker per announcement
- [ ] Optional CTA link/button on the widget
- [ ] Multi-panel support
- [ ] `announcements:prune` artisan command

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for details on recent changes.

---

## Contributing

Pull requests are welcome! Please keep the scope aligned with the package goals.

- Run `vendor/bin/pint` before submitting PHP changes
- Add tests for new behaviour when applicable
- Open an issue first for significant changes

---

## License

MIT — see [LICENSE](LICENSE) for details.

---

<p align="center">
    Made with ❤️ by <a href="https://github.com/MarceloDelgadoDev">Marcelo Delgado</a>
</p>