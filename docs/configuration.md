# Configuration

**Publish the Config File**:

Use the following artisan command to publish the configuration file:

```sh
php artisan vendor:publish --tag="backup-config"
```

## Configuration Options

Now, let's dive into the exciting world of configuration. Here's an overview of each option:

- **`content_path`**:

  - _Description_: Specifies the path to your content directory, used by the default content backup driver.
  - _Default_: `storage_path('content')`

- **`destination`**:

  - _Description_: Defines the backup destination disk and path.
  - _Default_: statamic-backups on the local disk.

- **`temp_path`**:

  - _Description_: Specifies the path to the temporary directory used for backup operations.
  - _Defualt_: `storage_path('framework/statamic-backup')`

- **`max_backups`**:

  - _Description_: Determines the maximum number of backups to keep. When this limit is exceeded, the oldest backup will be automatically deleted.
  - _Default_: 10

- **`password`**:

  - _Description_: Sets a backup password. If set to null, password protection is disabled.
  - _Default_: `env('BACKUP_PASSWORD')`

- **`schedule`**:

  - _Description_: Configure the backup schedule options. Specify the frequency and time for automatic backups.
  - _Configuration_: Check out our scheduling docs [here](scheduling.md).
  - _Default_: Daily at midnight

- **`pipeline`**:
  - _Description_: Specifies the backup pipeline. These "pipes" determine what parts of your site will be backed up.
  - _Configuration_: Check out our backup pipes docs [here](pipes.md).
  - _Default_: Content, Assets, and Users.

There you have it! With these options, you can fine-tune your backup setup to suit your needs and keep your data safe and sound. Happy configuring! 😄🚀
