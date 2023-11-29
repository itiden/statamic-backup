<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Illuminate\Support\Collection;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;
use Itiden\Backup\DataTransferObjects\BackupDto;

class BackuperManager extends Manager
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
     * Get all backups.
     *
     * @return Collection<BackupDto>
     */
    public function getBackups(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get a specific backup.
     */
    public function getBackup(string $timestamp): BackupDto
    {
        return $this->repository->find($timestamp);
    }

    /**
     * Delete a specific backup.
     */
    public function deleteBackup(string $timestamp): BackupDto
    {
        return $this->repository->remove($timestamp);
    }

    /**
     * Clear the backup directory.
     */
    public function clearBackups(): bool
    {
        return $this->repository->empty();
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
