<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;

class Content extends BackupPipe
{
    public static function getKey(): string
    {
        return 'content';
    }

    public function restore(string $restoringFromPath, Closure $next)
    {
        $destination = config('backup.content_path');

        File::cleanDirectory($destination);
        File::copyDirectory($this->getDirectoryPath($restoringFromPath), $destination);

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next)
    {
        $contentPath = config('backup.content_path');

        if (!File::exists($contentPath)) {
            return $this->skip(reason: 'Content directory didn\'t exist, is it configured correctly?', next: $next, zip: $zip);
        }

        $zip->addDirectory($contentPath, static::getKey());

        return $next($zip);
    }
}
