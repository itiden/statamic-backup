<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Facades\Backuper;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;

class RestorerManager extends Manager
{
    public function restoreFromTimestamp(string $timestamp): void
    {
        $backup = Backuper::getBackup($timestamp);

        if (!$backup) {
            throw new \Exception("Backup with timestamp {$timestamp} not found.");
        }

        $this->restoreFromPath($backup->path);
    }

    public function restoreFromPath(string $path): void
    {
        $actualPath = static::getDirectoryPath($path);

        collect($this->getDrivers())
            ->each(
                fn ($key) => $this->driver($key)->restore("{$actualPath}/{$key}")
            );
    }

    public static function getDirectoryPath(string $path)
    {
        $mime = File::mimeType($path);

        return match ($mime) {
            'application/zip' => Zipper::unzip($path, config('backup.temp_path') . '/temp', config('backup.password')),
            default => $path,
        };
    }
}
