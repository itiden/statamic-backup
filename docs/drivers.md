# Drivers

## Configuration

Publish the config file

```sh
php artisan vendor:publish --tag="backup-config"
```

Then you can swap out the drivers used however you want.

for example, maybe you don't want to backup your users, then just comment that one out:

```php
'pipeline' => [
    Itiden\Backup\Drivers\Content::class,
    Itiden\Backup\Drivers\Assets::class,
    // Itiden\Backup\Drivers\Users::class,
],
```

Now the users wont be backed up nor restored until you add it back.

## What is a driver?

In this package a driver is a step the `Backuper` can take (if it's in the config file).

For this package to use your driver it must implement the `BackupPipe` contract which defines three methods:

- `getKey` this is used for identifying when a specific driver should be should run.
- `restore` which runs on restore, gets a path like this `path/to/backup/driver_key`.
- `backup` runs on backup and it gets a zipper object you can use (zipper a zipArchive wrapper).

## How it works

### Backing up

- Create a zip archive
- Run the `backup` method from all of the drivers specified in `config('backup.pipeline')`.
- Encrypt and move the archive to backup destination

### Restoring

- Get the backup as a directory.
- Get the "root" directories of the backup, loop over them.
- Run the driver that has a key that equals to folder name.

## Creating a New Backup Driver

A backup driver in the context of this documentation refers to a component responsible for handling the backup and restore operations for a specific aspect of your application or data. To create a new backup driver, you'll need to implement the `BackupPipe` interface provided by the `Itiden\Backup\Contracts` namespace. This interface defines the methods that your driver must implement.

### Step 1: Create a New Driver Class

Start by creating a new PHP class that will serve as your backup driver. This class should implement the `BackupPipe` interface and provide implementations for its methods. Here's a basic structure for your driver class:

```php
use Itiden\Backup\Abstracts\BackupPipe;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

class Logs extends BackupPipe
{
    /**
     * Get the key of the driver.
     */
    public static function getKey(): string
    {
        return 'logs';
    }

    /**
     * Run the restore process.
     */
    public function restore(string $restoringFromPath): void
    {
        $path = $this->getDirectoryPath($restoringFromPath);
        // Implement the logic to restore data from the provided backup file at $path.
        File::copyDirectory($path, storage_path('logs'));
    }

    /**
     * Run the backup process.
     */
    public function backup(Zipper $zip): void
    {
        // Implement the logic to create a backup of your data and add it to the ZipArchive instance $zip.
        $zip->addDirectory(storage_path('logs'), static::getKey());
    }
}
```

In the example above, we've created a class `Logs` that implements the `BackupPipe` interface. This class defines the required methods: `getKey()`, `restore()`, and `backup()`.

- `getKey()`: This method returns a unique string key for your driver. This key will be used to identify when your driver should be ran in the restore process.

- `restore()`: In this method, you should implement the logic to restore your data, the `$path` path/to/backup/key, where key is what getKey returns.

- `backup()`: Here, you should implement the logic to create a backup of your data and add it to the `Zipper` instance `$zip`. This method is responsible for adding data to the backup archive. You should also prefix all paths with the key. `Zipper` is a wrapper around `ZipArchive` with some convenience methods, you can access the `ZipArchive` with the `getArchive` method.

### Step 2: Configure Your Backup

Finally, you can configure your backup to use the custom driver you've created. Update your `config/backup.php` file to include your new driver. For example:

```php
return [
    // ...
    'pipeline' => [
        Itiden\Backup\Drivers\Content::class,
        Itiden\Backup\Drivers\Assets::class,
        Itiden\Backup\Drivers\Users::class,
        Logs::class,
    ],
    // ...
];
```

In this configuration, we've added the Logs class to the backup drivers. Now, your application can use your custom backup driver to handle backups and restores for the logs aspect of your application.

That's it! You've created a custom backup driver and configured it for use in your Statamic application. Your new driver can now be used to manage backups and restores for your specific use case.
