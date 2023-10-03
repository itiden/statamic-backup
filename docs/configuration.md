# Configuration

To configure this package, follow these easy steps:

1. **Publish the Config File**:

   Use the following artisan command to publish the configuration file:

   ```sh
   php artisan vendor:publish --tag="backup-config"
   ```

   This opens the door to configuring your backups to your heart's content!

## Configuration Options

Now, let's dive into the exciting world of configuration. Here's an overview of each option:

- **`content_path`**:

  - _Description_: Specifies the path to your content directory, used by the default content backup driver.
  - _Default_: `storage_path('content')`

- **`destination`**:

  - _Description_: Defines the backup destination options, including the disk and path where backups will be stored. By default, backups are stored locally in a 'statamic-backups' directory.

- **`temp_path`**:

  - _Description_: Specifies the path to the temporary directory used for backup operations. By default, it's set to `storage_path('framework/statamic-backup')`.

- **`max_backups`**:

  - _Description_: Determines the maximum number of backups to keep. When this limit is exceeded, the oldest backup will be automatically deleted.
  - _Default_: 10 backups

- **`password`**:

  - _Description_: Sets a backup password. If set to null, password protection is disabled. You can also override this value with an environment variable named 'BACKUP_PASSWORD'.

- **`schedule`**:

  - _Description_: Configure the backup schedule options. Specify the frequency and time for automatic backups. By default, backups are scheduled to run daily. Check out the scheduling details [here](scheduling.md).

- **`backup_drivers`**:
  - _Description_: Specifies the backup drivers to use. These drivers determine what aspects of your site will be backed up. By default, three drivers are included: 'Content', 'Assets', and 'Users'. If you need more custom drivers, learn how to add them [here](drivers.md).

There you have it! With these options, you can fine-tune your backup setup to suit your needs and keep your data safe and sound. Happy configuring! 😄🚀
