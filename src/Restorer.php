<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupRestored;
use Itiden\Backup\Support\Zipper;

final class Restorer
{
    public function __construct(
        protected BackupRepository $repository
    ) {
    }

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

        $this->restore($backupZipPath, $backup);
    }

    /**
     * Restore from a backup at the given path.
     *
     * @throws Exception
     */
    public function restore(string $path, ?BackupDto $backup = null): void
    {

        if (!File::exists($path)) {
            throw new Exception("Path {$path} does not exist.");
        }

        if (File::mimeType($path) === 'application/zip') {
            if (!$backup) {
                $backup = BackupDto::fromAbsolutePath($path);
            }
            $path = $this->unzip($path);
        }

        Pipeline::via('restore')
            ->send($path)
            ->through(config('backup.pipeline'))
            ->thenReturn();

        event(new BackupRestored($backup ?? BackupDto::fromAbsolutePath($path)));

        File::cleanDirectory(config('backup.temp_path'));


        /**
         * Clear the cache and stache to make sure everything is up to date.
         */
        Artisan::call('cache:clear', [
            '--quiet' => true,
        ]);
        Artisan::call('statamic:stache:clear', [
            '--quiet' => true,
        ]);
    }

    /**
     * Restore from a archived backup at the given path.
     *
     * @throws Exception
     */
    private function unzip(string $path): string
    {
        $target = config('backup.temp_path') . '/unzipping';

        Zipper::open($path, true)->extractTo($target, config('backup.password'))->close();

        if (!collect(File::allFiles($target))->count()) {
            throw new Exception("This backup is empty, perhaps you used the wrong password?");
        }

        return $target;
    }
}
