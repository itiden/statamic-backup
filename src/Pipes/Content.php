<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;

class Content extends BackupPipe
{
    public static function getKey(): string
    {
        return 'content';
    }

    public function restore(string $restoringFromPath): void
    {
        $destination = config('backup.content_path');

        File::cleanDirectory($destination);
        File::copyDirectory($this->getDirectoryPath($restoringFromPath), $destination);
    }

    public function backup(Zipper $zip): void
    {
        $contentPath = config('backup.content_path');

        $zip->addDirectory($contentPath, static::getKey());
    }
}
