<?php

declare(strict_types=1);

namespace Itiden\Backup\Pipes;

use Closure;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;
use RuntimeException;
use Statamic\Facades\Stache;

class Users extends BackupPipe
{
    public static function getKey(): string
    {
        return 'users';
    }

    public function restore(string $restoringFromPath, Closure $next)
    {
        $destination = $this->getConfig()['users']->directory();
        $users = $this->getDirectoryPath($restoringFromPath);

        File::copyDirectory($users, $destination);

        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next)
    {
        $usersDir = Stache::store('users')?->directory();

        if (!$usersDir) {
            throw new RuntimeException('Users directory not found');
        }

        $zip->addDirectory($usersDir, static::getKey());

        return $next($zip);
    }
}
