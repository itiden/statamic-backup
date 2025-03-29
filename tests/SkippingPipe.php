<?php

declare(strict_types=1);

namespace Itiden\Backup\Tests;

use Closure;
use Itiden\Backup\Abstracts\BackupPipe;
use Itiden\Backup\Support\Zipper;

final readonly class SkippingPipe extends BackupPipe
{
    public static function getKey(): string
    {
        return 'skipping';
    }

    public function restore(string $restoringFromPath, Closure $next): string
    {
        return $next($restoringFromPath);
    }

    public function backup(Zipper $zip, Closure $next): Zipper
    {
        return $this->skip(
            reason: 'This pipe is skipped',
            next: $next,
            zip: $zip,
        );
    }
}
