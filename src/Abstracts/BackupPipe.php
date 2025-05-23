<?php

declare(strict_types=1);

namespace Itiden\Backup\Abstracts;

use Closure;
use Itiden\Backup\Support\Zipper;

abstract readonly class BackupPipe
{
    /**
     * Get the key of the driver.
     */
    abstract public static function getKey(): string;

    /**
     * Run the restore process.
     *
     * @param string $restoringFromPath The path to the root of the backup file.
     * @param Closure(string $restoringFromPath):string $next The next pipe in the chain.
     */
    abstract public function restore(string $restoringFromPath, Closure $next): string;

    /**
     * Run the backup process.
     *
     * @param Zipper $zip The zipper instance.
     * @param Closure(Zipper $zip):Zipper $next The next pipe in the chain.
     */
    abstract public function backup(Zipper $zip, Closure $next): Zipper;

    /**
     * Get the directory path for the current pipe.
     */
    protected function getDirectoryPath(string $path): string
    {
        return $path . DIRECTORY_SEPARATOR . static::getKey();
    }

    /**
     * Mark pipe as skipped.
     */
    protected function skip(string $reason, Closure $next, Zipper $zip): Zipper
    {
        $zip->addMeta(static::class, ['skipped' => $reason]);
        return $next($zip);
    }
}
