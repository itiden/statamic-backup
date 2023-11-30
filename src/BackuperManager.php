<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;
use Itiden\Backup\DataTransferObjects\BackupDto;

final class BackuperManager extends Manager
{
    /**
     * Create a new backup.
     */
    public function backup(): BackupDto
    {
        $temp_zip_path = config('backup.temp_path') . '/temp.zip';

        $zipper = Zipper::make($temp_zip_path);

        collect($this->getDrivers())->each(
            fn ($key) => $this->driver($key)->backup($zipper)
        );

        if ($password = config('backup.password')) {
            $zipper->encrypt($password);
        }

        $zipper->close();

        $backup = $this->repository->add($temp_zip_path);

        unlink($temp_zip_path);

        $this->enforceMaxBackups();

        return $backup;
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
