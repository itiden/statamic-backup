# Configuration

To configure this package you need to publish the config file

```sh
php artisan vendor:publish --tag="backup-config"
```

You can configure where backups are stored, how many are kept, whether they require a password for access, and when they are automatically scheduled to be created.

Below, you'll find an explanation of each configuration item:

- `content_path`: This setting specifies the path to the content directory used by the default content backup driver. It is set to `storage_path('content')`.

- `destination`: Defines the backup destination options, including the disk and path where backups will be stored. By default backups are stored locally in a 'statamic-backups' directory.

- `temp_path`: Specifies the path to the temporary directory used for backup operations. It's set to `storage_path('framework/statamic-backup')`.

- `max_backups`: Determines the maximum number of backups to keep. When this limit is exceeded, the oldest backup will be automatically deleted. The default is to keep 10 backups.

- `password`: Sets a backup password. If set to null, password protection is disabled. This value can be overridden by an environment variable named 'BACKUP_PASSWORD'.

- `schedule`: Configures the backup schedule options. You can specify the frequency and time for automatic backups. By default backups are scheduled to run daily. See more [here](sheduling.md)

- `backup_drivers`: Specifies the backup drivers to use. These drivers define what aspects of your site will be backed up. By default three drivers are included: 'Content', 'Assets', and 'Users'. You can add more custom drivers here if needed, more about that [here](drivers.md).
