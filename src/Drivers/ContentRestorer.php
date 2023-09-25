<?php

declare(strict_types=1);

namespace Itiden\Backup\Drivers;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Contracts\Restorer;
use Itiden\Backup\Support\Zipper;
use ZipArchive;

class ContentRestorer implements Restorer
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

        return Zipper::zipDir($contentPath, $zip, 'content');
    }
}
