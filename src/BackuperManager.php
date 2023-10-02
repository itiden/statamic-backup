<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;
use Illuminate\Support\Str;
use Itiden\Backup\DataTransferObjects\BackupDto;

class BackuperManager extends Manager
{
    /**
     * Create a new backup.
     */
    public function backup(): BackupDto
    {
        $disk = config('backup.destination.disk');
        $backup_path = config('backup.destination.path');
        $temp_zip_path = config('backup.temp_path') . '/temp.zip';

        $zipper = Zipper::make($temp_zip_path);

        collect($this->getDrivers())->each(
            fn ($key) => $this->driver($key)->backup($zipper)
        );

        if ($password = config('backup.password')) {
            $zipper->encrypt($password);
        }

        $zipper->close();

        $filename = Str::slug(config('app.name')) . '-' . Carbon::now()->unix() . '.zip';

        Storage::disk($disk)->makeDirectory($backup_path);

        $path = Storage::disk($disk)->putFileAs($backup_path, new File($temp_zip_path), $filename);

        unlink($temp_zip_path);

        $this->enforceMaxBackups();

        return BackupDto::fromFile($path);
    }

    /**
     * Get all backups.
     *
     * @return Collection<BackupDto>
     */
    public function getBackups(): Collection
    {
        $disk = config('backup.destination.disk');
        $backup_path = config('backup.destination.path');

        return collect(Storage::disk($disk)->files($backup_path))
            ->filter(fn ($path) => Str::endsWith($path, '.zip'))
            ->map([BackupDto::class, 'fromFile'])
            ->sort(fn ($a, $b) => $b->timestamp <=> $a->timestamp);
    }

    /**
     * Get a specific backup.
     */
    public function getBackup(string $timestamp): BackupDto
    {
        return $this->getBackups()->first(fn ($backup) => $backup->timestamp === $timestamp);
    }

    /**
     * Delete a specific backup.
     */
    public function deleteBackup(string $timestamp): BackupDto
    {
        $backup = $this->getBackup($timestamp);

        Storage::disk(config('backup.destination.disk'))->delete($backup->path);

        return $backup;
    }

    /**
     * Clear the backup directory.
     */
    public function clearBackups(): bool
    {
        return Storage::disk(config('backup.destination.disk'))->deleteDirectory(config('backup.destination.path'));
    }

    /**
     * Remove oldest backups when max backups is exceeded if it's present.
     */
    private function enforceMaxBackups(): void
    {
        if (!$max_backups = config('backup.max_backups', false)) {
            return;
        }

        $backups = $this->getBackups();

        if ($backups->count() > $max_backups) {
            $backups->slice($max_backups)->each(function ($backup) {
                Storage::disk(config('backup.destination.disk'))->delete($backup->path);
            });
        }
    }
}
