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

        $destination = Storage::disk(config('backup.destination.disk'))->path($backup->path);
        $target = config('backup.temp_path') . '/restore';

        Zipper::make($destination, true)->extractTo($target . '/restore', config('backup.password'))->close();

        if (!collect(File::allFiles($target))->count()) {
            throw new \Exception("This backup is empty, perhaps you used the wrong password?");
        }

        $this->restoreFromPath($target . '/restore');
    }

    /**
     * Restore from a backup at the given path.
     *
     * @throws Exception
     */
    public function restoreFromPath(string $path): void
    {

        if (!File::exists($path)) {
            throw new \Exception("Path {$path} does not exist.");
        }

        collect($this->getDrivers())
            ->each(
                fn ($key) => $this->driver($key)->restore("{$path}/{$key}")
            );

        File::cleanDirectory(config('backup.temp_path'));
    }
}
