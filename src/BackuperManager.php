<?php

declare(strict_types=1);

namespace Itiden\Backup;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;
use Illuminate\Support\Str;
use Itiden\Backup\DataTransferObjects\BackupDto;

class BackuperManager extends Manager
{
    public function backup(): BackupDto
    {
        $disk = config('backup.backup.disk');
        $backup_path = config('backup.backup.path');

        $temp_zip_path = Zipper::zip(function (ZipArchive $zip) {
            collect($this->getDrivers())->each(
                fn ($key) => $this->driver($key)->backup($zip)
            );
        });

        $filename = Str::slug(config('app.name')) . '-' . Carbon::now()->unix() . '.zip';

        Storage::disk($disk)->makeDirectory($backup_path);

        $path = Storage::disk($disk)->putFileAs($backup_path, new File($temp_zip_path), $filename);

        unlink($temp_zip_path);

        $this->enforceMaxBackups();

        return BackupDto::fromFile($path);
    }

    public function getBackups(): Collection
    {
        $disk = config('backup.backup.disk');
        $backup_path = config('backup.backup.path');

        return collect(Storage::disk($disk)->files($backup_path))
            ->map([BackupDto::class, 'fromFile'])
            ->sort(fn ($a, $b) => $b->timestamp <=> $a->timestamp);
    }

    private function enforceMaxBackups(): void
    {
        if (!$max_backups = config('backup.backup.max_backups', false)) {
            return;
        }

        $backups = $this->getBackups();

        if ($backups->count() > $max_backups) {
            $backups->slice($max_backups)->each(function ($backup) {
                Storage::disk(config('backup.backup.disk'))->delete($backup->path);
            });
        }
    }
}
