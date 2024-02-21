# Pipeline

## Configuration

Publish the config file

```sh
php artisan vendor:publish --tag="backup-config"
```

Then you can swap out the drivers used however you want.

for example, maybe you don't want to backup your users, then just comment that one out:

```php
'pipeline' => [
    Itiden\Backup\Pipes\Content::class,
    Itiden\Backup\Pipes\Assets::class,
    // Itiden\Backup\Pipes\Users::class,
],
```

Now the users wont be backed up nor restored until you add it back.

## What is a pipe?

A pipe is a step the backup/restore pipeline will run.

For this package to use your pipe it must extend the `BackupPipe` abstract which defines three methods you must implement:

- `getKey` this is used for identifying when a specific driver should be should run.
- `restore` which runs on restore, gets a path like this `path/to/backup/driver_key`.
- `backup` runs on backup and it gets a zipper object you can use (zipper a zipArchive wrapper).

## How it works

Since each backup pipe has a unique (it should be) key, it is pretty handy for the pipes to use this key to identify their own backup files. For example, the `Content` pipe will backup to `path/to/backup/content` and the `Assets` pipe will backup to `path/to/backup/assets`.

Then when restoring, the pipe will get the path to the backup file and it can use the key to identify its own backup file. There is a helper method (`getDirectoryPath`) for that in the `BackupPipe` abstract.

### Backing up

- Creates a zip archive
- Runs the `backup` method on all of the pipes specified in `config('backup.pipeline')`.
- Encrypts and moves the archive to backup destination

### Restoring

- Unzips the archive if needed
- Get the backup as a directory.
- Runs the `restore` method on all of the pipes specified in `config('backup.pipeline')`.

## Creating a new backup pipe

Creating a new backup pipe is easy. You just need to create a new class that extends the `Itiden\Backup\Abstracts\BackupPipe` abstract and implement the required methods.

### Step 1: Create a new pipe class

Start by creating a new PHP class that will serve as your backup driver. This class should extend the `BackupPipe` abstract and provide implementations for its abstract methods. Here's a basic structure for your driver class:

```php
namespace App\Backup\Pipes;

use Itiden\Backup\Abstracts\BackupPipe;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Support\Zipper;

final class Logs extends BackupPipe
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

In the example above, we've created a class `Logs` that extends the `BackupPipe` abstract. This class defines the required methods: `getKey()`, `restore()`, and `backup()`.

- `getKey()`: This method returns a unique string key for your driver. This key will be used to identify when your driver should be ran in the restore process.

- `restore()`: In this method, you should implement the logic to restore your data, the `$path` equals path/to/backup.

- `backup()`: Here, you should implement the logic to create a backup of your data and add it to the `Zipper` instance `$zip`. This method is responsible for adding data to the backup archive. You should also prefix all paths with the key. `Zipper` is a wrapper around `ZipArchive` with some convenience methods, you can access the underlying `ZipArchive` with the `getArchive` method.

### Step 2: Configure Your Backup

Finally, you can configure your backup to use the custom driver you've created. Update your `config/backup.php` file to include your new driver. For example:

```php
return [
    // ...
    'pipeline' => [
        Itiden\Backup\Drivers\Content::class,
        Itiden\Backup\Drivers\Assets::class,
        Itiden\Backup\Drivers\Users::class,
        App\Backup\Pipes\Logs::class,
    ],
    // ...
];
```

And that's it! If you feel like this documentation is missing something, feel free to open an issue or a pull request. We are happy to help you out! 😄🚀
