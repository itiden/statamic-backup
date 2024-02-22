<?php

declare(strict_types=1);

namespace Itiden\Backup\Events;

use Itiden\Backup\Exceptions\RestoreFailedException;

class RestoreFailed
{
    public function __construct(public RestoreFailedException $exception)
    {
    }
}
