<?php

namespace Itiden\Backup\Tests;

use Closure;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;

final class SkippingPipe extends BackupPipe
{
    public static function getKey(): string
    {
        return 'skipping';
    }

    public function restore(string $path, Closure $next)
    {
        return $next($path);
    }

    public function backup(Zipper $zip, Closure $next)
    {
        return $this->skip(reason: 'This pipe is skipped', next: $next, zip: $zip);
    }
}
