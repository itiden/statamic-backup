<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Exception;
use Illuminate\Support\Facades\Pipeline;
use Itiden\Backup\Contracts\Repositories\BackupRepository;
use Itiden\Backup\Support\Zipper;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Itiden\Backup\Events\BackupCreated;
use Itiden\Backup\Events\BackupFailed;
use Itiden\Backup\Exceptions\BackupFailedException;

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
            $temp_zip_path = config('backup.temp_path') . '/temp.zip';

            $zipper = Zipper::open($temp_zip_path);

            Pipeline::via('backup')
                ->send($zipper)
                ->through(config('backup.pipeline'))
                ->thenReturn();

            if ($password = config('backup.password')) {
                $zipper->encrypt($password);
            }

            $zipper->close();

            $backup = $this->repository->add($temp_zip_path);

            event(new BackupCreated($backup));

            unlink($temp_zip_path);

            $this->enforceMaxBackups();

            return $backup;
        } catch (Exception $e) {
            $exception = new BackupFailedException($e);

            event(new BackupFailed($exception));

            throw $exception;
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
