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
        $temp_path = Zipper::zip(function (ZipArchive $zip) {
            collect($this->getClients())
                ->each(
                    fn ($key) => $this->client($key)->backup($zip)
                );
        });

        $fileName = Str::slug(config('app.name')) . '-' . Carbon::now()->unix() . '.zip';

        Storage::disk(config('backup.backup.disk'))->makeDirectory(config('backup.backup.path'));

        $path = Storage::disk(config('backup.backup.disk'))->putFileAs(config('backup.backup.path'), new File($temp_path), $fileName);

        unlink($temp_path);

        return Storage::disk(config('backup.backup.disk'))->path($path);
    }
}
