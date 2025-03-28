# Naming backups

If you want to customize how backups are named and "discovered", you can!

The default naming scheme will be:

```
{app.name}-{timestamp}-{id}.zip
```

## Customizing

You can customize the naming by providing your own `BackupNameResolver` implementation.

This class is responsible for generating filenames and parsing files into identifiable information and required metadata in the form of `ResolvedBackupData`.
So when making your own implementation, you need to make sure that your generate and parseFilename methods work togheter or it will not work.

Here is an example of a custom `BackupNameResolver` implementation:

```php
use Carbon\CarbonImmutable;
use Itiden\Backup\Contracts\BackupNameResolver;
use Itiden\Backup\DataTransferObjects\ResolvedBackupData;

final readonly class MyAppSpecificBackupNameResolver implements BackupNameResolver
{
    private const string Separator = '---';

    // return a custom filename, the ".zip" extension will be added automatically if it is missing
    public function generateFilename(CarbonImmutable $createdAt, string $id): string
    {
        $parts = [
            "some-testest-that-implies-something",
            $createdAt->format('Y-m-d'),
            $id,
        ];

        return implode(self::Separator, $parts);
    }

    public function parseFilename(string $path): ?ResolvedBackupData
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $parts = explode(self::Separator, $filename);

        // if the filename cannot be parsed, return null
        if (count($parts) !== 3) {
            return null;
        }

        [$name, $date, $identifier] = $parts;

        $createdAt = CarbonImmutable::createFromFormat('Y-m-d', $date);

        return new ResolvedBackupData(
            createdAt: $createdAt,
            id: $identifier,
            name: $name
        );
    }
}
```
