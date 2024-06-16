<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Http\File as StreamableFile;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Support\Zipper;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;
use Itiden\Backup\Exceptions\BackupFailedException;
use Illuminate\Support\Str;
use Itiden\Backup\Contracts\BackupNameGenerator;
use Statamic\Support\Str as StatamicStr;

final class Backuper
{
    public function __construct(
        protected BackupRepository $repository
    ) {
    }

    /**
     * Create a new backup.
     *
     * @throws BackupFailedException
     */
    public function backup(): BackupDto
    {
        try {
            $temp_zip_path = config('backup.temp_path') . DIRECTORY_SEPARATOR . Str::uuid() . '.zip';

            $zipper = Zipper::open($temp_zip_path);

            Pipeline::via('backup')
                ->send($zipper)
                ->through(config('backup.pipeline'))
                ->thenReturn();

            if ($password = config('backup.password')) {
                $zipper->encrypt($password);
            }

            $zipper->close();

            $disk = config('backup.destination.disk');
            $directory = config('backup.destination.path');

            // Ensure the backup target directory exists
            Storage::disk($disk)->makeDirectory($directory);

            $createdAt = Carbon::now();

            // Move the backup to the destination disk
            $path = Storage::disk($disk)->putFileAs(
                $directory,
                new StreamableFile($temp_zip_path),
                $createdAt->unix() . '.zip'
            );

            $backup = $this->repository->add(
                new BackupDto(
                    name: app(BackupNameGenerator::class)->generate($createdAt),
                    created_at: $createdAt,
                    size: StatamicStr::fileSizeForHumans(filesize($temp_zip_path)),
                    timestamp: (string) $createdAt->unix(),
                    path: $path,
                    disk: $disk,
                )
            );

            event(new BackupCreated($backup));

            unlink($temp_zip_path);

            $this->enforceMaxBackups();

            return $backup;
        } catch (Exception $e) {
            report($e);

            $exception = new BackupFailedException();

            event(new BackupFailed($exception));

            throw $e;
        }
    }

    /**
     * Remove oldest backups when max backups is exceeded if it's present.
     */
    private function enforceMaxBackups(): void
    {
        if (!$max_backups = config('backup.max_backups', false)) {
            return;
        }

        $backups = $this->repository->all();

        if ($backups->count() > $max_backups) {
            $backups->slice($max_backups)->each(function ($backup) {
                $this->repository->remove($backup->timestamp);
            });
        }
    }
}
