# Backup

Backup is an extensible and powerful backup tool for statamic sites, it enables you to create, manage and schedule backups of your site with ease in the control panel.

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Scheduling](#scheduling)
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
- Tested, we have over 140 assertions in this addon.

## Installation

To install this addon, require it using composer.

```bash
composer require itiden/statamic-backup
```

## Configuration

You can configure the backup settings in the `config/backup.php` file, first publish the configuration file using the following command:

```bash
php artisan vendor:publish --tag="backup-config"
```

Then you can configure:

- Backup path
- Backup disk
- Backup filename
- Backup schedule
- Temporary files path
- Max amount of backups to keep
- Backup password
- The Backup pipeline

Read more about the configuration [here](docs/configuration.md)!

## Usage

To create a backup, navigate to the Backup section in the control panel and click the "Create Backup" button. There you can also see a list of all your backups and download or restore them if needed.

Or if you prefer to use the command line, you can use the following command:

```bash
php artisan statamic:backup:create
```

### Scheduling

Configure the backup schedule in the configuration file, read more about it [here](docs/scheduling.md).

To use the scheduling you need to run the laravel scheduler. Read more about that here: [Running the scheduler](https://laravel.com/docs/10.x/scheduling#running-the-scheduler).

## Documentation

In the docs directory you can read more about the pipeline, scheduling and notification configurations.

## License

Backup is open-sourced software licensed under the [MIT license](LICENSE.md).
