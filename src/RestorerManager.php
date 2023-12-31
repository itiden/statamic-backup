<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;

final class RestorerManager extends Manager
{
    /**
     * Restore from a backup with a given timestamp.
     *
     * @throws Exception
     */
    public function restoreFromTimestamp(string $timestamp): void
    {
        $backup = $this->repository->find($timestamp);

        if (!$backup) {
            throw new \Exception("Backup with timestamp {$timestamp} not found.");
        }

        $disk = config('backup.destination.disk');

        /**
         * If the disk is local, we can just use the path directly.
         * Otherwise we need to download it to a temporary location.
         *
         * This is because we can't extract a zip file from a remote disk.
         */
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

        $this->restoreFromArchive($backupZipPath);
    }

    /**
     * Restore from a backup at the given path.
     *
     * @throws Exception
     */
    public function restore(string $path): void
    {

        if (!File::exists($path)) {
            throw new \Exception("Path {$path} does not exist.");
        }

        collect($this->getDrivers())
            ->each(
                fn ($key) => $this->driver($key)->restore("{$path}/{$key}")
            );

        File::cleanDirectory(config('backup.temp_path'));

        Artisan::call('cache:clear', [
            '--quiet' => true,
        ]);
    }

    /**
     * Restore from a archived backup at the given path.
     *
     * @throws Exception
     */
    public function restoreFromArchive(string $path): void
    {
        $target = config('backup.temp_path') . '/unzipping';

        Zipper::make($path, true)->extractTo($target, config('backup.password'))->close();

        if (!collect(File::allFiles($target))->count()) {
            throw new \Exception("This backup is empty, perhaps you used the wrong password?");
        }

        $this->restore($target);
    }
}
