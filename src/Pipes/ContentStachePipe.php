<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\Store;

use function Illuminate\Filesystem\join_paths;

final class ContentStachePipe extends BackupPipe
{
    public static function getKey(): string
    {
        return 'stache-content';
    }

    public function restore(string $restoringFromPath, Closure $next): string
    {
        collect(Stache::stores())
            ->filter(static::shouldBackupStore(...))
            ->filter(static::storeHasSafeDirectory(...))
            ->filter(fn(Store $store) => File::exists(join_paths($restoringFromPath, static::prefixer($store))))
            ->each(function (Store $store) use ($restoringFromPath): void {
                File::cleanDirectory($store->directory());

                File::copyDirectory(join_paths($restoringFromPath, static::prefixer($store)), $store->directory());
            });

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next): Zipper
    {
        return collect(Stache::stores())
            ->filter(static::shouldBackupStore(...))
            ->filter(static::storeHasSafeDirectory(...))
            ->whenNotEmpty(
                function (Collection $stores) use ($zip, $next): Zipper {
                    $stores->each(fn(Store $store) => $zip->addDirectory(
                        path: realpath($store->directory()),
                        prefix: static::prefixer($store),
                    ));

                    return $next($zip);
                },
                default: fn() => $this->skip(
                    reason: 'No stores found to backup, is the Stache configured correctly?',
                    next: $next,
                    zip: $zip,
                ),
            );
    }

    private static function prefixer(Store $store): string
    {
        return self::getKey() . '::' . $store->key();
    }

    private static function storeHasSafeDirectory(Store $store): bool
    {
        $path = $store->directory();

        // Check if the path is a real path
        if (!realpath($path))
            return false;

        return !in_array(
            needle: $path,
            haystack: [
                '/',
            ],
            strict: true,
        );
    }

    private static function shouldBackupStore(Store $store): bool
    {
        return in_array($store->key(), config('backup.stache_stores', []), strict: true);
    }
}
