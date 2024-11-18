<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Carbon\Carbon;
use Exception;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Throwable;

class RestoreFailedException extends Exception
{
    public function __construct(
        public BackupDto $backup,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: __('statamic-backup::backup.restore_failed', ['name' => Carbon::now()->format('Ymd')]),
            previous: $previous
        );
    }
}
