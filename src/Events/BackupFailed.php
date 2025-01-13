<?php

declare(strict_types=1);

namespace Itiden\Backup\Events;

use Itiden\Backup\Exceptions;

final readonly class BackupFailed
{
    public function __construct(public Exceptions\BackupFailed $exception) {}
}
