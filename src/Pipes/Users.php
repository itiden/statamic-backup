<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Facades\File;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;

class Users extends BackupPipe
{
    public static function getKey(): string
    {
        return 'users';
    }

    public function restore(string $restoringFromPath, Closure $next)
    {
        $destination = config('statamic.stache.stores.users.directory');
        $users = $this->getDirectoryPath($restoringFromPath);

        File::copyDirectory($users, $destination);

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next)
    {
        $zip->addDirectory(config('statamic.stache.stores.users.directory'), static::getKey());

        return $next($zip);
    }
}
