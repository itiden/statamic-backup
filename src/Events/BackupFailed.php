<?php

declare(strict_types=1);

namespace Itiden\Backup\Events;

use Itiden\Backup\Exceptions\BackupFailedException;

class BackupFailed
{
    public function __construct(public BackupFailedException $exception)
    {
    }
}
