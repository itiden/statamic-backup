# Configuration

To configure this package you need to publish the config file

```sh
php artisan vendor:publish --tag="backup"
```

```php
return [
    'content_path' => storage_path('content'),
    'backup' => [
        'disk' => 'local',
        'path' => 'statamic-backups',
        'max_backups' => 10,
    ],
    'backup_drivers' => [
        Itiden\Backup\Drivers\Content::class,
        Itiden\Backup\Drivers\Assets::class,
    ],
];
```

The `content_path` should point to your content folder and is primarily used by the default content `backup driver`, to make it a bit more flexible.

The `backup` key contains the configuration options for your backups, where they should be stored and how many backups there should be at a maximum.

- `disk`: option can be any of your applications disks, read more about disks [here](https://laravel.com/docs/10.x/filesystem#configuration). Using laravels disk is really powerfull because it allows you to backup to another server for example.
- `path`: the directory the backup will be saved to.
- `max_backups`: the max number of backups, when superseded it will delete the oldest one.

The `backup_drivers` key tells the `Backuper` and `Restorer` what steps should be done on every backup and restore. the `Manager` class (which backuper and restorer extends) will collect the `backup_drivers` and run them when the backup or restore method is called.

Every `backup_driver` should implement the `BackupDriver` contract.

- `getKey()`: gets the key, it's used for deciding what driver gets to restore a folder.
- `backup()`: will get the backups `ZipArchive` and from there you can add whatever you want to that and it will be included in the backup.
- `restore()`: will get a path to a directory named the same as the `getKey` method returns, in the backup it's restoring from.
