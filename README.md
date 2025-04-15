# Backup

[![Latest Version](https://img.shields.io/github/release/itiden/statamic-backup.svg)](https://github.com/itiden/statamic-backup/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/itiden/statamic-backup.svg)](https://packagist.org/packages/itiden/statamic-backup)
![Backup tests](https://github.com/itiden/statamic-backup/workflows/Test/badge.svg)

Backup is an extensible and powerful backup tool for statamic sites, it enables you to create, manage and schedule backups of your site with ease in the control panel.

![image](https://github.com/user-attachments/assets/3dfe3930-8997-4e73-a270-342585c75fee)

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Documentation](#documentation)
- [License](#license)

## Features

Why use our backup addon?

- Control panel page where you can manage backups
- Chunked uploads - upload huge backups from other servers without the need to change your `php.ini`.
- Permissions - choose exactly which users can manage your backups with [permissions](https://statamic.dev/users#permissions).
- Choose exactly what you want to backup by configuring the backup [pipeline](docs/pipeline.md).
  - Easy to extend and customize, [just create a new pipes](docs/pipeline.md#creating-a-new-backup-pipe)!
- Uses laravels [storage system](https://laravel.com/docs/11.x/filesystem) and thus supports external storage out of the box.
- Tested, the addon have over 95% test coverage.

## Installation

Read the more detailed guide in the documentation, [here](https://itiden.github.io/statamic-backup/getting-started.html).

Otherwise, here is a "quickstart guide":

1. To install this addon, require it using composer.

   ```bash
   composer require itiden/statamic-backup
   ```

2. Give your user permissions to manage backups and start managing backups in the control panel. ([permissions](https://itiden.github.io/statamic-backup/getting-started.html#permissions))

## Documentation

Read more about configuration, installation, usage and more advanced topics in our [documentation](https://itiden.github.io/statamic-backup/)!

## License

Backup is open-sourced software licensed under the [MIT license](LICENSE.md).
