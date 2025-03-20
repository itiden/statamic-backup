<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\Store;

final class ContentStachePipe extends BackupPipe
{
    public static function getKey(): string
    {
        return 'stache-content';
    }

    public function restore(string $restoringFromPath, Closure $next): string
    {
        collect(Stache::stores())
            ->filter(fn(Store $store) => static::isSafePath($store->directory()))
            ->filter(fn(Store $store) => File::exists($restoringFromPath . '/' . static::prefixer($store)))
            ->each(function (Store $store) use ($restoringFromPath): void {
                File::cleanDirectory($store->directory());

                File::copyDirectory($restoringFromPath . '/' . static::prefixer($store), $store->directory());
            });

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next): Zipper
    {
        $stores = Stache::stores()
            ->filter(fn(Store $store) => $store->key() !== 'users')
            ->filter(fn(Store $store) => static::isSafePath($store->directory()));

        if ($stores->isEmpty()) {
            return $this->skip(
                reason: 'No content paths found, is the Stache configured correctly?',
                next: $next,
                zip: $zip,
            );
        }

        $stores->each(fn(Store $store) => $zip->addDirectory(realpath($store->directory()), static::prefixer($store)));

        return $next($zip);
    }

    private static function prefixer(Store $store): string
    {
        return self::getKey() . '::' . $store->key();
    }

    private static function isSafePath(string $path): bool
    {
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
}
