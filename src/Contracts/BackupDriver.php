<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts;

use Itiden\Backup\Support\Zipper;

interface BackupDriver
{
    /**
     * Get the key of the driver.
     */
    public static function getKey(): string;

    /**
     * Run the restore process.
     */
    public function restore(string $path): void;

    /**
     * Run the backup process.
     */
    public function backup(Zipper $zip): void;
}
