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
use Itiden\Backup\Events\RestoreFailed;
use Itiden\Backup\Support\Zipper;
use RuntimeException;

final class Restorer
{
    public function __construct(
        private BackupRepository $repository,
        private StateManager $stateManager
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
            throw new RuntimeException("Backup with timestamp {$timestamp} not found.");
        }

        $this->restore($backup);
    }

    /**
     * Restore to the given backup.
     *
     * @throws RestoreFailed
     */
    public function restore(BackupDto $backup): void
    {
        $state = $this->stateManager->getState();

        if (in_array($state, [State::BackupInProgress, State::RestoreInProgress])) {
            throw new Exception("Cannot start backup while in state \"{$state->value}\"");
        }

        try {
            $this->stateManager->setState(State::RestoreInProgress);

            $path = $this->getLocalBackupPath($backup);

            if (!File::exists($path)) {
                throw new Exception("Path {$path} does not exist.");
            }

            if (File::mimeType($path) === 'application/zip') {
                $path = $this->unzip($path);
            }

            Pipeline::via('restore')
                ->send($path)
                ->through(config('backup.pipeline'))
                ->thenReturn();

            event(new BackupRestored($backup));

            if ($user = auth()->user()) {
                $backup->getMetadata()->addRestore($user);
            }

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

            $this->stateManager->setState(State::RestoreCompleted);
        } catch (Exception $e) {
            report($e);

            $exception = new Exceptions\RestoreFailed($backup, previous: $e);

            $this->stateManager->setState(State::RestoreFailed);

            event(new RestoreFailed($exception));

            throw $exception;
        }
    }

    /**
     * Get the backup to a local disk if it is not already and return the path.
     */
    private function getLocalBackupPath(BackupDto $backup): string
    {
        $disk = config('backup.destination.disk');
        /**
         * If the backup does not exist on the given disk, return the path.
         */
        if (!Storage::disk($disk)->exists($backup->path)) {
            return $backup->path;
        }

        if (config("filesystems.disks.{$disk}.driver") === 'local') {
            return Storage::disk($disk)->path($backup->path);
        }

        $tempDisk = Storage::build([
            'driver' => 'local',
            'root' => config('backup.temp_path') . DIRECTORY_SEPARATOR . 'backup',
        ]);

        $tempDisk->writeStream('backup.zip', Storage::disk($disk)->readStream($backup->path));

        return $tempDisk->path('backup.zip');
    }

    /**
     * Restore from a archived backup at the given path.
     *
     * @throws Exception
     */
    private function unzip(string $path): string
    {
        $target = config('backup.temp_path') . DIRECTORY_SEPARATOR . 'unzipping';

        Zipper::open($path, true)->extractTo($target, config('backup.password'))->close();

        if (!collect(File::allFiles($target))->count()) {
            throw new Exception("This backup is empty, perhaps you used the wrong password?");
        }

        return $target;
    }
}
