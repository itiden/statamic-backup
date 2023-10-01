<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;

class RestorerManager extends Manager
{
    /**
     * Restore from a backup with a given timestamp.
     *
     * @throws Exception
     */
    public function restoreFromTimestamp(string $timestamp): void
    {
        $backup = Backuper::getBackup($timestamp);

        if (!$backup) {
            throw new \Exception("Backup with timestamp {$timestamp} not found.");
        }

        $this->restoreFromPath(Storage::disk(config('backup.destination.disk'))->path($backup->path));
    }

    /**
     * Restore from a backup at the given path.
     *
     * @throws Exception
     */
    public function restoreFromPath(string $path): void
    {
        $actualPath = static::getDirectoryPath($path);

        collect($this->getDrivers())
            ->each(
                fn ($key) => $this->driver($key)->restore("{$actualPath}/{$key}")
            );

        File::cleanDirectory(config('backup.temp_path'));
    }

    /**
     * Get the actual path of the backup which for now means in case of a zip file, unzip it.
     */
    private static function getDirectoryPath(string $path): string
    {
        $mime = File::mimeType($path);

        return match ($mime) {
            'application/zip' => Zipper::unzip($path, config('backup.temp_path') . '/temp', config('backup.password')),
            default => $path,
        };
    }
}
