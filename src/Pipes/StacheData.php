<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;
use LogicException;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\Store;

use function Illuminate\Filesystem\join_paths;

final readonly class StacheData extends BackupPipe
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
            ->filter(static fn(Store $store) => File::exists(join_paths($restoringFromPath, static::prefixer($store))))
            ->each(static function (Store $store) use ($restoringFromPath): void {
                File::cleanDirectory($store->directory());

                File::copyDirectory(
                    directory: join_paths($restoringFromPath, static::prefixer($store)),
                    destination: $store->directory(),
                );
            });

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next): Zipper
    {
        $stores = collect(Stache::stores())
            ->filter(static::shouldBackupStore(...))
            ->filter(static::storeHasSafeDirectory(...))
            ->filter(static fn(Store $store) => File::isDirectory($store->directory()));

        if ($stores->isEmpty()) {
            return $this->skip(
                reason: 'No stores found to backup, is the Stache configured correctly?',
                next: $next,
                zip: $zip,
            );
        }

        $stores->each(static fn(Store $store) => $zip->addDirectory(
            path: static::realPath($store),
            prefix: static::prefixer($store),
        ));

        return $next($zip);
    }

    private static function realPath(Store $store): string
    {
        $path = $store->directory();

        $realPath = realpath($path);

        if (!$realPath) {
            throw new LogicException("Unable to resolve real path for store [{$store->key()}] at [{$path}].");
        }

        return $realPath;
    }

    private static function prefixer(Store $store): string
    {
        return self::getKey() . '::' . $store->key();
    }

    private static function storeHasSafeDirectory(Store $store): bool
    {
        $path = $store->directory();

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
        return in_array($store->key(), Config::array('backup.stache_stores', []), strict: true);
    }
}
