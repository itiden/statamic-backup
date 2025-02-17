<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Carbon\Carbon;
use Exception;
use Throwable;

final class BackupFailed extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct(
            __('statamic-backup::backup.failed', ['date' => Carbon::now()->format('Ymd')]),
            previous: $previous,
        );
    }
}
