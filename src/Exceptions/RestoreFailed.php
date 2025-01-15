<?php

declare(strict_types=1);

namespace Itiden\Backup\Exceptions;

use Exception;
use Itiden\Backup\DataTransferObjects\BackupDto;
use Throwable;

final class RestoreFailed extends Exception
{
    public function __construct(
        public BackupDto $backup,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: __('statamic-backup::backup.restore.failed', ['name' => $backup->name]),
            previous: $previous
        );
    }
}
