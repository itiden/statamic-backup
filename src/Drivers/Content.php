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

    public function restore(string $content): void
    {
        $destination = config('backup.content_path');

        File::cleanDirectory($destination);
        File::copyDirectory($content, $destination);
    }

    public function backup(ZipArchive $zip): void
    {
        $contentPath = config('backup.content_path');

        Zipper::zipDir($contentPath, $zip, static::getKey());
    }
}
