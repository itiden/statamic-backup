# Naming backups

Sometimes you need to customize the naming of your backupfiles, maybe you download them often and want to have "prettier" names for them, or maybe you want to have a specific naming scheme for your backups due to some other reason.
This package provides a way to customize the naming of your backup files.

You can customize the naming by providing your own `BackupNameResolver` implementation, [Read more](#making-your-own-implementation).

## Default naming scheme

The default driver for naming backups is `Itiden\Backup\GenericBackupNameResolver`, which will generate filenames like this:

```
{app.name}---{timestamp}---{id}.zip
```

The reason for this format is that it is easy to parse and it contains all the information you need to identify the backup.

> [!INFO]
> When you upload a backup, it will be renamed with with the naming scheme of the driver you are using.

## The inner workings

### `generateFilename`

Will be provided with a `CarbonImmutable` and a `string` identifier (ulid), the returned string can be a path and the `zip` extension will be appended if it isn't there already.

> [!CAUTION]
> The provided identifier MUST be included in the filename since it will be used to find the backup after creation.

### `parseFilename`

Will be provided with storages path to the file, this path is relative to the configured backup location, so it will look something like this:

```
config('backup.destination.path')/laravel---1696156800---unique-id.zip
```

> [!TIP]
> If you only want to parse the filename, you can use `pathinfo($path, PATHINFO_FILENAME)` to get the filename.

It should return a `ResolvedBackupData` dto with the must-have information about the backup, or null if the data couldn't be resolved and the path should be ignored.

## Making your own implementation

When making your own `BackupNameResolver` implementation, it is important that your `generateFilename` and `parseFilename` methods work togheter or your backups might become undiscoverable.

> [!TIP]
> The `parseFilename` method will be provided a "relative" path for each file in the configured backup location, so you can save and resolve backups in subdirectories!

Here is an example of a custom `BackupNameResolver` implementation:

```php
use Carbon\CarbonImmutable;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\DataTransferObjects\ResolvedBackupData;

final readonly class MyAppSpecificBackupNameResolver implements BackupNameResolver
{
    private const string Separator = '---';

    public function __construct(
        private GenericBackupNameResolver $previouslyUsedBackupNameResolver,
    ) {
    }

    // return a custom filename, the ".zip" extension will be added automatically if it is missing
    public function generateFilename(CarbonImmutable $createdAt, string $id): string
    {
        $parts = [
            "some-name-that-implies-something",
            $createdAt->timestamp,
            $id,
        ];

        // here we are storing the the backup in a directory named after the date, might be nice if you often view the backups in a file browser
        return $createdAt->format('Y-m-d') . '/' . implode(self::Separator, $parts);
    }

    public function parseFilename(string $path): ?ResolvedBackupData
    {
        // The path in this example will be something like "statamic-backups/2023-10-01/some-name-that-implies-something---1696156800---unique-id.zip"

        $filename = pathinfo($path, PATHINFO_FILENAME);

        $parts = explode(self::Separator, $filename);

        // Here we can return null, or maybe use a fallback to try and parse the filename
        // if we don't have the expected amount of parts
        // or if the parts don't match our expectations
        // - it's up to you!
        if (count($parts) !== 3) {
            return $this->previouslyUsedBackupNameResolver->parseFilename($path);
        }

        [$name, $date, $identifier] = $parts;

        return new ResolvedBackupData(
            id: $identifier,
            name: $name
            createdAt: CarbonImmutable::createFromFormat('Y-m-d', $date),
        );
    }
}
```
