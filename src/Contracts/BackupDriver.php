<?php

declare(strict_types=1);

namespace Itiden\Backup\Contracts;

use ZipArchive;

interface BackupDriver
{
    /**
     * Get the key of the driver.
     * 
     * @return string
     */
    public static function getKey(): string;

    /**
     * Run the restore process.
     * 
     * @param ZipArchive $zip
     * 
     * @return void
     */
    public function restore(string $path): void;

    /**
     * Run the backup process.
     * 
     * @param ZipArchive $zip
     * 
     * @return void
     */
    public function backup(ZipArchive $zip): void;
}
