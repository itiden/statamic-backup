<?php

namespace Itiden\Backup;

use Carbon\Carbon;
use Illuminate\Http\File;
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

        return Storage::disk($disk)->path($path);
    }
}
