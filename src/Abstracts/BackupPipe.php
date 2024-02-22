<?php

declare(strict_types=1);

namespace Itiden\Backup\Abstracts;

use Itiden\Backup\Support\Zipper;

abstract class BackupPipe
{
    /**
     * Get the key of the driver.
     */
    abstract public static function getKey(): string;

    /**
     * Run the restore process.
     *
     * @param string $path The path to the root of the backup file.
     */
    abstract public function restore(string $path): void;

    /**
     * Run the backup process.
     */
    abstract public function backup(Zipper $zip): void;

    /**
     * Get the directory path for the current pipe.
     */
    protected function getDirectoryPath(string $path): string
    {
        return $path . DIRECTORY_SEPARATOR . static::getKey();
    }
}
