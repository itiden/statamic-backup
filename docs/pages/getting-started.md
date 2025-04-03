# Getting started

## Installation

### Prerequisites

- [Statamic](https://statamic.com/) 5.x
- [PHP ext-zip](https://www.php.net/manual/en/book.zip.php) (you probably have this installed by default)
- [A cache driver that supports atomic locks](https://laravel.com/docs/11.x/cache#atomic-locks) (The default Laravel one works)

Install by requiring the package with composer:

```sh
composer require itiden/statamic-backup
```

### configuration

To start configuring your backup settings, you must publish the config file:

```sh
php artisan vendor:publish --tag="backup-config"
```

To learn more about the configuration read [here](./configuration.md)!

## Usage

### Permissions

The package comes with a few permissions to keep your backups safe:

- `manage backups` - This permissions allows the user to access the backups page and view the backups.
  - `create backups` - This permission allows the user to create backups.
  - `delete backups` - This permission allows the user to delete backups.
  - `restore backups` - This permission allows the user to restore and upload backups.
  - `download backups` - This permission allows the user to download backups.

If you want to know more about permissions, you can read the statamic [permissions documentation](https://statamic.dev/users#permissions).

We recommend using the `roles` or `groups` feature to manage permissions.

### Backups

When you have configured your permissions, you should see a `Backups` entry in the navigation menu.

On the backups page you can create new backups, delete or restore from existing, or upload backups.

The uploader chunks backups so no need to worry about the file being larger than your servers post sizes! (the chunk size is configurable!)

### CLI

You can also manage backups from the cli, see [here](./commands.md) for a list of all available commands.
