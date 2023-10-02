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

        $disk = config('backup.destination.disk');

        $target = config('backup.temp_path') . '/open';

        // If the disk is not local, we need to download it first
        // to a temporary location so we can extract it.
        if (config("filesystems.disks.{$disk}.driver") === 'local') {
            $backupZipPath = Storage::disk($disk)->path($backup->path);
        } else {
            $tempDisk = Storage::build([
                'driver' => 'local',
                'root' => config('backup.temp_path') . '/backup',
            ]);

            $tempDisk->writeStream('backup.zip', Storage::disk($disk)->readStream($backup->path));

            $backupZipPath = $tempDisk->path('backup.zip');
        }

        Zipper::make($backupZipPath, true)->extractTo($target, config('backup.password'))->close();

        if (!collect(File::allFiles($target))->count()) {
            throw new \Exception("This backup is empty, perhaps you used the wrong password?");
        }

        $this->restoreFromPath($target);
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
