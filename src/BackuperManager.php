<?php

namespace Itiden\Backup;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Itiden\Backup\Support\Manager;
use Itiden\Backup\Support\Zipper;
use Illuminate\Support\Str;

class BackuperManager extends Manager
{
    public function backup(): string
    {
        $disk = config('backup.backup.disk');
        $backup_path = config('backup.backup.path');

        $temp_path = Zipper::zip(function (ZipArchive $zip) {
            collect($this->getClients())->each(
                fn ($key) => $this->client($key)->backup($zip)
            );
        });

        $fileName = Str::slug(config('app.name')) . '-' . Carbon::now()->unix() . '.zip';

        Storage::disk($disk)->makeDirectory($backup_path);

        $path = Storage::disk($disk)->putFileAs($backup_path, new File($temp_path), $fileName);

        unlink($temp_path);

        $this->enforceMaxBackups();

        return Storage::disk($disk)->path($path);
    }

    public function getBackups(): Collection
    {
        $disk = config('backup.backup.disk');
        $backup_path = config('backup.backup.path');

        return collect(Storage::disk($disk)->files($backup_path))
            ->map(function ($path) use ($disk) {
                $timestamp = Str::before(Str::after(basename($path), '-'), '.zip');

                return [
                    'name' => Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s'),
                    'size' =>   Storage::disk($disk)->size($path),
                    'path' => $path,
                    'timestamp' => $timestamp,
                ];
            })
            ->sort(fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);
    }

    private function enforceMaxBackups(): void
    {
        if (!$maxBackups = config('backup.backup.max_backups', false)) {
            return;
        }

        $backups = $this->getBackups();

        if ($backups->count() > $maxBackups) {
            $backups->slice($maxBackups)->each(function ($backup) {
                Storage::disk(config('backup.backup.disk'))->delete($backup['path']);
            });
        }
    }
}
