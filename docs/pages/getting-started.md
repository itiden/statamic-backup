# Getting started

## Installation

### Prerequisites

- [Statamic](https://statamic.com/) 5.x
- [PHP ext-zip](https://www.php.net/manual/en/book.zip.php) (you probably have this installed by default)

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

First you will have to give your user permissions to manage backups, this is done like any other permission in statamic.

Then you should see a `Backups` entry in the navigation menu.

On the backups page you can create new backups, delete or restore from existing, or upload and restore from that.

The uploader chunks backups so no need to worry about the file being larger than your servers post sizes!
