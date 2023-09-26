<?php

declare(strict_types=1);

namespace Itiden\Backup\Drivers;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Contracts\BackupDriver;
use Itiden\Backup\Support\Zipper;
use ZipArchive;

class Content implements BackupDriver
{
    public static function getKey(): string
    {
        return 'content';
    }

    public function restore(string $content): bool
    {
        $destination = config('backup.content_path');

        return File::copyDirectory($content, $destination);
    }

    public function backup(ZipArchive $zip): bool
    {
        $contentPath = config('statamic.stache.directories.content');

        return Zipper::zipDir($contentPath, $zip, static::getKey());
    }
}
