# Backup

Backup is an extensible and powerful backup tool for statamic sites, it enables you to create, manage and schedule backups of your site with ease in the control panel.

Why use our backup addon?

- Chunked uploads - upload huge backups from other servers without the need to change your `php.ini`.
- Choose exactly what you want to backup by changing the backup [pipeline](docs/pipeline.md).
  - Easy to extend due to the pipeline design, [just create a new pipes](docs/pipeline.md#creating-a-new-backup-pipe)!
- UI to manage backups
- Permissions - choose exactly which users can manage your backups with [permissions](https://statamic.dev/users#permissions).

## Installation

To install this addon, require it using composer.

```bash
composer require itiden/statamic-backup
```

## Usage

To create a backup, navigate to the Backup section in the control panel and click the "Create Backup" button. There you can also see a list of all your backups and download or restore them if needed.

Or if you prefer to use the command line, you can use the following command:

```bash
php artisan statamic:backup:create
```

### Scheduling

Configure the backup schedule in the configuration file, read more about it [here](docs/scheduling.md).

To use the scheduling you need to run the laravel scheduler. Read more about that here: [Running the scheduler](https://laravel.com/docs/10.x/scheduling#running-the-scheduler).

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

## License

Backup is open-sourced software licensed under the [MIT license](LICENSE.md).
